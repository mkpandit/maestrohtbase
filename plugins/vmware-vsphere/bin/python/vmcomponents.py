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

	args = parser.parse_args()

	try:
		service_instance = connect.SmartConnect(host=args.host,
												user=args.user,
												pwd=args.password,
												port=int(args.port))
		atexit.register(connect.Disconnect, service_instance)
	
		content=service_instance.RetrieveContent()
		# vswitches
		for Host in libvmtask.get_vim_objects(content,vim.HostSystem):
			for vswitch in Host.config.network.vswitch:
				#print(vswitch.name)
				#print(vswitch)
				for portgroup in vswitch.portgroup:
					#print str(portgroup)
					#t = str(portgroup.split("key-vim.host.PortGroup-",1))
					#print portgroup.split("-",2)[2]
					pgname = str(portgroup.split("-",2)[2])
					pgname = pgname.replace(" ", "@")
					sys.stdout.write("t=vs")
					sys.stdout.write("|name=" + pgname)
					sys.stdout.write("|vswitch=" + str(vswitch.name))
					sys.stdout.write("|numPorts=" + str(vswitch.numPorts))
					sys.stdout.write("|numPortsAvailable=" + str(vswitch.numPortsAvailable))
					sys.stdout.write("|mtu=" + str(vswitch.mtu))
					sys.stdout.write("|key=" + str(vswitch.key))

					print

			datastore = Host.configManager.storageSystem
			host_volumes = datastore.fileSystemVolumeInfo.mountInfo
			for v in host_volumes:
				#print v
				#print v.volume
				if v.volume.name:
					sys.stdout.write("t=ds")
					sys.stdout.write("|name=" + str(v.volume.name))
					sys.stdout.write("|type=" + str(v.volume.type))
					sys.stdout.write("|capacity=" + str(v.volume.capacity/1024/1024))
					sys.stdout.write("|remoteHost=" + str(v.volume.remoteHost))
					sys.stdout.write("|remotePath=" + str(v.volume.remotePath))
					print


		# resource pools
		for respool in libvmtask.get_vim_objects(content,vim.ResourcePool):
			#print(respool.summary.name)
			#print(respool.name)
			sys.stdout.write("t=rs")
			sys.stdout.write("|name=" + str(respool.summary.name))
			print

		# datacenter
		content = service_instance.RetrieveContent()
		for dc in content.rootFolder.childEntity:
			sys.stdout.write("t=dc")
			sys.stdout.write("|name=" + str(dc.name))
			print

		return 0

	except vmodl.MethodFault as error:
		print("ERROR: " + error.msg)
		sys.exit(1)

# Start program
if __name__ == "__main__":
	main()
