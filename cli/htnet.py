import getopt
import sys
import re
# import subprocess
# import os

from constants import *

def htnet(argv):
	"main function for htnetwork"

	parameters_size = len(argv)
	if parameters_size > 1:
		try:
			getattr(sys.modules[__name__], "%s_%s" % (argv[0], argv[1]))(argv[2:])
		except (AttributeError):
			print en_string['COMM_NOT_RECOG']
	else:
		print en_string['COMM_NOT_RECOG']
	return

def populate_parameters(raw_parameters):
	"populate all parameters in the variable"
	parameters = {}
	for key, value in raw_parameters:
		if key == "--usedhcp":
			parameters['usedhcp'] = True
		else:
			parameters[key[2:]] = value.lower()
	return parameters



def testParameters(parameters):
	"test all parameters"
	for option, value in parameters:
		if ((option == "--name" or
			option == "--interface" or
			option == "--bridgename" or
			option == "--interface1" or
			option == "--interface2") and
			not re.match( regex['NAME'], value, re.I)):
			print en_string['PAR_INVALID'] % (option, value)
			return False
		if((option == "--ip" or
			option == "--netmask" or
			option == "--gateway" or
			option == "--dns1" or
			option == "--dns2") and
			not re.match( regex['IP'], value, re.I)):
			print en_string['PAR_INVALID'] % (option[2:], value)
			return False
		if(option == "--vlan"):
			try:
				vlanInt = int(value)
				if(vlanInt > 4094 or vlanInt < 1):
					print en_string['PAR_INVALID'] % (option, value)
					return False
			except getopt.GetoptError as err:
				print en_string['PAR_INVALID'] % (option, value)
				return False
	return True

def create_bridge(argv):
	"create the bridge on system"
	create_bridge_parameters = ['name=', 'ip=', 'netmask=', 'usedhcp', 'gateway=', 'dns1=', 'dns2=']
	raw_parameters = getParameters(argv, create_bridge_parameters)
	if not testParameters(raw_parameters):
		return

	parameters = populate_parameters(raw_parameters)

	if parameters['name']:
		if (parameters.has_key('ip') and not parameters.has_key('netmask')) and not parameters['netmask']:
			print 'ip needs netmask\n';
			return
		# Test if bridge exists
		status = subprocess.call([ovswitch['OPENVSWITCH_EXECUTABLE'], 'br-exists', parameters['name'] ])
		if status == system_return_codes['BRIDGE_EXISTS_RESULT']:
			print 'Bridge %s alread exists\n' % parameters['name'];
		else:
			# Create Bridge infos
			ifcfg = 'DEVICE=%s\n' % parameters['name']
			ifcfg = ifcfg + 'TYPE=Bridge\n'
			ifcfg = ifcfg + 'ONBOOT=yes\n'
			ifcfg = ifcfg + 'NM_CONTROLLED=no\n'
			ifcfg = ifcfg + 'DELAY=0\n'
			ifcfg = ifcfg + 'IPV6INIT=no\n'
			ifcfg = ifcfg + 'IPV6_AUTOCONF=no\n'
			ifcfg = ifcfg + 'STP=yes\n'

			if ip:
				ifcfg = ifcfg + 'BOOTPROTO=none\n'
				ifcfg = ifcfg + 'IPADDR=%s\n' % ip
				ifcfg = ifcfg + 'NETMASK=%s\n' % netmask
				if gateway:
					ifcfg = ifcfg + 'GATEWAY=%s\n' % gateway
				if dns1:
					ifcfg = ifcfg + 'DNS1=%s\n' % dns1
				if dns2:
					ifcfg = ifcfg + 'DNS2=%s\n' % dns2
			elif usedhcp:
				ifcfg = ifcfg + 'BOOTPROTO=dhcp\n'

			# TODO Put if to ubuntu.

			# Add on CentOS network file
			with open('/etc/sysconfig/network-scripts/ifcfg-%s' % name, 'w') as ifcfg_script:
				ifcfg_script.write(ifcfg)

			# Creating the bridge
			status = subprocess.call([ovswitch['OPENVSWITCH_EXECUTABLE'], 'add-br', name ])
	print 'Bridge needs a name\n';
	return

