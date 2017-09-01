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

import atexit
import requests
from pyVim import connect
from pyVmomi import vim, vmodl
from inspect import getmembers
from pprint import pprint
import argparse
import sys

requests.packages.urllib3.disable_warnings()



def list_vm(virtual_machine, datacenter, cluster, host):

	if not virtual_machine:
		return
	if not hasattr(virtual_machine, 'summary'):
		return
	if not hasattr(virtual_machine.summary, 'config'):
		return
	if not hasattr(virtual_machine.summary, 'runtime'):
		return
	if not hasattr(virtual_machine.summary, 'quickStats'):
		return
	if not hasattr(virtual_machine.summary, 'guest'):
		return
	if not hasattr(virtual_machine.summary.runtime, 'host'):
		return
	if not hasattr(virtual_machine.summary.runtime.host, 'summary'):
		return

	summary = virtual_machine.summary

	#   print(summary.config.name)
	#	pprint(getmembers(virtual_machine.config.hardware.device[1]))
	#	pprint(getmembers(summary.runtime))
	#	pprint(getmembers(summary))
	#	pprint(getmembers(summary.runtime.host))
	#	print(summary.runtime.host.summary.managementServerIp)
	#	print(summary.runtime.host.summary.config.name)
	#	pprint(getmembers(virtual_machine))

	boot = ''
	vnc = 0
	vncp = ''
	if hasattr(virtual_machine.config, 'extraConfig'):
		for option in virtual_machine.config.extraConfig:
			#print option
			if option.key == 'bios.bootDeviceClasses':
				boot = option.value
			if option.key == 'RemoteDisplay.vnc.port':
				vnc = int(option.value)
			if option.key == 'RemoteDisplay.vnc.password':
				vncp = option.value


	mac = []
	macstr = ''
	vmdk = []
	vmdkstr = ''
	iso = []
	isostr = ''
	network = []
	networkstr = ''

	if hasattr(virtual_machine.config, 'hardware'):
		for dev in virtual_machine.config.hardware.device:
	#		print dev
			# get the mac address
			if hasattr(dev, 'macAddress'):
				mac.append(dev.macAddress)
			# get the vmdk
			if isinstance(dev, vim.vm.device.VirtualDisk):
				if hasattr(dev, 'backing'):
					if hasattr(dev.backing, 'fileName'):
						vmdk.append(dev.backing.fileName)
			# get the iso
			if isinstance(dev, vim.vm.device.VirtualCdrom):
				if hasattr(dev, 'backing'):
					if hasattr(dev.backing, 'fileName'):
						iso.append(dev.backing.fileName)

			# get nic
			if isinstance(dev, vim.vm.device.VirtualE1000):
				if hasattr(dev, 'deviceInfo'):
					if hasattr(dev.deviceInfo, 'summary'):
						netappend = dev.macAddress + "@e1000@" + dev.deviceInfo.summary
						network.append(netappend)
			if isinstance(dev, vim.vm.device.VirtualVmxnet3):
				if hasattr(dev, 'deviceInfo'):
					if hasattr(dev.deviceInfo, 'summary'):
						netappend = dev.macAddress + "@vmx@" + dev.deviceInfo.summary
						network.append(netappend)
			if isinstance(dev, vim.vm.device.VirtualPCNet32):
				if hasattr(dev, 'deviceInfo'):
					if hasattr(dev.deviceInfo, 'summary'):
						netappend = dev.macAddress + "@pc32@" + dev.deviceInfo.summary
						network.append(netappend)





	# host

	sys.stdout.write("name=" + str(summary.config.name))
	sys.stdout.write("|hostip=" + str(summary.runtime.host.summary.managementServerIp))
	sys.stdout.write("|memorySizeMB=" + str(summary.config.memorySizeMB))
	sys.stdout.write("|numCpu=" + str(summary.config.numCpu))
	sys.stdout.write("|numEthernetCards=" + str(summary.config.numEthernetCards))
	sys.stdout.write("|uuid=" + str(summary.config.uuid))
	sys.stdout.write("|instanceUuid=" + str(summary.config.instanceUuid))
	sys.stdout.write("|guestId=" + str(summary.config.guestId))
	sys.stdout.write("|guestFullName=" + str(summary.config.guestFullName))
	sys.stdout.write("|guestMemoryUsage=" + str(summary.quickStats.guestMemoryUsage))
	sys.stdout.write("|uptimeSeconds=" + str(summary.quickStats.uptimeSeconds))
	sys.stdout.write("|powerState=" + str(summary.runtime.powerState))
	sys.stdout.write("|ipAddress=" + str(summary.guest.ipAddress))
	sys.stdout.write("|hostName=" + str(summary.guest.hostName))
	sys.stdout.write("|resourcepool=" + str(virtual_machine.resourcePool.name))
	sys.stdout.write("|macAddress=")
	for item in mac:
		macstr = macstr + item + ","
	macstr = macstr[:-1]
	sys.stdout.write(macstr)
	sys.stdout.write("|fileName=")
	for item in vmdk:
		vmdkstr = vmdkstr + item + ","
	vmdkstr = vmdkstr[:-1]
	sys.stdout.write(vmdkstr)

	sys.stdout.write("|iso=")
	for item in iso:
		isostr = isostr + item + ","
	isostr = isostr[:-1]
	sys.stdout.write(isostr)

	sys.stdout.write("|network=")
	for item in network:
		networkstr = networkstr + item + ","
	networkstr = networkstr[:-1]
	sys.stdout.write(networkstr)


	sys.stdout.write("|boot=" + boot)
	sys.stdout.write("|vncport=" + str(vnc))
	sys.stdout.write("|vncpass=" + vncp)

	sys.stdout.write("|datacenter=" + str(datacenter.name))
	sys.stdout.write("|cluster=" + str(cluster.name))
	sys.stdout.write("|host=" + str(host.name))
	sys.stdout.write("|resourcepool=" + str(virtual_machine.resourcePool.name))

	print



def main():
	parser = argparse.ArgumentParser(description='vCenter login')
	parser.add_argument('-s', '--host', required=True, action='store', help='vSphere IP')
	parser.add_argument('-o', '--port', type=int, default=443, action='store', help='vSphere Port')
	parser.add_argument('-u', '--user', required=True, action='store', help='User name')
	parser.add_argument('-p', '--password', required=False, action='store', help='Password')
	parser.add_argument('-n', '--name', required=False, action='store', help='VM Name to list')
	args = parser.parse_args()

	try:
		service_instance = connect.SmartConnect(host=args.host,
												user=args.user,
												pwd=args.password,
												port=int(args.port))
		atexit.register(connect.Disconnect, service_instance)
		content = service_instance.RetrieveContent()

		for dc in content.rootFolder.childEntity:
			for c in dc.hostFolder.childEntity:
				if isinstance(c, vim.ClusterComputeResource):
					for h in c.host:
						if isinstance(h, vim.HostSystem):
							for vm in h.vm:
								if args.name:
									if args.name == vm.name:
										list_vm(vm, dc, c, h)
								else:
									list_vm(vm, dc, c, h)

				elif isinstance(c, vim.ComputeResource):
					for h in c.host:
						if isinstance(h, vim.HostSystem):
							for vm in h.vm:
								if args.name:
									if args.name == vm.name:
										list_vm(vm, dc, h, h)
								else:
									list_vm(vm, dc, h, h)

		return 0

	except vmodl.MethodFault as error:
		print("ERROR: " + error.msg)
		sys.exit(1)


if __name__ == "__main__":
	main()
