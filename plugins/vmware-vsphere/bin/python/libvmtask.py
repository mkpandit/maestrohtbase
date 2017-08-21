#!/usr/bin/env python
#
# HyperTask Enterprise developed by HyperTask Enterprise GmbH.
#
# All source code and content (c) Copyright 2014, HyperTask Enterprise GmbH unless specifically noted otherwise.
#
# This source code is released under the HyperTask Enterprise Server and Client License, unless otherwise agreed with HyperTask Enterprise GmbH.
# The latest version of this license can be found here: http://htvcenter-enterprise.com/license
#
# By using this software, you acknowledge having read this license and agree to be bound thereby.
#
#           http://htvcenter-enterprise.com
#
# Copyright 2014, HyperTask Enterprise GmbH <info@htvcenter-enterprise.com>
#

from pyVmomi import vmodl
from pyVmomi import vim
from inspect import getmembers
from pprint import pprint
import sys
import subprocess


# returns a list of objects of the given type
def get_vim_objects(content,vim_type):
	return [item for item in content.viewManager.CreateContainerView(content.rootFolder,[vim_type],recursive=True).view]

# returns a list of objects of the given name
def get_vim_obj_by_name(content,vimtype,name):
    obj = None
    container = content.viewManager.CreateContainerView(content.rootFolder, vimtype, True)
    for c in container.view:
        if c.name == name:
            obj = c
            break
    return obj


# waits for end of task
def WaitForTasks(tasks, si):
	pc = si.content.propertyCollector
	task_result = None
	taskList = [str(task) for task in tasks]
	objSpecs = [vmodl.query.PropertyCollector.ObjectSpec(obj=task) for task in tasks]
	propSpec = vmodl.query.PropertyCollector.PropertySpec(type=vim.Task, pathSet=[], all=True)
	filterSpec = vmodl.query.PropertyCollector.FilterSpec()
	filterSpec.objectSet = objSpecs
	filterSpec.propSet = [propSpec]
	filter = pc.CreateFilter(filterSpec, True)

	try:
		version, state = None, None
		while len(taskList):
			update = pc.WaitForUpdates(version)
			for filterSet in update.filterSet:
				for objSet in filterSet.objectSet:
					task = objSet.obj
					for change in objSet.changeSet:
						if change.name == 'info':
							state = change.val.state
						elif change.name == 'info.state':
							state = change.val
						else:
							continue

						if not str(task) in taskList:
							continue

						if state == vim.TaskInfo.State.success:
							task_result = task.info.result

							taskList.remove(str(task))
						elif state == vim.TaskInfo.State.error:
							raise task.info.error
			version = update.version
	except Exception, e:
		print "Caught Exception in WaitForTasks : " + str(e)
	finally:
		if filter:
			filter.Destroy()

	return task_result



def validate_input(input):
	return str(input).replace("@", " ")




def add_disk(vm, service_instance, disk_size, disk_type):

	# add controller
	controller = vim.vm.device.ParaVirtualSCSIController()
	controller.sharedBus=vim.vm.device.VirtualSCSIController.Sharing.noSharing
	virtual_device_spec = vim.vm.device.VirtualDeviceSpec()
	virtual_device_spec.operation = vim.vm.device.VirtualDeviceSpec.Operation.add
	virtual_device_spec.device = controller
	config_spec = vim.vm.ConfigSpec()
	config_spec.deviceChange = [virtual_device_spec]
	task = vm.ReconfigVM_Task(config_spec)
	WaitForTasks([task], service_instance)

	unit_number = 0
	spec = vim.vm.ConfigSpec()
	# get all disks on a VM, set unit_number to the next available
	for dev in vm.config.hardware.device:
		if hasattr(dev.backing, 'fileName'):
			unit_number = int(dev.unitNumber) + 1
			# unit_number 7 reserved for scsi controller
			if unit_number == 7:
				unit_number += 1
			if unit_number >= 16:
				print "we don't support this many disks"
				return
		if isinstance(dev, vim.vm.device.VirtualSCSIController):
			controller = dev
	# add disk here
	dev_changes = []
	new_disk_kb = int(disk_size) * 1024
	disk_spec = vim.vm.device.VirtualDeviceSpec()
	disk_spec.fileOperation = "create"
	disk_spec.operation = vim.vm.device.VirtualDeviceSpec.Operation.add
	disk_spec.device = vim.vm.device.VirtualDisk()
	disk_spec.device.backing = \
		vim.vm.device.VirtualDisk.FlatVer2BackingInfo()
	if disk_type == 'thin':
		print "- Using thin provisioning"
		disk_spec.device.backing.thinProvisioned = True
	else:
		print "- Using regular (thick) provisioning"
	disk_spec.device.backing.diskMode = 'persistent'
	disk_spec.device.unitNumber = unit_number
	disk_spec.device.capacityInKB = new_disk_kb
	disk_spec.device.controllerKey = controller.key
	dev_changes.append(disk_spec)
	spec.deviceChange = dev_changes
	task = vm.ReconfigVM_Task(spec=spec)
	WaitForTasks([task], service_instance)

	print "%sMB disk added to %s" % (disk_size, vm.config.name)



