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
		for respool in libvmtask.get_vim_objects(content,vim.ResourcePool):
			#pprint(getmembers(respool))
			vmliststr = ''
			sys.stdout.write("name=" + str(respool.name))
			sys.stdout.write("|parent=" + str(respool.parent.name))
			sys.stdout.write("|overallStatus=" + str(respool.overallStatus))
			sys.stdout.write("|cpuexpandablereservation=" + str(respool.summary.config.cpuAllocation.expandableReservation))
			sys.stdout.write("|cpureservation=" + str(respool.summary.config.cpuAllocation.reservation))
			sys.stdout.write("|cpulimit=" + str(respool.summary.config.cpuAllocation.limit))
			sys.stdout.write("|cpushares=" + str(respool.summary.config.cpuAllocation.shares.shares))
			sys.stdout.write("|cpulevel=" + str(respool.summary.config.cpuAllocation.shares.level))
			sys.stdout.write("|cpuoverallusage=" + str(respool.summary.runtime.cpu.overallUsage))
			sys.stdout.write("|cpumaxusage=" + str(respool.summary.runtime.cpu.maxUsage))
			sys.stdout.write("|memoryexpandablereservation=" + str(respool.summary.config.memoryAllocation.expandableReservation))
			sys.stdout.write("|memoryreservation=" + str(respool.summary.config.memoryAllocation.reservation))
			sys.stdout.write("|memorylimit=" + str(respool.summary.config.memoryAllocation.limit))
			sys.stdout.write("|memoryshares=" + str(respool.summary.config.memoryAllocation.shares.shares))
			sys.stdout.write("|memorylevel=" + str(respool.summary.config.memoryAllocation.shares.level))
			sys.stdout.write("|memoryoverallusage=" + str(respool.summary.runtime.memory.overallUsage))
			sys.stdout.write("|memorymaxusage=" + str(respool.summary.runtime.memory.maxUsage))
			sys.stdout.write("|vm=")
			for vm in respool.vm:
				vmliststr = vmliststr + vm.name + ","
			vmliststr = vmliststr[:-1]
			sys.stdout.write(vmliststr)

			print

		return 0

	except vmodl.MethodFault as error:
		print("ERROR: " + error.msg)
		sys.exit(1)

# Start program
if __name__ == "__main__":
	main()
