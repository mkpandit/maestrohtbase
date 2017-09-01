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
	parser.add_argument('-q', '--parent', required=True, action='store', help='Parent resourcepool name')
	parser.add_argument('-n', '--name', required=True, action='store', help='Resourcepool name')
	parser.add_argument('-a', '--action', required=False, action='store', help='create/update, default is create')
	parser.add_argument('-c1', '--cpuexpandableReservation', required=False, action='store', help='CPU expandablereservation True/False')
	parser.add_argument('-c2', '--cpulimit', required=False, action='store', help='CPU Limit, -1 = infinite')
	parser.add_argument('-c3', '--cpureservation', required=False, action='store', help='CPU reservation, 0 = none')
	parser.add_argument('-c4', '--cpushares', required=False, action='store', help='CPU Shares e.g. 1000')
	parser.add_argument('-c5', '--cpulevel', required=False, action='store', help='CPU Level normal/high/low')
	parser.add_argument('-m1', '--memoryexpandableReservation', required=False, action='store', help='Memory expandablereservation True/False')
	parser.add_argument('-m2', '--memorylimit', required=False, action='store', help='Memory Limit, -1 = infinite')
	parser.add_argument('-m3', '--memoryreservation', required=False, action='store', help='Memory reservation, 0 = none')
	parser.add_argument('-m4', '--memoryshares', required=False, action='store', help='Memory Shares e.g. 1000')
	parser.add_argument('-m5', '--memorylevel', required=False, action='store', help='Memory Level normal/high/low')
	args = parser.parse_args()

	try:
		service_instance = connect.SmartConnect(host=args.host,
												user=args.user,
												pwd=args.password,
												port=int(args.port))
		atexit.register(connect.Disconnect, service_instance)

		content=service_instance.RetrieveContent()


		if not args.cpuexpandableReservation:
			args.cpuexpandableReservation = bool()
		else:
			if args.cpuexpandableReservation == 'False':
				args.cpuexpandableReservation = bool()
			else:
				args.cpuexpandableReservation = bool(1)

		if not args.cpulimit:
			args.cpulimit = -1

		if not args.cpureservation:
			args.cpureservation = 0

		if not args.cpushares:
			args.cpushares = 0

		if not args.cpulevel:
			args.cpulevel = 'normal'

		if not args.memoryexpandableReservation:
			args.memoryexpandableReservation = bool()
		else:
			if args.memoryexpandableReservation == 'False':
				args.memoryexpandableReservation = bool()
			else:
				args.memoryexpandableReservation = bool(1)

		if not args.memorylimit:
			args.memorylimit = -1

		if not args.memoryreservation:
			args.memoryreservation = 0

		if not args.memoryshares:
			args.memoryshares = 0

		if not args.memorylevel:
			args.memorylevel = 'normal'

		if args.action and args.action == 'update':
				libvmtask.resourcepool_update(service_instance, libvmtask.validate_input(args.parent),  libvmtask.validate_input(args.name), args.cpuexpandableReservation, long(args.cpulimit), args.cpureservation, args.cpushares, args.cpulevel, args.memoryexpandableReservation, long(args.memorylimit), args.memoryreservation, args.memoryshares, args.memorylevel)
		else:
			libvmtask.resourcepool_create(service_instance, libvmtask.validate_input(args.parent),  libvmtask.validate_input(args.name), args.cpuexpandableReservation, long(args.cpulimit), args.cpureservation, args.cpushares, args.cpulevel, args.memoryexpandableReservation, long(args.memorylimit), args.memoryreservation, args.memoryshares, args.memorylevel)

		return 0

	except vmodl.MethodFault as error:
		print("ERROR: " + error.msg)
		sys.exit(1)

# Start program
if __name__ == "__main__":
	main()