def detach_disk(vm, service_instance, disk_path):

	device = ''
	spec = vim.vm.ConfigSpec()
	for dev in vm.config.hardware.device:
		if hasattr(dev.backing, 'fileName'):
			if dev.backing.fileName == disk_path:
				device = dev
				continue

	#print device.backing.fileName
	#print device.controllerKey
	# remove disk here
	dev_changes = []

	disk_spec = vim.vm.device.VirtualDeviceSpec()
	disk_spec.operation = vim.vm.device.VirtualDeviceSpec.Operation.remove
	disk_spec.device = device

	dev_changes.append(disk_spec)
	spec.deviceChange = dev_changes
	task = vm.ReconfigVM_Task(spec=spec)
	WaitForTasks([task], service_instance)

	print "Disk removed from %s" % (vm.config.name)





def attach_disk(vm, service_instance, disk_path):

	controller = ''
	unit_number = 0
	for dev in vm.config.hardware.device:
		if hasattr(dev.backing, 'fileName'):
			unit_number = int(dev.unitNumber) + 1
			# unit_number 7 reserved for scsi controller
			if unit_number == 7:
				unit_number += 1
			if unit_number >= 16:
				print "we don't support this many disks"
				return
		if isinstance(dev, vim.vm.device.VirtualSCSIController):
			controller = dev

	spec = vim.vm.ConfigSpec()
	dev_changes = []
	disk_spec = vim.vm.device.VirtualDeviceSpec()
	disk_spec.operation = vim.vm.device.VirtualDeviceSpec.Operation.add

	disk_spec.device = vim.vm.device.VirtualDisk()
	disk_spec.device.backing = vim.vm.device.VirtualDisk.RawDiskVer2BackingInfo()
	disk_spec.device.backing.descriptorFileName = disk_path
	disk_spec.device.controllerKey = controller.key

	new_disk_kb = 1024 * 1024
	disk_spec.device.unitNumber = unit_number
	disk_spec.device.capacityInKB = new_disk_kb

	dev_changes.append(disk_spec)
	spec.deviceChange = dev_changes
	task = vm.ReconfigVM_Task(spec=spec)
	WaitForTasks([task], service_instance)

	print "Disk attached to %s" % (vm.config.name)






def add_vnic(service_instance, host, pgname, mac):
	content=service_instance.RetrieveContent()
	for Host in get_vim_objects(content,vim.HostSystem):
		if Host.name == host:
			vspec = vim.host.VirtualNic.Specification()
			vspec.ip = vim.host.IpConfig(dhcp=True)
			vspec.mac = mac
			Host.configManager.networkSystem.AddServiceConsoleVirtualNic(portgroup=pgname, nic=vspec)




