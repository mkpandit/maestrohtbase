import getopt
import re

import htnet
import htfs
import vpn

from constants import *

def check_parameters(parameters):
	if parameters[0] == 'network':
		htnet.htnet(parameters[1:])
	elif parameters[0] == 'htfs':
		htfs.htfs(parameters[1:])
	elif parameters[0] == 'vpn':
		vpn.vpn(parameters[1:])
	else:
		print en_string['COMM_NOT_RECOG']
	return

def getParameters(argv, options):
	'This get all parameters options'

	parameters = {}
	try:
		parameters, remainder = getopt.gnu_getopt(argv, '', options)
	except getopt.GetoptError as err:
		print
	return parameters

def parameter_test(raw_parameters, name, regex):
	'test one parameter'

	for option, value in raw_parameters:
		if (option == '--' + name and
			not re.match( regex, value, re.I)):
				print en_string['PARAMETER_INVALID'] % (option, value)
				return False
	return True

def populate_parameters(raw_parameters, parameter_size):
	'populate all parameters'

	parameters = {}
	for key, value in raw_parameters:
		parameters[key[2:]] = value.lower()
	if len(parameters) != parameter_size:
		print help_pages['htfs']
		sys.exit(1)
	return parameters

def get_test_populate(args, parameters_list, test_parameters_list, regex_list, parameters_qtd):
	'Get test and populate parameters'

	raw_parameters = getParameters(args, parameters_list)
	i = 0
	for test_parameter in test_parameters_list:
		if not parameter_test(raw_parameters, test_parameter, regex_list[i]):
			return
		i = i + 1
	parameters = populate_parameters(raw_parameters, parameters_qtd)
	return parameters
