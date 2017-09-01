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


def update_vm(vm, service_instance, network=None, boot=None, memory=None, cpu=None, vncport=None, vncpassword=None, iso=None):

	print "in update"
	if memory:
		memory = long(memory)
		libvmtask.set_memory(vm, service_instance, memory)
	if cpu:
		cpu = int(cpu)
		libvmtask.set_cpu(vm, service_instance, cpu)


	# nic
	if network:
		print "- Removing existing network cards"
		print network
		libvmtask.remove_all_nic(vm, service_instance)
		network_list = network.split('|')
		for n in network_list:
			nc = n.split(',')
			print "- Adding nic: "
			print nc
			libvmtask.add_nic(vm, service_instance, nc[0], nc[1], nc[2])

	# attach iso/cdrom
	if iso:
		if iso == 'none':
			libvmtask.remove_iso(vm, service_instance)
		else:
			libvmtask.add_iso(vm, service_instance, iso)

	# boot from
	if boot:
		libvmtask.set_boot(vm, service_instance, boot)

	# vncpassword
	if vncpassword:
		libvmtask.set_vncpassword(vm, service_instance, vncpassword)
	# vncport
	if vncport:
		libvmtask.set_vncport(vm, service_instance, vncport)




def main():
	parser = argparse.ArgumentParser(description='vCenter login')
	parser.add_argument('-s', '--host', required=True, action='store', help='vSphere IP')
	parser.add_argument('-o', '--port', type=int, default=443, action='store', help='vSphere Port')
	parser.add_argument('-u', '--user', required=True, action='store', help='User name')
	parser.add_argument('-p', '--password', required=True, action='store', help='Password')
	parser.add_argument('-n', '--name', required=True, action='store', help='VM Name')
	parser.add_argument('-t', '--cpu', required=False, action='store', help='CPU number')
	parser.add_argument('-b', '--boot', required=False, action='store', help='Boot order e.g. local/network/cdrom')
	parser.add_argument('-r', '--memory', required=False, action='store', help='Memory in MB e.g. 1024')
	parser.add_argument('-m', '--network', required=False, action='store', help='The Network configuration string formated as "[vSwitch-name],[nic-type],{MAC address]|[vSwitch-name],[nic-type],{MAC address]|..." e.g. "vSwitch0,e1000,00:50:56:8c:a9:24|vSwitch0,e1000,00:50:56:8c:a9:25"')
	parser.add_argument('-w', '--vncpassword', required=False, action='store', help='VNC password')
	parser.add_argument('-v', '--vncport', required=False, action='store', help='VNC port')
	parser.add_argument('-c', '--comment', required=False, action='store', help='VM description')
	parser.add_argument('-i', '--iso', required=False, action='store', help='Path to ISO image attached as cdrom')

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
					print "Updating VM " + vm.name
					update_vm(vm, service_instance, args.network, args.boot, args.memory, args.cpu, args.vncport, args.vncpassword, args.iso)

		else:
			raise SystemExit("Please provide a VM name")

		return 0

	except vmodl.MethodFault as error:
		print("ERROR: " + error.msg)
		sys.exit(1)

# Start program
if __name__ == "__main__":
	main()