def add_nic(vm, service_instance, network_name, nictype, mac):
	nicspec = vim.vm.device.VirtualDeviceSpec()
	nicspec.operation = vim.vm.device.VirtualDeviceSpec.Operation.add

	if nictype == 'vmx':
		print "- creating vmx network card"
		nicspec.device = vim.vm.device.VirtualVmxnet3()
	elif nictype == 'e1000':
		print "- creating e1000 network card"
		nicspec.device = vim.vm.device.VirtualE1000()
	elif nictype == 'pc32':
		print "- creating pc32 network card"
		nicspec.device = vim.vm.device.VirtualPCNet32()
	else:
		print "- creating default e1000 network card"
		nicspec.device = vim.vm.device.VirtualE1000()

	nicspec.device.wakeOnLanEnabled = True
	nicspec.device.addressType = 'Manual'
	nicspec.device.macAddress = mac
	nicspec.device.deviceInfo = vim.Description()
	nicspec.device.deviceInfo.label = network_name
	nicspec.device.deviceInfo.summary = network_name

	nicspec.device.backing = vim.vm.device.VirtualEthernetCard.NetworkBackingInfo()
	content = service_instance.RetrieveContent()
	nicspec.device.backing.network = get_vim_obj_by_name(content, [vim.Network], network_name)
	# in case it is a dvs and not a standard switch
	#if not nicspec.device.backing.network:
	#	pg_obj = get_vim_obj_by_name(content, [vim.dvs.DistributedVirtualPortgroup], network_name)
	#	dvs_port_connection = vim.dvs.PortConnection()
	#	dvs_port_connection.portgroupKey= pg_obj.key
	#	dvs_port_connection.switchUuid= pg_obj.config.distributedVirtualSwitch.uuid
	#	nicspec.device.backing = vim.vm.device.VirtualEthernetCard.DistributedVirtualPortBackingInfo()
	#	nicspec.device.backing.port = dvs_port_connection

	nicspec.device.backing.deviceName = network_name
	nicspec.device.connectable = vim.vm.device.VirtualDevice.ConnectInfo()
	nicspec.device.connectable.startConnected = True
	nicspec.device.connectable.allowGuestControl = True

	dev_changes = []
	spec = vim.vm.ConfigSpec()
	dev_changes.append(nicspec)
	spec.deviceChange = dev_changes
	task = vm.ReconfigVM_Task(spec=spec)
	WaitForTasks([task], service_instance)
	print "Added nic to %s" % (vm.config.name)







def remove_all_nic(vm, service_instance):

	content = service_instance.RetrieveContent()
	devices = []
	for device in vm.config.hardware.device:
		if hasattr(device, 'addressType'):
			nic = vim.vm.device.VirtualDeviceSpec()
			nic.operation = vim.vm.device.VirtualDeviceSpec.Operation.remove
			nic.device = device
			devices.append(nic)

			dev_changes = []
			spec = vim.vm.ConfigSpec()
			dev_changes.append(nic)
			spec.deviceChange = dev_changes
			task = vm.ReconfigVM_Task(spec=spec)
			WaitForTasks([task], service_instance)
			print "Removed nic from %s" % (vm.config.name)




def add_iso(vm, service_instance, iso):
	print "Attaching iso to ", vm.config.name
	ds = iso.split(']')
	datastore_name = ds[0][1:]
	content = service_instance.RetrieveContent()

	controller = vim.vm.device.VirtualIDEController()
	for dev in vm.config.hardware.device:
		if isinstance(dev, vim.vm.device.VirtualIDEController):
			controller = dev

	cdspec = None
	cdspec = vim.vm.device.VirtualDeviceSpec()
	cdspec.operation = vim.vm.device.VirtualDeviceSpec.Operation.add
	cdspec.device = vim.vm.device.VirtualCdrom()
	cdspec.device.key = 3000
	cdspec.device.controllerKey = controller.key

	# unit number == ide controller slot, 0 is first ide disk, 1 second
	cdspec.device.unitNumber = 0
	cdspec.device.deviceInfo = vim.Description()
	cdspec.device.deviceInfo.label = 'CD/DVD drive 1'
	cdspec.device.deviceInfo.summary = 'ISO'
	cdspec.device.backing = vim.vm.device.VirtualCdrom.IsoBackingInfo()
	cdspec.device.backing.fileName = iso
	datastore = get_vim_obj_by_name(content=content, vimtype=[vim.Datastore], name=datastore_name)
	cdspec.device.backing.datastore = datastore
	#cdspec.device.backing.dynamicType = 
	#cdspec.device.backing.backingObjectId = '0'

	cdspec.device.connectable = vim.vm.device.VirtualDevice.ConnectInfo()
	cdspec.device.connectable.startConnected = True
	cdspec.device.connectable.allowGuestControl = True
	cdspec.device.connectable.connected = False
	cdspec.device.connectable.status = 'untried'

	vmconf = vim.vm.ConfigSpec()
	vmconf.deviceChange = [cdspec]
	dev_changes = []
	dev_changes.append(cdspec)
	vmconf.deviceChange = dev_changes
	task = vm.ReconfigVM_Task(spec=vmconf) 
	WaitForTasks([task], service_instance)




