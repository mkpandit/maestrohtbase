import sys

import parameters_managements

from constants import *

def cli():
	"main function for cli"

	while True:
		person = raw_input('\nHT CLI:> ')
		command = person.split()
		if len(command) > 0:
			if command[0] == 'quit':
				print "bye..."
				sys.exit(finish['FINISH_OK'])
			else:
				parameters_managements.check_parameters(command)
	return
