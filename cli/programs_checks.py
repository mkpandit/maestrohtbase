import subprocess
import os

from constants import *

def check_programs():
	"Check all programs necessary to run the HyperTask CLI"
	found = True
	if not verify_program([system_executables['OPENVSWITCH'], '--version']):
		print '%s executable not found on the system' % system_executables['OPENVSWITCH']
		found = False
	if not verify_program([system_executables['UMOUNT'], '-h']):
		print '%s executable not found on the system' % system_executables['UMOUNT']
		found = False
	if not verify_program([system_executables['MFSMOUNT'], '--version']):
		print '%s executable not found on the system' % 'htfsmount'
		found = False
	if not verify_program([system_executables['SED'], '--version']):
		print '%s executable not found on the system' % system_executables['SED']
		found = False
	if not verify_program([system_executables['CHOWN'], '--version']):
		print '%s executable not found on the system' % system_executables['CHOWN']
		found = False
	if not verify_program([system_executables['UFW'], '--version']):
		print '%s executable not found on the system' % system_executables['UFW']
		found = False
	return found

def verify_program(parameters):
	"Check one program to see if exist on system"
	try:
		subprocess.call(parameters, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
		return True
	except OSError as e:
		if e.errno == os.errno.ENOENT:
			return False
		return True
	return