def remove_iso(vm, service_instance):
	print "Removing iso from ", vm.config.name
	#ds = iso.split(']')
	#datastore_name = ds[0][1:]
	content = service_instance.RetrieveContent()

	controller = vim.vm.device.VirtualIDEController()
	for dev in vm.config.hardware.device:
		if isinstance(dev, vim.vm.device.VirtualIDEController):
			controller = dev

	if len(controller.device) > 0:
		cdspec = None
		cdspec = vim.vm.device.VirtualDeviceSpec()
		cdspec.operation = vim.vm.device.VirtualDeviceSpec.Operation.remove

		cdspec.device = vim.vm.device.VirtualCdrom()
		cdspec.device.key = controller.device[0]
		cdspec.device.controllerKey = controller.key
		# unit number == ide controller slot, 0 is first ide disk, 1 second
		cdspec.device.unitNumber = 1
		cdspec.device.deviceInfo = vim.Description()
		cdspec.device.deviceInfo.label = 'CD/DVD drive 1'
		cdspec.device.deviceInfo.summary = 'ISO'
		#cdspec.device.backing = vim.vm.device.VirtualCdrom.IsoBackingInfo()
		#cdspec.device.backing.fileName = iso
		#datastore = get_vim_obj_by_name(content=content, vimtype=[vim.Datastore], name=datastore_name)
		#cdspec.device.backing.datastore = datastore

		cdspec.device.connectable = vim.vm.device.VirtualDevice.ConnectInfo()
		cdspec.device.connectable.startConnected = False
		cdspec.device.connectable.allowGuestControl = False
		cdspec.device.connectable.connected = False
		cdspec.device.connectable.status = 'untried'

		vmconf = vim.vm.ConfigSpec()
		vmconf.deviceChange = [cdspec]
		dev_changes = []
		dev_changes.append(cdspec)
		vmconf.deviceChange = dev_changes
		task = vm.ReconfigVM_Task(spec=vmconf) 
		WaitForTasks([task], service_instance)




def set_boot(vm, service_instance, bootfrom):
	print "Setting boot-options for ", vm.config.name
	if bootfrom == 'local':
		bo = vim.option.OptionValue(key='bios.bootDeviceClasses',value='allow:hd,cd,net')
	elif bootfrom == 'cdrom':
		bo = vim.option.OptionValue(key='bios.bootDeviceClasses',value='allow:cd,hd,net')
	elif bootfrom == 'network':
		bo = vim.option.OptionValue(key='bios.bootDeviceClasses',value='allow:net,hd,cd')
	else:
		bo = vim.option.OptionValue(key='bios.bootDeviceClasses',value='allow:hd,cd,net')

	vmconf = vim.vm.ConfigSpec()
	vmconf.extraConfig  = [bo]
	task = vm.ReconfigVM_Task(vmconf)
	WaitForTasks([task], service_instance)




def list_files(service_instance, pattern):

	content = service_instance.RetrieveContent()
	datacenter = content.rootFolder.childEntity[0]
	datastores = datacenter.datastore

	for ds in datastores:
		search = vim.HostDatastoreBrowserSearchSpec()
		search.matchPattern = pattern
		n = '[' + ds.summary.name + ']'
		search_ds = ds.browser.SearchDatastoreSubFolders_Task(n, search)
		while search_ds.info.state != "success":
			pass
		results = search_ds.info.result
		for rs in search_ds.info.result:
			dsfolder = rs.folderPath
			for f in rs.file:
				dsfile = f.path
				print dsfolder + dsfile





def set_memory(vm, service_instance, memory):
	print "Updating memory on VM ", vm.config.name
	vmconf = vim.vm.ConfigSpec()
	vmconf.memoryMB  = memory
	task = vm.ReconfigVM_Task(vmconf)
	WaitForTasks([task], service_instance)


def set_cpu(vm, service_instance, cpu):
	print "Updating CPU on VM ", vm.config.name
	vmconf = vim.vm.ConfigSpec()
	vmconf.numCPUs  = cpu
	task = vm.ReconfigVM_Task(vmconf)
	WaitForTasks([task], service_instance)



