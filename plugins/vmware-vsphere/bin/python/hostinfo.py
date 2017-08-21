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
from pyVim.connect import SmartConnect, Disconnect
from pyVmomi import vim, vmodl
from inspect import getmembers
from pprint import pprint
import sys
import argparse

requests.packages.urllib3.disable_warnings()


def main():
	parser = argparse.ArgumentParser(description='vCenter login')
	parser.add_argument('-s', '--host', required=True, action='store', help='vSphere IP')
	parser.add_argument('-o', '--port', type=int, default=443, action='store', help='vSphere Port')
	parser.add_argument('-u', '--user', required=True, action='store', help='User name')
	parser.add_argument('-p', '--password', required=False, action='store', help='Password')
	args = parser.parse_args()

	try:
		service_instance = connect.SmartConnect(host=args.host,
												user=args.user,
												pwd=args.password,
												port=int(args.port))

		atexit.register(connect.Disconnect, service_instance)

		content = service_instance.content

		for dc in content.rootFolder.childEntity:
			for c in dc.hostFolder.childEntity:
				for host in c.host:
					if host.summary.managementServerIp == args.host:
						sys.stdout.write("managementServerIp=" + str(host.summary.managementServerIp))
						sys.stdout.write("|overallCpuUsage=" + str(host.summary.quickStats.overallCpuUsage))
						sys.stdout.write("|overallMemoryUsage=" + str(host.summary.quickStats.overallMemoryUsage))
						sys.stdout.write("|uptime=" + str(host.summary.quickStats.uptime))
						sys.stdout.write("|memorySize=" + str(host.summary.hardware.memorySize/1024/1024))
						sys.stdout.write("|cpuModel=" + str(host.summary.hardware.cpuModel))
						sys.stdout.write("|cpuMhz=" + str(host.summary.hardware.cpuMhz))
						sys.stdout.write("|numNics=" + str(host.summary.hardware.numNics))
						sys.stdout.write("|numHBAs=" + str(host.summary.hardware.numHBAs))
						print

					#pprint(getmembers(host))
					for vm in host.vm:
						#print vm.summary.guest.ipAddress
						if vm.summary.guest.ipAddress == args.host:
							sys.stdout.write("managementServerIp=" + str(vm.summary.guest.ipAddress))
							sys.stdout.write("|overallCpuUsage=" + str(vm.summary.quickStats.overallCpuUsage))
							sys.stdout.write("|overallMemoryUsage=" + str(vm.summary.quickStats.guestMemoryUsage))
							sys.stdout.write("|uptime=" + str(vm.summary.quickStats.uptimeSeconds))
							sys.stdout.write("|memorySize=" + str(vm.summary.config.memorySizeMB))
							sys.stdout.write("|numCpu=" + str(vm.summary.config.numCpu))
							sys.stdout.write("|numNics=" + str(vm.summary.config.numEthernetCards))
							print





#		obj_view = content.viewManager.CreateContainerView(content.rootFolder,[vim.HostSystem],True)
#		host_list = obj_view.view
#		hosts = []
#		for host in host_list:
#			#print host.summary
#			if host.summary.managementServerIp == args.host:
#				sys.stdout.write("managementServerIp=" + str(host.summary.managementServerIp))
#				sys.stdout.write("|overallCpuUsage=" + str(host.summary.quickStats.overallCpuUsage))
#				sys.stdout.write("|overallMemoryUsage=" + str(host.summary.quickStats.overallMemoryUsage))
#				sys.stdout.write("|uptime=" + str(host.summary.quickStats.uptime))
#				sys.stdout.write("|memorySize=" + str(host.summary.hardware.memorySize/1024/1024))
#				sys.stdout.write("|cpuModel=" + str(host.summary.hardware.cpuModel))
#				sys.stdout.write("|cpuMhz=" + str(host.summary.hardware.cpuMhz))
#				sys.stdout.write("|numNics=" + str(host.summary.hardware.numNics))
#				sys.stdout.write("|numHBAs=" + str(host.summary.hardware.numHBAs))
#				print
#


	except vmodl.MethodFault as error:
		return -1

	return 0

if __name__ == "__main__":
	main()
