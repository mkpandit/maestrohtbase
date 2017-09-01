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

import libvmtask

from inspect import getmembers
from pprint import pprint
import argparse
import sys

requests.packages.urllib3.disable_warnings()



def destroy_vm_by_name(service_instance, virtual_machine, name, depth=1):
	if hasattr(virtual_machine, 'childEntity'):
		vmList = virtual_machine.childEntity
		for c in vmList:
			destroy_vm_by_name(service_instance, c, name, depth + 1)
		return
	summary = virtual_machine.summary
	if summary.config.name == name:
		sys.stdout.write("name=" + str(summary.config.name))
		sys.stdout.write("|uuid=" + str(summary.config.uuid))
		print("Found: {0}".format(virtual_machine.name))
		print("The current powerState is: {0}".format(virtual_machine.runtime.powerState))
		if format(virtual_machine.runtime.powerState) == "poweredOn":
			print("Attempting to power off {0}".format(virtual_machine.name))
			task = virtual_machine.PowerOffVM_Task()
			libvmtask.WaitForTasks([task], service_instance)
			print("{0}".format(task.info.state))

		print("Destroying VM from vSphere: {0}".format(virtual_machine.name))
		task = virtual_machine.Destroy_Task()
		libvmtask.WaitForTasks([task], service_instance)
		print("Done by name.")



def main():
	parser = argparse.ArgumentParser(description='vCenter login')
	parser.add_argument('-s', '--host', required=True, action='store', help='vSphere IP')
	parser.add_argument('-o', '--port', type=int, default=443, action='store', help='vSphere Port')
	parser.add_argument('-u', '--user', required=True, action='store', help='User name')
	parser.add_argument('-p', '--password', required=True, action='store', help='Password')
	parser.add_argument('-n', '--name', required=False, action='store', help='VM name')
	parser.add_argument('-i', '--uuid', required=False, action='store', help='VM uuid')
	args = parser.parse_args()

	try:
		service_instance = connect.SmartConnect(host=args.host,
												user=args.user,
												pwd=args.password,
												port=int(args.port))
		atexit.register(connect.Disconnect, service_instance)


		if args.uuid:
			virtual_machine = service_instance.content.searchIndex.FindByUuid(None, args.uuid, True, False)
			if virtual_machine is None:
				raise SystemExit("Unable to find VM.")

			print("Found: {0}".format(virtual_machine.name))
			print("The current powerState is: {0}".format(virtual_machine.runtime.powerState))
			if format(virtual_machine.runtime.powerState) == "poweredOn":
				print("Attempting to power off {0}".format(virtual_machine.name))
				task = virtual_machine.PowerOffVM_Task()
				libvmtask.WaitForTasks([task], service_instance)
				print("{0}".format(task.info.state))

			print("Destroying VM from vSphere: {0}".format(virtual_machine.name))
			task = virtual_machine.Destroy_Task()
			libvmtask.WaitForTasks([task], service_instance)
			print("Done by uuid.")


		elif args.name:

			content = service_instance.RetrieveContent()
			children = content.rootFolder.childEntity
			for child in children:
				if hasattr(child, 'vmFolder'):
					datacenter = child
				else:
					continue

				vm_folder = datacenter.vmFolder
				vm_list = vm_folder.childEntity
				for virtual_machine in vm_list:
					destroy_vm_by_name(service_instance, virtual_machine, args.name, 10)

		else:
			raise SystemExit("Please provide a VM name or uuid")


		return 0

	except vmodl.MethodFault as error:
		print("ERROR: " + error.msg)
		sys.exit(1)


if __name__ == "__main__":
	main()
