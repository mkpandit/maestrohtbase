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
	args = parser.parse_args()

	try:
		service_instance = connect.SmartConnect(host=args.host,
												user=args.user,
												pwd=args.password,
												port=int(args.port))
		atexit.register(connect.Disconnect, service_instance)
		content = service_instance.content

		for dc in content.rootFolder.childEntity:
			#pprint(getmembers(dc))
			#print dc.name

			sys.stdout.write("name=" + str(dc.name))

			hosts = "|hosts="
			found_hosts = False
			cluster = "|cluster="
			found_cluster = False
			for c in dc.hostFolder.childEntity:
				if isinstance(c, vim.ClusterComputeResource):
					one_cluster = str(c.name) + ","
					cluster = cluster + one_cluster
					found_cluster = True
					for h in c.host:
						if isinstance(h, vim.HostSystem):
							one_host = str(c.name) + ":" + str(h.name) + ","
							hosts = hosts + one_host
							found_hosts = True

				elif isinstance(c, vim.ComputeResource):
					for h in c.host:
						if isinstance(h, vim.HostSystem):
							one_host = str(h.name) + ","
							hosts = hosts + one_host
							found_hosts = True

			if found_cluster:
				cluster = cluster[:-1]
				sys.stdout.write(str(cluster))

			if found_hosts:
				hosts = hosts[:-1]
				sys.stdout.write(str(hosts))

			datastore = "|datastore="
			found_ds = False
			for ds in dc.datastore:
				one_ds = str(ds.name) + ":" + str(ds.overallStatus) + ":" + str(ds.summary.type)  + ":" + str(ds.summary.capacity/1024/1024)  + ":" + str(ds.summary.freeSpace/1024/1024)  + ","
				datastore = datastore + one_ds
				found_ds = True
			if found_ds:
				datastore = datastore[:-1]
				sys.stdout.write(str(datastore))

			print


		return 0

	except vmodl.MethodFault as error:
		print("ERROR: " + error.msg)
		sys.exit(1)


if __name__ == "__main__":
	main()
