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
from pyVmomi import vmodl
from pyVmomi import vim

import libvmtask

from inspect import getmembers
from pprint import pprint
import argparse
import sys

import hashlib
import json
import random
import time

requests.packages.urllib3.disable_warnings()





def create_vm(name, service_instance, vm_folder, resource_pool, datastore, guestid, version, boot, memory, cpu, disk, disktype, network, vncport, vncpassword, comment, iso):

	# set vnc extraconfig
	option = vim.option.OptionValue()
	spec = vim.vm.ConfigSpec()
	spec.extraConfig = []
	option.key = 'RemoteDisplay.vnc.enabled'
	option.value = 'True'
	spec.extraConfig.append(option)
	option = vim.option.OptionValue()


	option.key = 'RemoteDisplay.vnc.port'
	option.value = vncport
	spec.extraConfig.append(option)
	option = vim.option.OptionValue()


	option.key = 'RemoteDisplay.vnc.password'
	option.value = vncpassword
	spec.extraConfig.append(option)
	option = vim.option.OptionValue()


	# create vm
	datastore_path = '[' + datastore + '] ' + name
	vmx_file = vim.vm.FileInfo(logDirectory=datastore_path,
							   snapshotDirectory=datastore_path,
							   suspendDirectory=datastore_path,
							   vmPathName=datastore_path)

	config = vim.vm.ConfigSpec(name=name, memoryMB=memory, numCPUs=cpu,
							   files=vmx_file, guestId=guestid,
							   version=version, extraConfig=spec.extraConfig, annotation=comment)

	print "Creating VM {}...".format(name)
	task = vm_folder.CreateVM_Task(config=config, pool=resource_pool)
	libvmtask.WaitForTasks([task], service_instance)

	# get the vm obj
	content = service_instance.RetrieveContent()
	vm = libvmtask.get_vim_obj_by_name(content, [vim.VirtualMachine], name)

	# disk
	libvmtask.add_disk(vm, service_instance, disk, disktype)

	# nic
	network_list = network.split('|')
	for n in network_list:
		nc = n.split(',')
		print "- Adding nic: "
		print nc
		libvmtask.add_nic(vm, service_instance, nc[0], nc[1], nc[2])

	# attach iso/cdrom
	if iso:
		libvmtask.add_iso(vm, service_instance, iso)

	# boot from
	libvmtask.set_boot(vm, service_instance, boot)






def main():
	parser = argparse.ArgumentParser(description='vCenter login')
	parser.add_argument('-s', '--host', required=True, action='store', help='vSphere IP')
	parser.add_argument('-o', '--port', type=int, default=443, action='store', help='vSphere Port')
	parser.add_argument('-u', '--user', required=True, action='store', help='User name')
	parser.add_argument('-p', '--password', required=True, action='store', help='Password')
	parser.add_argument('-n', '--name', required=True, action='store', help='VM Name')
	parser.add_argument('-d', '--datastore', required=True, action='store', help='Datastore Name')
	parser.add_argument('-t', '--cpu', required=True, action='store', help='CPU number')
	parser.add_argument('-l', '--disk', required=True, action='store', help='Disk size in MB')
	parser.add_argument('-y', '--disktype', required=False, action='store', help='Disk type thin/thick, defaults to thin')
	parser.add_argument('-g', '--guestid', required=True, action='store', help='Guest ID')
	parser.add_argument('-x', '--vmx', required=True, action='store', help='VMX file version e.g. vmx-07')
	parser.add_argument('-b', '--boot', required=True, action='store', help='Boot order e.g. local/network/cdrom')
	parser.add_argument('-r', '--memory', required=True, action='store', help='Memory in MB e.g. 1024')
	parser.add_argument('-m', '--network', required=True, action='store', help='The Network configuration string formated as "[vSwitch-name],[nic-type],{MAC address]|[vSwitch-name],[nic-type],{MAC address]|..." e.g. "vSwitch0,e1000,00:50:56:8c:a9:24|vSwitch0,e1000,00:50:56:8c:a9:25"')
	parser.add_argument('-v', '--vncport', required=True, action='store', help='VNC address ip:port e.g. 1.2.3.4:5905')
	parser.add_argument('-w', '--vncpassword', required=True, action='store', help='VNC password')
	parser.add_argument('-c', '--comment', required=True, action='store', help='VM description')
	parser.add_argument('-i', '--iso', required=False, action='store', help='Path to ISO image attached as cdrom')
	parser.add_argument('-q', '--resourcepool', required=False, action='store', help='Resource-Pool name, if empty the first available resource pool will be used.')
	parser.add_argument('-a', '--datacenter', required=False, action='store', help='Datacenter name, if empty the first available resource pool will be used.')

	args = parser.parse_args()


	try:
		service_instance = connect.SmartConnect(host=args.host,
												user=args.user,
												pwd=args.password,
												port=int(args.port))
		atexit.register(connect.Disconnect, service_instance)

		content = service_instance.RetrieveContent()
		if args.datacenter:
			for dc in content.rootFolder.childEntity:
				if dc.name == args.datacenter:
					datacenter = dc
		else:
			datacenter = content.rootFolder.childEntity[0]
		print("using datacenter " + datacenter.name)

		vmfolder = datacenter.vmFolder
		hosts = datacenter.hostFolder.childEntity

		if args.resourcepool:
			print("using resource pool " + args.resourcepool)
			resource_pool = libvmtask.get_vim_obj_by_name(content, [vim.ResourcePool], args.resourcepool)
		else:
			print("using first available resource pool");
			resource_pool = hosts[0].resourcePool
			#cluster = get_obj(content, [vim.ClusterComputeResource], args.resourcepool)
			#resource_pool = cluster.resourcePool

		if not resource_pool:
			print("no res pool found")
			sys.exit(1)

		if args.disktype:
			disktype = args.disktype
		else:
			disktype = 'thin'

		create_vm(args.name, service_instance, vmfolder, resource_pool, args.datastore, args.guestid, args.vmx, args.boot, long(args.memory), int(args.cpu), args.disk, disktype, args.network, args.vncport, args.vncpassword, args.comment, args.iso)

		return 0

	except vmodl.MethodFault as error:
		print("ERROR: " + error.msg)
		sys.exit(1)

# Start program
if __name__ == "__main__":
	main()
