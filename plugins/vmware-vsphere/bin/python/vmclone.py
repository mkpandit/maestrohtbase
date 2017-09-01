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


def main():
	parser = argparse.ArgumentParser(description='vCenter login')
	parser.add_argument('-s', '--host', required=True, action='store', help='vSphere IP')
	parser.add_argument('-o', '--port', type=int, default=443, action='store', help='vSphere Port')
	parser.add_argument('-u', '--user', required=True, action='store', help='User name')
	parser.add_argument('-p', '--password', required=True, action='store', help='Password')
	parser.add_argument('-n', '--name', required=True, action='store', help='VM Name')
	parser.add_argument('-d', '--datastore', required=False, action='store', help='Datastore Name')
	parser.add_argument('-q', '--resourcepool', required=False, action='store', help='Resource-Pool name, if empty the first available resource pool will be used.')
	parser.add_argument('-c', '--datacenter', required=False, action='store', help='Datacenter name, if empty the first available resource pool will be used.')
	parser.add_argument('-t', '--template', required=True, action='store', help='VM Name to clone or template name.')


	args = parser.parse_args()


	try:
		service_instance = connect.SmartConnect(host=args.host,
												user=args.user,
												pwd=args.password,
												port=int(args.port))
		atexit.register(connect.Disconnect, service_instance)

		content = service_instance.RetrieveContent()

		if args.datacenter:
			print "Using datacenter " + libvmtask.validate_input(args.datacenter)
			datacenter = libvmtask.get_vim_obj_by_name(content, [vim.Datacenter], libvmtask.validate_input(args.datacenter))
		else:
			datacenter = content.rootFolder.childEntity[0]

		destfolder = datacenter.vmFolder
		vmfolder = datacenter.vmFolder
		hosts = datacenter.hostFolder.childEntity

		relospec = vim.vm.RelocateSpec()

		if args.datastore:
			datastore= libvmtask.get_vim_obj_by_name(content, [vim.Datastore], libvmtask.validate_input(args.datastore))
			if datastore:
				print "Using datastore " + libvmtask.validate_input(args.datastore)
				relospec.datastore = datastore
			else:
				print "Cloud not find datastore, using datastore of origin VM"

		if args.resourcepool:
			print("using resource pool " + libvmtask.validate_input(args.resourcepool))
			resource_pool = libvmtask.get_vim_obj_by_name(content, [vim.ResourcePool], libvmtask.validate_input(args.resourcepool))
		else:
			print("using first available resource pool");
			resource_pool = hosts[0].resourcePool
			#resource_pool = cluster.resourcePool
		if resource_pool:
			relospec.pool = resource_pool
		else:
			print "Cloud not find resource pool, using resource pool of origin VM"

		clonespec = vim.vm.CloneSpec()
		clonespec.location = relospec
		clonespec.powerOn = False

		template = libvmtask.get_vim_obj_by_name(content, [vim.VirtualMachine], libvmtask.validate_input(args.template))
		if not template:
			print "VM or template not found"
			sys.exit(1)
		print "Cloning VM"
		task = template.Clone(folder=destfolder, name=libvmtask.validate_input(args.name), spec=clonespec)
		libvmtask.WaitForTasks([task], service_instance)

		clonevm = libvmtask.get_vim_obj_by_name(content, [vim.VirtualMachine], libvmtask.validate_input(args.name))
		libvmtask.remove_all_nic(clonevm, service_instance)
		libvmtask.disable_vnc(clonevm, service_instance)


		return 0

	except vmodl.MethodFault as error:
		print("ERROR: " + error.msg)
		sys.exit(1)

# Start program
if __name__ == "__main__":
	main()