def set_vncpassword(vm, service_instance, vncpassword):
	print "Setting VNC password for ", vm.config.name
	vncp = vim.option.OptionValue(key='RemoteDisplay.vnc.enabled',value='True')
	vncp = vim.option.OptionValue(key='RemoteDisplay.vnc.password',value=vncpassword)
	vmconf = vim.vm.ConfigSpec()
	vmconf.extraConfig  = [vncp]
	task = vm.ReconfigVM_Task(vmconf)
	WaitForTasks([task], service_instance)
	# enable vnc
	vncp = vim.option.OptionValue(key='RemoteDisplay.vnc.enabled',value='True')
	vmconf = vim.vm.ConfigSpec()
	vmconf.extraConfig  = [vncp]
	task = vm.ReconfigVM_Task(vmconf)
	WaitForTasks([task], service_instance)


def set_vncport(vm, service_instance, vncport):
	print "Setting VNC port for ", vm.config.name
	vncp = vim.option.OptionValue(key='RemoteDisplay.vnc.port',value=vncport)
	vmconf = vim.vm.ConfigSpec()
	vmconf.extraConfig  = [vncp]
	task = vm.ReconfigVM_Task(vmconf)
	WaitForTasks([task], service_instance)
	# enable vnc
	vncp = vim.option.OptionValue(key='RemoteDisplay.vnc.enabled',value='True')
	vmconf = vim.vm.ConfigSpec()
	vmconf.extraConfig  = [vncp]
	task = vm.ReconfigVM_Task(vmconf)
	WaitForTasks([task], service_instance)



def disable_vnc(vm, service_instance):
	print "Disabling VNC for ", vm.config.name
	vncp = vim.option.OptionValue(key='RemoteDisplay.vnc.enabled',value='False')
	vmconf = vim.vm.ConfigSpec()
	vmconf.extraConfig  = [vncp]
	task = vm.ReconfigVM_Task(vmconf)
	WaitForTasks([task], service_instance)




def create_dc(service_instance, name, folder=None):
	content = service_instance.RetrieveContent()
	if not name:
		sys.stdout.write("error=" + str("Datacenter name missing."))
		print
		return
	for edc in content.rootFolder.childEntity:
		if edc.name == name:
			sys.stdout.write("error=" + str("Datacenter %s already exists" % name))
			print
			return edc
	if len(name) > 79:
		sys.stdout.write("error=" + str("Datacenter name must be less 80 characters."))
		print
		return
	if folder is None:
		folder = service_instance.content.rootFolder
	if folder is not None and isinstance(folder, vim.Folder):
		# print("Creating Datacenter %s " % name )
		dc = folder.CreateDatacenter(name=name)
		return dc


def destroy_dc(service_instance, name):
	content = service_instance.RetrieveContent()
	for dc in content.rootFolder.childEntity:
		if dc.name == name:
			task = dc.vmFolder.UnregisterAndDestroy();
			task = dc.Destroy_Task();
			WaitForTasks([task], service_instance)
			print("Destroyed Datacenter  %s " % name )





def create_cluster(service_instance, name, cluster):
	content = service_instance.RetrieveContent()
	if len(cluster) > 79:
		print("Cluster name must be less 80 characters.")
		return
	for dc in content.rootFolder.childEntity:
		if dc.name == name:
			print("Found Datacenter  %s " % name)
			spec = vim.cluster.ConfigSpecEx()
			host_folder = dc.hostFolder
			print("Creating Cluster %s " % cluster )
			cluster = host_folder.CreateClusterEx(name=cluster, spec=spec)
			return cluster




def destroy_cluster(service_instance, name, cluster):
	content = service_instance.RetrieveContent()
	if name:
		for dc in content.rootFolder.childEntity:
			if dc.name == name:
				for c in dc.hostFolder.childEntity:
					if isinstance(c, vim.ClusterComputeResource):
						if c.name == cluster:
							print("Destroying Cluster  %s " % cluster )
							task = c.Destroy_Task();
							WaitForTasks([task], service_instance)
	else:
		for dc in content.rootFolder.childEntity:
			for c in dc.hostFolder.childEntity:
				if isinstance(c, vim.ClusterComputeResource):
					if c.name == cluster:
						print("Destroying Cluster  %s " % cluster )
						task = c.Destroy_Task();
						WaitForTasks([task], service_instance)








