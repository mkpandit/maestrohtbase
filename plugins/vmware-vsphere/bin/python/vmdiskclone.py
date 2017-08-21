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


#### om /usr/local/lib/python2.7/dist-packages/requests/adapters.py verify = False

import requests
import atexit
from pyVim import connect
from pyVmomi import vim, vmodl

import libvmtask

from inspect import getmembers
from pprint import pprint
import argparse
import sys
requests.packages.urllib3.disable_warnings()


#import ssl
#try:
#	_create_unverified_https_context = ssl._create_unverified_context
#except AttributeError:
#	# Legacy Python that doesn't verify HTTPS certificates by default
#	pass
#else:
#	# Handle target environment that doesn't support HTTPS verification
#	ssl._create_default_https_context = _create_unverified_https_context


#import ssl
#set ssl.SSLContext.verify_mode = CERT_NONE

def main():

	parser = argparse.ArgumentParser(description='vCenter login')
	parser.add_argument('-s', '--host', required=True, action='store', help='vSphere IP')
	parser.add_argument('-o', '--port', type=int, default=443, action='store', help='vSphere Port')
	parser.add_argument('-u', '--user', required=True, action='store', help='User name')
	parser.add_argument('-p', '--password', required=True, action='store', help='Password')
	parser.add_argument('-d', '--diskpath', required=False, action='store', help='Path to vmdk e.g. "[NAS] hulli/hulli.vmdk"')
	parser.add_argument('-c', '--clonepath', required=False, action='store', help='Path to vmdk e.g. "[NAS] hulla/hulla.vmdk"')
	args = parser.parse_args()

	try:
		service_instance = connect.SmartConnect(host=args.host,
												user=args.user,
												pwd=args.password,
												port=int(args.port))
		atexit.register(connect.Disconnect, service_instance)
		content = service_instance.content

		print args.diskpath
		print args.clonepath

		disk_path = args.clonepath
		adapter_type = vim.VirtualDiskManager.VirtualDiskAdapterType.lsiLogic
		disk_type = vim.VirtualDiskManager.VirtualDiskType.preallocated
		capacity_kb = 16 * 1024 * 1024 # some arbitrary size

		spec = vim.VirtualDiskManager.FileBackedVirtualDiskSpec()
		spec.adapterType = adapter_type
		spec.diskType = disk_type
		spec.capacityKb = capacity_kb


		#spec = vim.VirtualDiskManager.VirtualDiskSpec()
		#spec.adapterType = "lsiLogic"
		#spec.diskType = "thick"

		children = content.rootFolder.childEntity
		for child in children:
			print child


		print child.name
		disk_manager = content.virtualDiskManager

		# create working
		#task = disk_manager.CreateVirtualDisk(disk_path, child, spec)
		#libvmtask.WaitForTasks([task], service_instance)

		# delete working
		#task = disk_manager.DeleteVirtualDisk(disk_path, child)
		#libvmtask.WaitForTasks([task], service_instance)


		# not yet implemented !?
		#task = disk_manager.CopyVirtualDisk(args.diskpath, child, args.clonepath, child, spec)
		#libvmtask.WaitForTasks([task], service_instance)






		return 0

	except vmodl.MethodFault as error:
		print("ERROR: " + error.msg)
		sys.exit(1)


if __name__ == "__main__":
	main()
