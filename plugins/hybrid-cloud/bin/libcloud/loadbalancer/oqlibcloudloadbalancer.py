#!/usr/bin/python
#
# htvcenter Enterprise developed by htvcenter Enterprise GmbH.
#
# All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.
#
# This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
# The latest version of this license can be found here: http://htvcenter-enterprise.com/license
#
# By using this software, you acknowledge having read this license and agree to be bound thereby.
#
#           http://htvcenter-enterprise.com
#
# Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
#
import sys, getopt
import libcloud.compute.types
import libcloud.compute.providers
from libcloud.loadbalancer.base import Member, Algorithm
from libcloud.loadbalancer.types import State, Provider
from libcloud.loadbalancer.providers import get_driver


def htvcenter_lc_parse_cmdline(argv):
	ACCESS_ID = ''
	SECRET_KEY = ''
	REGION = ''
	IDENTIFIER = ''
	LOADBALANCER_NAME = ''
	MEMBER = ''
	MEMBER_IP = ''
	MEMBER_PORT = ''
	PROTOCOL = ''
	try:
		opts, args = getopt.getopt(argv,"hO:W:l:i:p:m:o:r",["aws-access-key=","aws-secret-key=","region=", "member=", "loadbalancer-name=", "ip=", "port=", "protocol"])
		#print opts

	except getopt.GetoptError:
		print 'lc-xxxx -O <aws-access-key> -W <aws-secret-key> --region <region>'
		sys.exit(2)
	for opt, arg in opts:
		#print opt
		#print arg
		if opt == '-h':
			print 'lc-xxxx -O <aws-access-key> -W <aws-secret-key> --region <region>'
			sys.exit()
		elif opt in ("-O", "--aws-access-key"):
			ACCESS_ID = arg
		elif opt in ("-W", "--aws-secret-key"):
			SECRET_KEY = arg
		elif opt in ("--region"):
			REGION = arg
		elif opt in ("-l", "--loadbalancer-name"):
			LOADBALANCER_NAME = arg
		elif opt in ("-m", "--memeber"):
			MEMBER = arg
		elif opt in ("-i", "--ip"):
			MEMBER_IP = arg
		elif opt in ("-p", "--port"):
			MEMBER_PORT = arg
		elif opt in ("-o", "--protocol"):
			PROTOCOL = arg

	for ident in argv:
		if ident not in (ACCESS_ID, SECRET_KEY, REGION, IDENTIFIER, MEMBER, LOADBALANCER_NAME, MEMBER_IP, MEMBER_PORT, PROTOCOL, "-h", "-O", "--aws-access-key", "-W", "--aws-secret-key", "--region", "-l", "-m", "-i", "-o", "-p"):
			IDENTIFIER = ident

	#print 'ACCESS_ID is ', ACCESS_ID
	#print 'SECRET_KEY is ', SECRET_KEY
	#print 'REGION is ', REGION

	return dict([('ACCESS_ID', ACCESS_ID), ('SECRET_KEY', SECRET_KEY), ('REGION', REGION), ('IDENTIFIER', IDENTIFIER), ('LOADBALANCER_NAME', LOADBALANCER_NAME), ('MEMBER', MEMBER), ('MEMBER_IP', MEMBER_IP), ('MEMBER_PORT', MEMBER_PORT), ('PROTOCOL', PROTOCOL)])



def htvcenter_lc_get_connection(params):
	Driver = get_driver(Provider.ELB)(params['ACCESS_ID'], params['SECRET_KEY'], params['REGION'])
	return Driver


def htvcenter_lc_get_compute_connection(params):

	if params['REGION'] == 'eu-west-1':
		Driver = libcloud.compute.providers.get_driver(libcloud.compute.providers.Provider.EC2_EU_WEST)
	elif params['REGION'] == 'us-east-1':
		Driver = libcloud.compute.providers.get_driver(libcloud.compute.providers.Provider.EC2_US_EAST)
	else:
		Driver = libcloud.compute.providers.get_driver(libcloud.compute.providers.Provider.EC2)

	conn = Driver(params['ACCESS_ID'], params['SECRET_KEY'])
	return conn