def list_cluster(service_instance, name):
	content = service_instance.RetrieveContent()
	if name:
		for dc in content.rootFolder.childEntity:
			if dc.name == name:
				#print dc.name
				#pprint(getmembers(dc.hostFolder.childEntity[0]))
				for c in dc.hostFolder.childEntity:
					if isinstance(c, vim.ClusterComputeResource):
						# this is a cluster not a host
						print c.name
	else:
		for dc in content.rootFolder.childEntity:
			for c in dc.hostFolder.childEntity:
				if isinstance(c, vim.ClusterComputeResource):
					# this is a cluster not a host
					print c.name
		






def get_host_ssl(ip):
	p1 = subprocess.Popen(('echo', '-n'), stdout=subprocess.PIPE, stderr=subprocess.PIPE)
	p2 = subprocess.Popen(('openssl', 's_client', '-connect', '{0}:443'.format(ip)), stdin=p1.stdout, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
	p3 = subprocess.Popen(('openssl', 'x509', '-noout', '-fingerprint', '-sha1'), stdin=p2.stdout, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
	out = p3.stdout.read()
	ssl = out.split('=')[-1].strip()
	return ssl



def add_host_to_cluster(service_instance, name, cluster, ip, hostname, username, password):
	content = service_instance.RetrieveContent()
	for dc in content.rootFolder.childEntity:
		if dc.name == name:
			print dc.name
			print("Found Datacenter  %s " % name )
			for c in dc.hostFolder.childEntity:
				print c.name
				if c.name == cluster:
					print("Found Cluster  %s " % cluster )
					try:
						ssl=get_host_ssl(ip)
						print("Got ssl from host %s " % ssl )
						hostspec = vim.host.ConnectSpec(hostName=hostname, userName=username, sslThumbprint=ssl, password=password, force=True, managementIp=ip, port=443)
						print("Hostspec %s " % hostspec )
						task=c.AddHost(spec=hostspec, asConnected=True)
					except vmodl.MethodFault as error:
						print "Caught vmodl fault : " + error.msg
						return -1
					WaitForTasks([task], service_instance)
					content = service_instance.RetrieveContent()
					host = get_vim_obj_by_name(content, [vim.HostSystem], hostname)
					return host





def add_host_to_dc(service_instance, name, ip, hostname, username, password):
	content = service_instance.RetrieveContent()
	for dc in content.rootFolder.childEntity:
		if dc.name == name:
			print dc.name
			print("Found Datacenter  %s " % name )
			try:
				ssl=get_host_ssl(ip)
				print("Got ssl from host %s " % ssl )
				hostspec = vim.host.ConnectSpec(hostName=hostname, userName=username, sslThumbprint=ssl, password=password, force=True, managementIp=ip, port=443)
				print("Hostspec %s " % hostspec )
				task=dc.hostFolder.AddStandaloneHost(spec=hostspec, addConnected=True)
			except vmodl.MethodFault as error:
				print "Caught vmodl fault : " + error.msg
				return -1
			WaitForTasks([task], service_instance)
			content = service_instance.RetrieveContent()
			host = get_vim_obj_by_name(content, [vim.HostSystem], hostname)
			return host





def add_nas_to_host(service_instance, host, nasip, naspath, datastore):
		content = service_instance.content
		for dc in content.rootFolder.childEntity:
			for c in dc.hostFolder.childEntity:
				if isinstance(c, vim.ComputeResource):
					for h in c.host:
						if isinstance(h, vim.HostSystem):
							if h.name == host:
								print "Adding NAS datastore to ESX Host " + h.name
								spec = vim.host.NasVolume.Specification()
								spec.remoteHost=nasip
								spec.remotePath=naspath
								spec.localPath=datastore
								spec.accessMode="readWrite"
								datastore=h.configManager.datastoreSystem.CreateNasDatastore(spec)
								return datastore



def add_vswitch(service_instance, host, vswitchname, numports, nicname):
	content=service_instance.RetrieveContent()
	for Host in get_vim_objects(content,vim.HostSystem):
		if Host.name == host:
			vsspec = vim.host.VirtualSwitch.Specification()
			vsspec.numPorts = int(numports)
			if nicname:
				vsspec.bridge = vim.host.VirtualSwitch.BondBridge(nicDevice=[nicname])
				#vsspec.bridge = vim.host.VirtualSwitch.SimpleBridge(nicDevice='key')
			Host.configManager.networkSystem.AddVirtualSwitch(vswitchName=vswitchname, spec=vsspec)
			print "Added vswitch ", vswitchname


def update_vswitch(service_instance, host, vswitchname, numports, nicname):
	content=service_instance.RetrieveContent()
	for Host in get_vim_objects(content,vim.HostSystem):
		if Host.name == host:
			vsspec = vim.host.VirtualSwitch.Specification()
			vsspec.numPorts = int(numports)
			if nicname:
				vsspec.bridge = vim.host.VirtualSwitch.BondBridge(nicDevice=[nicname])
				#vsspec.bridge = vim.host.VirtualSwitch.SimpleBridge(nicDevice='key')
			Host.configManager.networkSystem.UpdateVirtualSwitch(vswitchName=vswitchname, spec=vsspec)
			print "Updated vswitch ", vswitchname



def remove_vswitch_nic(service_instance, host, vswitchname, nicname):
	content=service_instance.RetrieveContent()
	for Host in get_vim_objects(content,vim.HostSystem):
		if Host.name == host:
			for vswitch in Host.config.network.vswitch:
				if vswitch.name == vswitchname:
					numports = vswitch.numPorts
					pnics = []
					for vn in vswitch.spec.policy.nicTeaming.nicOrder.activeNic:
						if vn != nicname:
							pnics.append(vn)

					vsspec = vim.host.VirtualSwitch.Specification()
					vsspec.numPorts = int(numports)
					if pnics:
						vsspec.bridge = vim.host.VirtualSwitch.BondBridge(nicDevice=pnics)

					Host.configManager.networkSystem.UpdateVirtualSwitch(vswitchName=vswitchname, spec=vsspec)
					print "Removed nic from vswitch ", nicname


def add_vswitch_nic(service_instance, host, vswitchname, nicname):
	content=service_instance.RetrieveContent()
	for Host in get_vim_objects(content,vim.HostSystem):
		if Host.name == host:
			for vswitch in Host.config.network.vswitch:
				if vswitch.name == vswitchname:
					numports = vswitch.numPorts
					pnics = []
					for vn in vswitch.spec.policy.nicTeaming.nicOrder.activeNic:
						pnics.append(vn)

					pnics.append(nicname)
					vsspec = vim.host.VirtualSwitch.Specification()
					vsspec.numPorts = int(numports)
					if pnics:
						vsspec.bridge = vim.host.VirtualSwitch.BondBridge(nicDevice=pnics)

					Host.configManager.networkSystem.UpdateVirtualSwitch(vswitchName=vswitchname, spec=vsspec)
					print "Added nic from vswitch ", nicname





def add_portgroup(service_instance, host, pgname, vswitchname, vlan):
	content=service_instance.RetrieveContent()
	for Host in get_vim_objects(content,vim.HostSystem):
		if Host.name == host:
			spolicy = vim.host.NetworkPolicy.SecurityPolicy()
			spolicy.allowPromiscuous = True
			spolicy.forgedTransmits = True
			spolicy.macChanges = False
			pgspec = vim.host.PortGroup.Specification()
			pgspec.name = pgname
			pgspec.vlanId = int(vlan)
			pgspec.vswitchName = vswitchname
			pgspec.policy = vim.host.NetworkPolicy(security=spolicy)
			Host.configManager.networkSystem.AddPortGroup(portgrp=pgspec)
			print "Added portgroup", pgname





def remove_vswitch(service_instance, host, vswitchname):
	content=service_instance.RetrieveContent()
	for Host in get_vim_objects(content,vim.HostSystem):
		if Host.name == host:
			Host.configManager.networkSystem.RemoveVirtualSwitch(vswitchName=vswitchname)
			print "Removed vswitch ", vswitchname


def remove_portgroup(service_instance, host, pgname):
	content=service_instance.RetrieveContent()
	for Host in get_vim_objects(content,vim.HostSystem):
		if Host.name == host:
			Host.configManager.networkSystem.RemovePortGroup(pgName=pgname)
			print "Removed portgroup", pgname





			
def resourcepool_create(service_instance, parent, name, cpuexpandableReservation, cpulimit, cpureservation, cpushares, cpulevel, memoryexpandableReservation, memorylimit, memoryreservation, memoryshares, memorylevel):

	#cpuAllocation = vim.ResourceAllocationInfo()
	#cpuAllocation.expandableReservation = False
	#cpuAllocation.limit = -1
	#cpuAllocation.reservation = 1000
	#cpuShareInfo = vim.SharesInfo()
	#cpuShareInfo.shares = 1000
	#cpuSharesLevel = vim.SharesLevel('normal');
	#cpuShareInfo.level = cpuSharesLevel
	#cpuAllocation.shares = cpuShareInfo
	#print cpuAllocation

	#memoryAllocation = vim.ResourceAllocationInfo()
	#memoryAllocation.expandableReservation = False
	#memoryAllocation.limit = -1
	#memoryAllocation.reservation = 1000
	#memoryShareInfo = vim.SharesInfo()
	#memoryShareInfo.shares = 1000
	#memorySharesLevel = vim.SharesLevel('normal');
	#memoryShareInfo.level = memorySharesLevel
	#memoryAllocation.shares = memoryShareInfo
	#print memoryAllocation


	cpuAllocation = vim.ResourceAllocationInfo()
	cpuAllocation.expandableReservation = cpuexpandableReservation
	cpuAllocation.limit = cpulimit
	cpuAllocation.reservation = int(cpureservation)
	cpuShareInfo = vim.SharesInfo()
	cpuShareInfo.shares = int(cpushares)
	cpuSharesLevel = vim.SharesLevel(cpulevel);
	cpuShareInfo.level = cpuSharesLevel
	cpuAllocation.shares = cpuShareInfo
	#print cpuAllocation

	memoryAllocation = vim.ResourceAllocationInfo()
	memoryAllocation.expandableReservation = memoryexpandableReservation
	memoryAllocation.limit = memorylimit
	memoryAllocation.reservation = int(memoryreservation)
	memoryShareInfo = vim.SharesInfo()
	memoryShareInfo.shares = int(memoryshares)
	memorySharesLevel = vim.SharesLevel(memorylevel);
	memoryShareInfo.level = memorySharesLevel
	memoryAllocation.shares = memoryShareInfo
	#print memoryAllocation


	rpspec = vim.ResourceConfigSpec();
	rpspec.cpuAllocation = cpuAllocation
	rpspec.memoryAllocation = memoryAllocation

	#print rpspec
	content=service_instance.RetrieveContent()
	for respool in get_vim_objects(content,vim.ResourcePool):
		if respool.name == parent:
			print "Found parent resourcepool"
			newresp = respool.CreateResourcePool(name, rpspec)
			print "Created resourcepool " + newresp.name


def resourcepool_update(service_instance, parent, name, cpuexpandableReservation, cpulimit, cpureservation, cpushares, cpulevel, memoryexpandableReservation, memorylimit, memoryreservation, memoryshares, memorylevel):

	cpuAllocation = vim.ResourceAllocationInfo()
	cpuAllocation.expandableReservation = cpuexpandableReservation
	cpuAllocation.limit = cpulimit
	cpuAllocation.reservation = int(cpureservation)
	cpuShareInfo = vim.SharesInfo()
	cpuShareInfo.shares = int(cpushares)
	cpuSharesLevel = vim.SharesLevel(cpulevel);
	cpuShareInfo.level = cpuSharesLevel
	cpuAllocation.shares = cpuShareInfo

	memoryAllocation = vim.ResourceAllocationInfo()
	memoryAllocation.expandableReservation = memoryexpandableReservation
	memoryAllocation.limit = memorylimit
	memoryAllocation.reservation = int(memoryreservation)
	memoryShareInfo = vim.SharesInfo()
	memoryShareInfo.shares = int(memoryshares)
	memorySharesLevel = vim.SharesLevel(memorylevel);
	memoryShareInfo.level = memorySharesLevel
	memoryAllocation.shares = memoryShareInfo

	rpspec = vim.ResourceConfigSpec();
	rpspec.cpuAllocation = cpuAllocation
	rpspec.memoryAllocation = memoryAllocation

	content=service_instance.RetrieveContent()
	for respool in get_vim_objects(content,vim.ResourcePool):
		if respool.name == name:
			print "Found resourcepool " + name
			newresp = respool.UpdateConfig(name, rpspec)
			print "Updated resourcepool " + name



def resourcepool_destroy(service_instance, name):
	content=service_instance.RetrieveContent()
	for respool in get_vim_objects(content,vim.ResourcePool):
		if respool.name == name:
			task = respool.Destroy()
			WaitForTasks([task], service_instance)
			print "Destroyed " + name


