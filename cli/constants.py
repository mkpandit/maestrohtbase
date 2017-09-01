import sys

# Finish codes
finish = {
	'FINISH_OK': 0,
	'SYSTEM_FINISH': 1,
	'FINISH_WRONGPARAMETERS': 3,
	'FINISH_ALREAD_EXISTS': 4,
	'FINISH_NOT_EXISTS': 5,
	'FINISH_VARIABLE_NOT_MATCH': 6,
	'CANNOT_DELETE': 7,
	'SYSTEM_PROGRAMS_NOT_FOUND': 8
}

# System executables
system_executables = {
	'OPENVSWITCH': 'ovs-vsctl',
	'UMOUNT': 'umount',
	'MFSMOUNT': 'mfsmount',
	'SED': 'sed',
	'CHOWN': 'chown',
	'UFW': 'ufw'
}

# Regex table
regex = {
	'NAME': '^[a-zA-Z][a-zA-Z0-9]{1,9}$',
	'IP': '^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$',
	'NETMASK': '^(((128|192|224|240|248|252|254)\.0\.0\.0)|(255\.(0|128|192|224|240|248|252|254)\.0\.0)|(255\.255\.(0|128|192|224|240|248|252|254)\.0)|(255\.255\.255\.(0|128|192|224|240|248|252|254)))$',
	'FQDN': '^/.*(?<!/)$'
}

# OpenVSwitch Return codes
system_return_codes = {
	'BRIDGE_EXISTS_RESULT': 0,
	'PORT_EXISTS_RESULT': 0,
	'BRIDGE_NOT_EXISTS': 2,
	'INTERFACE_NOT_EXISTS': 1
}

help_pages = {
	'htnetwork': 'htnetwork syntax:\n'
	'network create\n'
	'		bridge --name <NAME> --usedhcp --ip <IP> --netmask <NETMASK> --gateway <GATEWAY> --dns1 <DNS1> --dns2 <DNS2>\n'
	'		bond --name <NAME> --port1 <PORT 1> --port2 <PORT 2>\n'
	'		port --name <NAME> --bridgename <BRIDGE> --vlan <VLAN>\n'
	'		dhcp\n'
	'		host --name <NAME> --ip <IP> --dhcpname <NAME>\n'
	'\n'
	'htnetwork remove\n'
	'		bridge --name <NAME>\n',

	'htfs': 'htfs syntax:\n'
	'Configure HTFS\n'
	'		htfs configure --master <MASTER_IP> [slave]\n'
	'Add Disk to HTFS\n'
	'		htfs add --mount <MOUNT_POINT>\n'
	'Remove Disk to HTFS\n'
	'		htfs remove --mount <MOUNT_POINT>\n'
	'Restart chunk server\n'
	'		htfs restart chunk\n'
	'Restart master server\n'
	'		htfs restart master\n'
	'Restart HTFS storage\n'
	'		htfs restart storage\n',

	'hypertask': 'hypertask CLI:\n'
	'hypertask network [...]\n'
	'		Configure network with hypertask.\n'
	'hypertask htfs [...]\n'
	'		Configure htfs with hypertask.\n'
	'hypertask vpn [...]\n'
	'		Configure vpn with hypertask.\n'
}

en_string = {
	'COMM_NOT_RECOG': 'Command not recognized',
	'PAR_INVALID': '%s %s invalid.\n',
	'ONLY_ROOT': '\nOnly root can run this script',
	'REMOVING_DISK': 'Removing %s from config file',
	'RESTARTING_CHUNK': 'Restarting the chunk server',
	'RESTARTING_MASTER': 'Restarting the master server',
	'UMOUNTING_STORAGE': 'Umounting the storage',
	'MOUNTING_STORAGE': 'Mounting the storage',
	'CHANGE_MASTER_CONFIG': 'Change the master config file',
	'CHANGE_CHUNK_CONFIG': 'Change the chunk config file',
	'CHANGE_HOSTS_FILE': 'Changing the /etc/hosts file',
	'ADD_DISK_CONFIG': 'Adding mount point on mount config',
	'CHANGE_MOUNT_PERMISSION': 'changing %s permission',
	'PARAMETER_INVALID': '%s %s invalid.\n'
}


def finish_with_wrong_parameters():
	print help_pages['hypertask']
	sys.exit(finish['FINISH_WRONGPARAMETERS'])


	
