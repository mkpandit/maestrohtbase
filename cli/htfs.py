import sys
import subprocess

import parameters_managements
import files

from constants import *

def restart_chunk():
	'restart the chunk server'

	print en_string['RESTARTING_CHUNK']
	subprocess.call(['/etc/init.d/lizardfs-chunkserver', 'restart'], stdout=subprocess.PIPE, stderr=subprocess.PIPE)
	return

def restart_master():
	'restart the master server'

	print en_string['RESTARTING_MASTER']
	subprocess.call(['mfsmetarestore', '-a'], stdout=subprocess.PIPE, stderr=subprocess.PIPE)
	subprocess.call(['/etc/init.d/lizardfs-master', 'restart'], stdout=subprocess.PIPE, stderr=subprocess.PIPE)
	return

def restart_storage():
	'restart the storage'

	print en_string['UMOUNTING_STORAGE']
	subprocess.call(['umount', '/usr/share/htvcenter/storage'], stdout=subprocess.PIPE, stderr=subprocess.PIPE)
	print en_string['MOUNTING_STORAGE']
	subprocess.call(['mfsmount', '/usr/share/htvcenter/storage', '-o', 'mfsmaster=htfsmaster'], stdout=subprocess.PIPE, stderr=subprocess.PIPE)
	return

def htfs_restart(args):
	if args[0] == 'chunk':
		restart_chunk()
	elif args[0] == 'master':
		restart_master()
	elif args[0] == 'storage':
		restart_storage()
	else:
		print help_pages['htfs']
	return

def htfs_configure(args):
	'configure the htfs'

	# Test and populate parameters
	parameters = parameters_managements.get_test_populate(args, ['master='], ['master'], [regex['IP']], 1)
	if parameters is None:
		return

	slave = False
	for arg in args:
		if arg == 'slave':
			slave = True

	# Change the flag LIZARDFSMASTER_ENABLE and LIZARDFSCHUNKSERVER_ENABLE
	print en_string['CHANGE_MASTER_CONFIG']
	if slave:
		files.change_text_in_file('LIZARDFSMASTER_ENABLE=true', 'LIZARDFSMASTER_ENABLE=false', '/etc/default/lizardfs-master')
	else:
		files.change_text_in_file('LIZARDFSMASTER_ENABLE=false', 'LIZARDFSMASTER_ENABLE=true', '/etc/default/lizardfs-master')

	print en_string['CHANGE_CHUNK_CONFIG']
	files.change_text_in_file('LIZARDFSCHUNKSERVER_ENABLE=false', 'LIZARDFSCHUNKSERVER_ENABLE=true', '/etc/default/lizardfs-chunkserver')

	print en_string['CHANGE_HOSTS_FILE']

	# remove mfsmaster on /etc/hosts file
	files.delete_on_file('/etc/hosts', 'htfsmaster')

	# add mfsmaster on /etc/hosts file
	files.write_on_file('/etc/hosts', 'htfsmaster', '\n%s\thtfsmaster\n' % parameters['master'])

	# Restart the services
	restart_chunk()
	restart_master()

	# Mount the HTFS
	print en_string['MOUNTING_STORAGE']
	subprocess.call(['mfsmount', '/usr/share/htvcenter/storage', '-o', 'mfsmaster=htfsmaster'], stdout=subprocess.PIPE, stderr=subprocess.PIPE)

def htfs_add(args):
	'add a disk on htfs storage'

	# Test and populate parameters
	parameters = parameters_managements.get_test_populate(args, ['mount='], ['mount'], [regex['FQDN']], q)
	if parameters is None:
		return

	# add disk on /etc/mfs/mfshdd.cfg file
	print en_string['ADD_DISK_CONFIG']
	find = files.write_on_file('/etc/mfs/mfshdd.cfg', parameters['mount'], parameters['mount'] + '\n')

	# change the permission on mount point
	if not find:
		print en_string['CHANGE_MOUNT_PERMISSION'] % parameters['mount']
		subprocess.call(['chown', '-R', 'mfs:mfs', parameters['mount']])

	# Restart the services
	restart_chunk()

def htfs_remove(args):
	'remove a disk on htfs storage'

	# Test and populate parameters
	parameters = parameters_managements.get_test_populate(args, ['mount='], ['mount'], [regex['FQDN']], 1)
	if parameters is None:
		return

	# remove disk on /etc/mfs/mfshdd.cfg file
	print en_string['REMOVING_DISK'] % parameters['mount']
	files.delete_on_file('/etc/mfs/mfshdd.cfg', parameters['mount'])

	# Restart the services
	restart_chunk()

def htfs(argv):
	"main function for htfs"

	# enter in the correct method.
	parameters_size = len(argv)
	if parameters_size > 1:
		try:
			getattr(sys.modules[__name__], "htfs_%s" % (argv[0]))(argv[1:])
		except (AttributeError):
			print en_string['COMM_NOT_RECOG']
	else:
		print en_string['COMM_NOT_RECOG']
	return
