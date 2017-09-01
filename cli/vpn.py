import sys
import subprocess

import parameters_managements
import files

from constants import *

def vpn_configure(args):
	'configure the vpn'

	# Test and populate parameters
	parameters = parameters_managements.get_test_populate(args, ['name=', 'ip='], ['ip'], [regex['IP']], 2)
	if parameters is None:
		return

	# Unzip the configure files
	subprocess.call(['gunzip', '-df', '/usr/share/doc/openvpn/examples/sample-config-files/server.conf.gz'], stdout=subprocess.PIPE, stderr=subprocess.PIPE)
	subprocess.call(['cp', '-r', '/usr/share/doc/openvpn/examples/sample-config-files/server.conf', '/etc/openvpn'], stdout=subprocess.PIPE, stderr=subprocess.PIPE)

	# Configure the server.conf
	files.change_text_in_file('dh dh1024.pem', 'dh dh2048.pem', '/etc/openvpn/server.conf')
	files.change_text_in_file(';push "dhcp-option DNS 208.67.222.222"', 'push "dhcp-option DNS 208.67.222.222"', '/etc/openvpn/server.conf')
	files.change_text_in_file(';push "dhcp-option DNS 208.67.220.220"', 'push "dhcp-option DNS 208.67.220.220"', '/etc/openvpn/server.conf')
	files.change_text_in_file(';user nobody', 'user nobody', '/etc/openvpn/server.conf')
	files.change_text_in_file(';group nogroup', 'group nogroup', '/etc/openvpn/server.conf')
	files.change_text_in_file(';client-to-client', 'client-to-client', '/etc/openvpn/server.conf')

	# Change ip_forward
	subprocess.call(['echo 1 > /proc/sys/net/ipv4/ip_forward'], stdout=subprocess.PIPE, shell=True)

	# Change sysctl.conf
	files.change_text_in_file('#net.ipv4.ip_forward=1', 'net.ipv4.ip_forward=1', '/etc/sysctl.conf')
	subprocess.call(['sysctl', '-w', 'net.ipv4.ip_forward=1'], stdout=subprocess.PIPE, stderr=subprocess.PIPE)

	# Configure ufw
	subprocess.call(['ufw', 'allow', 'ssh'])
	subprocess.call(['ufw', 'allow', '1194/udp'])
	files.change_text_in_file('DEFAULT_INPUT_POLICY="DROP"', 'DEFAULT_INPUT_POLICY="ACCEPT"', '/etc/default/ufw')
	files.change_text_in_file('DEFAULT_FORWARD_POLICY="DROP"', 'DEFAULT_FORWARD_POLICY="ACCEPT"', '/etc/default/ufw')

	# Configure before.rules
	files.line_prepender('/etc/ufw/before.rules', 'COMMIT')
	files.line_prepender('/etc/ufw/before.rules', '-A POSTROUTING -s 10.8.0.0/8 -o eth0 -j MASQUERADE')
	files.line_prepender('/etc/ufw/before.rules', ':POSTROUTING ACCEPT [0:0]')
	files.line_prepender('/etc/ufw/before.rules', '*nat')

	# Enable ufw rules
	subprocess.call(['ufw', '--force', 'enable'])
 
	# Configure cert Keys
	subprocess.call(['cp', '-r', '/usr/share/easy-rsa/', '/etc/openvpn'])
	subprocess.call(['mkdir', '/etc/openvpn/easy-rsa/keys'], stdout=subprocess.PIPE, stderr=subprocess.PIPE)

	files.change_text_in_file('export KEY_COUNTRY=', 'export KEY_COUNTRY="CA"', '/etc/openvpn/easy-rsa/vars')
	files.change_text_in_file('export KEY_PROVINCE=', 'export KEY_PROVINCE="ON"', '/etc/openvpn/easy-rsa/vars')
	files.change_text_in_file('export KEY_CITY=', 'export KEY_CITY="Toronto"', '/etc/openvpn/easy-rsa/vars')
	files.change_text_in_file('export KEY_ORG=', 'export KEY_ORG="%s"' % parameters['name'], '/etc/openvpn/easy-rsa/vars')
	files.change_text_in_file('export KEY_EMAIL=', 'export KEY_EMAIL="htbase@htbase.com"', '/etc/openvpn/easy-rsa/vars')
	files.change_text_in_file('export KEY_OU=', 'export KEY_OU="HQ"', '/etc/openvpn/easy-rsa/vars')
	files.change_text_in_file('export KEY_NAME=', 'export KEY_NAME="server"', '/etc/openvpn/easy-rsa/vars')
	#subprocess.call(['openssl', 'dhparam', '-out', '/etc/openvpn/dh2048.pem', '2048'])
	export_variables = 'EASY_RSA="`pwd`"; OPENSSL="openssl"; PKCS11TOOL="pkcs11-tool"; GREP="grep"; KEY_CONFIG=`$EASY_RSA/whichopensslcnf $EASY_RSA`; KEY_DIR="$EASY_RSA/keys"; PKCS11_MODULE_PATH="dummy"; PKCS11_PIN="dummy"; KEY_SIZE=2048; CA_EXPIRE=3650; KEY_EXPIRE=3650; KEY_COUNTRY="CA"; KEY_PROVINCE="ON"; KEY_CITY="Toronto"; KEY_ORG="htbase"; KEY_EMAIL="htbase@htbase.com"; KEY_OU="HQ"; KEY_NAME="server"; '
	subprocess.call([export_variables + './clean-all'], cwd='/etc/openvpn/easy-rsa', shell=True)
	subprocess.call([export_variables + '/etc/openvpn/easy-rsa/pkitool --initca'], cwd='/etc/openvpn/easy-rsa', shell=True)
	subprocess.call([export_variables + '/etc/openvpn/easy-rsa/pkitool --server server'], cwd='/etc/openvpn/easy-rsa', shell=True)
	subprocess.call(['cp /etc/openvpn/easy-rsa/keys/server.crt /etc/openvpn'], shell=True)
	subprocess.call(['cp /etc/openvpn/easy-rsa/keys/server.key /etc/openvpn'], shell=True)
	subprocess.call(['cp /etc/openvpn/easy-rsa/keys/ca.crt /etc/openvpn'], shell=True)
	subprocess.call(['service', 'openvpn', 'start'], cwd='/etc/openvpn/easy-rsa')
  	subprocess.call([export_variables + '/etc/openvpn/easy-rsa/pkitool client'], cwd='/etc/openvpn/easy-rsa', shell=True)
     	subprocess.call(['cp', '/usr/share/doc/openvpn/examples/sample-config-files/client.conf', '/etc/openvpn/easy-rsa/keys/client.ovpn'], cwd='/etc/openvpn/easy-rsa')
   
	# Configure client settings
	files.change_text_in_file('remote my-server-1 1194', 'remote %s 1194'  % parameters['ip'], '/etc/openvpn/easy-rsa/keys/client.ovpn')
	files.change_text_in_file(';user nobody', 'user nobody', '/etc/openvpn/easy-rsa/keys/client.ovpn')
	files.change_text_in_file(';group nogroup', 'group nogroup', '/etc/openvpn/easy-rsa/keys/client.ovpn')
	files.change_text_in_file('ca ca.crt', ';ca ca.crt', '/etc/openvpn/easy-rsa/keys/client.ovpn')
	files.change_text_in_file('cert client.crt', ';cert client.crt', '/etc/openvpn/easy-rsa/keys/client.ovpn')
	files.change_text_in_file('key client.key', ';key client.key', '/etc/openvpn/easy-rsa/keys/client.ovpn')
	
	files.line_append('/etc/openvpn/easy-rsa/keys/client.ovpn', '<ca>')
	subprocess.call(['cat /etc/openvpn/ca.crt >> /etc/openvpn/easy-rsa/keys/client.ovpn'], stdout=subprocess.PIPE, stderr=subprocess.PIPE, shell=True)
	files.line_append('/etc/openvpn/easy-rsa/keys/client.ovpn', '</ca>')
	files.line_append('/etc/openvpn/easy-rsa/keys/client.ovpn', '<cert>')
	subprocess.call(['cat /etc/openvpn/easy-rsa/keys/client.crt >> /etc/openvpn/easy-rsa/keys/client.ovpn'], stdout=subprocess.PIPE, stderr=subprocess.PIPE, shell=True)
	files.line_append('/etc/openvpn/easy-rsa/keys/client.ovpn', '</cert>')
	files.line_append('/etc/openvpn/easy-rsa/keys/client.ovpn', '<key>')
	subprocess.call(['cat /etc/openvpn/easy-rsa/keys/client.key >> /etc/openvpn/easy-rsa/keys/client.ovpn'], stdout=subprocess.PIPE, stderr=subprocess.PIPE, shell=True)
	files.line_append('/etc/openvpn/easy-rsa/keys/client.ovpn', '</key>')

	return

def vpn(argv):
	"main function for vpn"

	# enter in the correct method.
	parameters_size = len(argv)
	if parameters_size > 1:
		try:
			getattr(sys.modules[__name__], "vpn_%s" % (argv[0]))(argv[1:])
		except (AttributeError):
			print en_string['COMM_NOT_RECOG']
	else:
		print en_string['COMM_NOT_RECOG']
	return
