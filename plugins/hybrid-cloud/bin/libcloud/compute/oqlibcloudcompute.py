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
from libcloud.compute.types import Provider
from libcloud.compute.providers import get_driver
from libcloud.compute.deployment import ScriptDeployment
from libcloud.compute.base import NodeAuthPassword, NodeImage, NodeSize
import libcloud.security

libcloud.security.VERIFY_SSL_CERT = False


def htvcenter_lc_parse_cmdline(argv):
	ACCESS_ID = ''
	SECRET_KEY = ''
	REGION = ''
	FILTER = ''
	IDENTIFIER = ''
	INSTANCE_TYPE = ''
	PROVIDER = ''
	USERNAME = ''
	PASSWORD = ''
	TENANT = ''
	HOST = ''
	PORT = ''
	ENDPOINT = ''
	AMI = ''
	SIZE = ''
	KEYPAIR = ''
	GROUP = ''
	USERDATA = ''
	SUBSCRIPTION_ID = ''
	KEYFILE = ''
	SERVICENAME = ''
	try:
		opts, args = getopt.getopt(argv,"hO:W:",["region=","provider=", "filter=", "instance-type=", "username=", "password=", "tenant=", "host=", "port=", "endpoint=", "identifier=", "ami=", "size=", "keypair=", "identifier=", "group=", "userdata=", "subscription-id=", "keyfile=", "service-name="])
		#print opts

	except getopt.GetoptError:
		print 'lc-command -O <aws-access-key> -W <aws-secret-key> --region <region> --filter <filter> --provider <provider> --instance-type <type> --username <username> --password <password> --tenant <tenant-name> --host <ip-address/hostname> --port <portnumber> --endpoint <service-url-endpoint> --identifier <identifier> --ami <ami> --size <size> --keypair <keypair> --group <group> --userdata <userdata-file> --subscription-id <subscription-id> --keyfile <keyfile> --service-name <service-name>'
		sys.exit(2)
	for opt, arg in opts:
		#print opt
		#print arg
		if opt == '-h':
			print 'lc-command -O <aws-access-key> -W <aws-secret-key> --region <region> --filter <filter> --provider <provider> --instance-type <type> --username <username> --password <password> --tenant <tenant-name> --host <ip-address/hostname> --port <portnumber> --endpoint <service-url-endpoint> --identifier <identifier> --ami <ami> --size <size> --keypair <keypair> --group <group> --userdata <userdata-file> --subscription-id <subscription-id> --keyfile <keyfile> --service-name <service-name>'
			sys.exit()
		elif opt in ("-O", "--aws-access-key"):
			ACCESS_ID = arg
		elif opt in ("-W", "--aws-secret-key"):
			SECRET_KEY = arg
		elif opt in ("--region"):
			REGION = arg
		elif opt in ("--filter"):
			FILTER = arg
		elif opt in ("--instance-type"):
			INSTANCE_TYPE = arg
		elif opt in ("--provider"):
			PROVIDER = arg
		elif opt in ("--username"):
			USERNAME = arg
		elif opt in ("--password"):
			PASSWORD = arg
		elif opt in ("--tenant"):
			TENANT = arg
		elif opt in ("--host"):
			HOST = arg
		elif opt in ("--endpoint"):
			ENDPOINT = arg
		elif opt in ("--port"):
			PORT = arg
		elif opt in ("--identifier"):
			IDENTIFIER = arg
		elif opt in ("--ami"):
			AMI = arg
		elif opt in ("--size"):
			SIZE = arg
		elif opt in ("--keypair"):
			KEYPAIR = arg
		elif opt in ("--group"):
			GROUP = arg
		elif opt in ("--userdata"):
			USERDATA = arg
		elif opt in ("--subscription-id"):
			SUBSCRIPTION_ID = arg
		elif opt in ("--keyfile"):
			KEYFILE = arg
		elif opt in ("--service-name"):
			SERVICENAME = arg
	return dict([('ACCESS_ID', ACCESS_ID), ('SECRET_KEY', SECRET_KEY), ('REGION', REGION), ('FILTER', FILTER), ('IDENTIFIER', IDENTIFIER), ('INSTANCE_TYPE', INSTANCE_TYPE), ('PROVIDER', PROVIDER), ('USERNAME', USERNAME), ('PASSWORD', PASSWORD), ('TENANT', TENANT), ('HOST', HOST), ('PORT', PORT), ('ENDPOINT', ENDPOINT), ('IDENTIFIER', IDENTIFIER), ('AMI', AMI), ('SIZE', SIZE), ('KEYPAIR', KEYPAIR), ('GROUP', GROUP), ('USERDATA', USERDATA), ('SUBSCRIPTION_ID', SUBSCRIPTION_ID), ('KEYFILE', KEYFILE), ('SERVICENAME', SERVICENAME)])


def htvcenter_lc_get_connection(params):

	if params['PROVIDER'] == 'EC2_EU_WEST':
		Driver = get_driver(Provider.EC2_EU_WEST)
		conn = Driver(params['ACCESS_ID'], params['SECRET_KEY'])
		return conn
	elif params['PROVIDER'] == 'EC2_US_EAST':
		Driver = get_driver(Provider.EC2_US_EAST)
		conn = Driver(params['ACCESS_ID'], params['SECRET_KEY'])
		return conn
	elif params['PROVIDER'] == 'OPENSTACK':
		OpenStack = get_driver(Provider.OPENSTACK)
		Driver = OpenStack(params['USERNAME'], params['PASSWORD'],
			host=params['HOST'], port=params['PORT'], secure=False,
			ex_force_service_name='nova',
			ex_force_auth_url=params['ENDPOINT'],
			ex_force_auth_version='2.0_password',
			ex_tenant_name=params['TENANT'])
		return Driver
	elif params['PROVIDER'] == 'AZURE':
		cls = get_driver(Provider.AZURE)
		conn = cls(subscription_id=params['SUBSCRIPTION_ID'],
			key_file=params['KEYFILE'])
		return conn


