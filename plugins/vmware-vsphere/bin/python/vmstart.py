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

import libvmtask

from inspect import getmembers
from pprint import pprint
import argparse
import sys

requests.packages.urllib3.disable_warnings()


def main():
	parser = argparse.ArgumentParser(description='vCenter login')
	parser.add_argument('-s', '--host', required=True, action='store', help='vSphere IP')
	parser.add_argument('-o', '--port', type=int, default=443, action='store', help='vSphere Port')
	parser.add_argument('-u', '--user', required=True, action='store', help='User name')
	parser.add_argument('-p', '--password', required=True, action='store', help='Password')
	parser.add_argument('-n', '--name', required=False, action='store', help='VM name')
	args = parser.parse_args()

	try:
		service_instance = connect.SmartConnect(host=args.host,
												user=args.user,
												pwd=args.password,
												port=int(args.port))
		atexit.register(connect.Disconnect, service_instance)


		if args.name:

			content = service_instance.content
			objView = content.viewManager.CreateContainerView(content.rootFolder, [vim.VirtualMachine], True)
			vmlist = objView.view
			objView.Destroy()

			for vm in vmlist:
				if vm.name == args.name:
					print "Powering on VM " + vm.name
					task = vm.PowerOn()
					libvmtask.WaitForTasks([task], service_instance)
					print "Powered on VM " + vm.name

					#libvmtask.list_files(service_instance, '*.vmdk')
					#libvmtask.list_files(service_instance, '*.iso')
					#libvmtask.detach_disk(vm, service_instance, '[NAS] hulli/hulli.vmdk')
					#libvmtask.attach_disk(vm, service_instance, '[NAS] hulli/hulli.vmdk')

		else:
			raise SystemExit("Please provide a VM name")


		return 0

	except vmodl.MethodFault as error:
		print("ERROR: " + error.msg)
		sys.exit(1)


if __name__ == "__main__":
	main()
