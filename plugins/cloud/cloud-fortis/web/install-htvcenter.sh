#!/bin/bash
#
# installs htvcenter Enterprise
# contact@htbase.com
#

clear
WHOAMI=`whoami`
if [ "$WHOAMI" != "root" ]; then
	echo "ERROR: Please run this install script as root!"
	exit 1
fi
export htvcenter_SERVER_INSTALL_DIR=`pushd \`dirname $0\` 1>/dev/null && pwd && popd 1>/dev/null`
cd $htvcenter_SERVER_INSTALL_DIR
clear
more htvcenter-releasenotes.txt
echo
echo
echo
echo "==========================================================="
echo "Welcome to the htvcenter Enterprise installation"
echo "==========================================================="
echo "This script will install htvcenter and additional required"
echo "third-party software on this system. By using this software"
echo "you accept htvcenters Enterprise License available at"
echo "http://www.htbase.com/license"
echo
echo "Please press ENTER to review the htvcenter Enterprise license"
read
clear
more htvcenter-Enterprise-License.txt

read -p "Do you accept the above license terms? [y/n] " -n 1 -r CONFIRM
if [[ $CONFIRM =~ ^[Yy]$ ]]; then
	if [ -f /etc/debian_version ]; then
		apt-get update && apt-get install -y make
	elif [ -f /etc/redhat-release ]; then
		yum -y install make
	elif [ -f /etc/SuSE-release ]; then
		zypper --non-interactive install make
	else
		echo "ERROR: You are trying to install htvcenter on an unsupported Linux Distribution!"
		exit 1
	fi
	cd src/
	make && make install && make start
fi

echo
cd -
exit 0