def delete_bridge(argv):
	"delete the bridge on system"
	delete_bridge_parameters = ['name=']
	raw_parameters = getParameters(argv, delete_bridge_parameters)
	testParameters(raw_parameters)

	parameters = { 'name': None }
	parameters = populate_parameters(raw_parameters, parameters)

	if name:
		proc = subprocess.Popen([ovswitch['OPENVSWITCH_EXECUTABLE'], 'list-ports', name ], 
			stdout=subprocess.PIPE, stderr=subprocess.PIPE)
		output_stdout = proc.stdout.read()
		output_stdout = output_stdout.split()
		output_stderr = proc.stderr.read()

		if len(output_stdout) > 0:
			print 'Bridge have %d port(s) active\n' % len(output_stdout);
			print 'Ports: %r' % output_stdout
			sys.exit(finish['CANNOT_DELETE'])
		elif 'no bridge named' in output_stderr:
			print 'Bridge %s not exists\n' % name;
			sys.exit(finish['CANNOT_DELETE'])
		else:
			status = subprocess.call([ovswitch['OPENVSWITCH_EXECUTABLE'], 'del-br', name ])
			os.remove('/etc/sysconfig/network-scripts/ifcfg-%s' % name)
			sys.exit(finish['FINISH_OK'])
	finish_with_wrong_parameters()
	return

# def createBond:
# 	create_bond_parameters = ['name=', 'bridgename=', 'interface1=', 'interface2=']
# 	parameters = getParameters(argv, create_bond_parameters)
# 	testParameters(parameters)
# 	populate_parameters(parameters)

# 	if name and interface1 and interface2:
# 		# Test if bridge not exists
# 		status = subprocess.call([ovswitch['OPENVSWITCH_EXECUTABLE'], 'br-exists', name ])
# 		if status == system_return_codes['BRIDGE_NOT_EXISTS']:
# 			print 'Bridge %s not exists\n' % name;
# 			sys.exit(finish['FINISH_NOT_EXISTS'])
# 		# Test if 2 interfaces exists
# 		status = subprocess.call('ip', 'addr', 'show','dev', interface1])
# 		if status == system_return_codes['INTERFACE_NOT_EXISTS']:
# 			print 'Interface %s not exists\n' % interface1;
# 			sys.exit(finish['FINISH_NOT_EXISTS'])
# 		status = subprocess.call(['ip', 'addr', 'show','dev', interface2])
# 		if status == system_return_codes['INTERFACE_NOT_EXISTS']:
# 			print 'Interface %s not exists\n' % interface2;
# 			sys.exit(finish['FINISH_NOT_EXISTS'])

# 		# create bond
# 		status = subprocess.call([ovswitch['OPENVSWITCH_EXECUTABLE'], 
# 			'add-bond', bridgename, name, interface1, interface2 ])
# 		sys.exit(finish['FINISH_OK'])
# 	finish_with_wrong_parameters()
# 	return

# def deleteBond:
# 	return

# def createPort:
# 	create_bond_parameters = ['name=', 'bridgename=', 'vlan=']
# 	parameters = getParameters(argv, create_bond_parameters)
# 	testParameters(parameters)
# 	populate_parameters(parameters)

# 	if name and bridgename:
# 		# Test if bridge exists
# 		status = subprocess.call([ovswitch['OPENVSWITCH_EXECUTABLE'], 'br-exists', bridgename ])
# 		if status == system_return_codes['BRIDGE_NOT_EXISTS']:
# 			print 'Bridge %s not exists\n' % bridgename;
# 			sys.exit(finish['FINISH_NOT_EXISTS'])
# 		# Test if port exists
# 		status = subprocess.call([ovswitch['OPENVSWITCH_EXECUTABLE'], 'port-to-br', name ])
# 		if status == system_return_codes['PORT_EXISTS_RESULT']:
# 			print 'port %s exists\n' % name;
# 			sys.exit(finish['FINISH_NOT_EXISTS'])

# 		system("$OPENVSWITCH_EXECUTABLE", "port-to-br", "$name");
# 		sys.exit(finish['FINISH_OK'])
		
# 	print "Port needs a name and a bridge.\n";
# 	exit $FINISH_WRONGPARAMETERS;

# # # sub deletePort {
# # # 	exit 3;
# # # }


# # # sub createDHCP {
# # # 	exit 3;
# # # }
# # # sub deleteDHCP {
# # # 	exit 3;
# # # }

# # # sub createHost {}
# # # sub deleteHost {}

# # # sub sendToDatabase {}

# # # sub checkParameters {
# # # 	if ($options{h})
# # # {
# # #   showInfos();
# # #   	exit $FINISH_OK;
# # # }



