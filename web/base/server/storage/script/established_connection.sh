#!/bin/bash
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

# This script integrates a local (existing) server into htvcenter
# The system then can (should) be set to netboot to gain the full
# advantages and flexibility of the htvcenter management platform


if ["$resource_basedir" == ""]; then
		resource_basedir='/usr/share'
fi
resource_execdport='1667'

if [ "$htvcenter_web_protocol" == "https" ]; then
	WGET="wget -q --no-check-certificate"
else
	WGET="wget -q"
fi

htvcenter_RESOURCE_PARAMETER_FILE="/var/htvcenter/htvcenter-resource.conf"
export LANGUAGE=C
export LANG=C
export LC_ALL=C


function usage() {
		echo "Usage : $0 integrate -q <ip-address-of-htvcenter-server> "
#		echo "        $0 remove -u <user> -p <password> -q <ip-address-of-htvcenter-server> [ -n <hostname> ] [-s <http/https>]"
}


function htvcenter_integrate_local_server() {


	echo "Integrating system to htvcenter-server at $htvcenter_SERVER"
	if [ -f /etc/debian_version ]; then
		apt-get install rpl
		apt-get -y install wget
		#apt-get install mmv
	fi


	# check for dropbear
	DROPBEAR=`which dropbear`
	if test -z $DROPBEAR; then
		echo "-> could not find dropbear. Trying to automatically install it ..."
		if [ -f /etc/debian_version ]; then
			
			if ! apt-get -y install dropbear; then
				echo "Failed to install required package dropbear!"
				echo "Please install dropbear and try again!"
				return 1
			fi
	
		elif [ -f /etc/redhat-release ]; then
			#cd /tmp
			yum -y install wget
			# check for rpmforge
			echo "Checking for rpmforge/DAG repository ..."
			if rpm -qa | grep rpmforge 1>/dev/null; then
				echo "-> found rpmforge repository available"
			else
				echo "ERROR: Please enable the rpmforge/DAG repository!"
				return 1
			fi
			# check for epel-release
			echo "Checking for epel-release repository ..."
			if rpm -qa | grep epel-release 1>/dev/null; then
				echo "-> found epel-release repository available"
			else
				echo "ERROR: Please enable the epel-release repository!"
				return 1
			fi
			if ! yum -y install dropbear; then
				echo "Failed to install required package dropbear!"
				echo "Please install dropbear and try again!"
				return 1
			fi
		elif [ -f /etc/SuSE-release ]; then
			if ! zypper --non-interactive install dropbear; then
				zypper --non-interactive install wget
				echo "Failed to install required package dropbear!"
				echo "Please install dropbear and try again!"
				return 1
			fi
		else
			echo "Failed to find package manager to automatically install dropbear."
			echo "Please install dropbear and try again!"
			return 1
		fi
	fi

	mkdir -p `dirname $htvcenter_RESOURCE_PARAMETER_FILE`

echo 'INDROPBEAR'
			# install and use the distro dropbear package
			DROPBEAR=`which dropbear`
			if test -z $DROPBEAR; then
			
				FORCE_INSTALL=true htvcenter_install_os_dependency dropbear
				# on debian and ubuntu, lets make sure it is not started as a service due to our install
				if test -e /etc/default/dropbear; then
					
					if grep '^NO_START=0' /etc/default/dropbear 1>/dev/null|| ! grep 'NO_START' /etc/default/dropbear 1>/dev/null; then
						# looks like it has been set to start by default; let's revert that
						/etc/init.d/dropbear stop
						sed -i -e "s/^NO_START=0/NO_START=1/g" /etc/default/dropbear
						# just in case it was never there in the first place
						echo "NO_START=1" >> /etc/default/dropbear
					fi
				fi
			fi

			# start dropbear as htvcenter-execd
			/bin/rm -rf $resource_basedir/htvcenter/etc/dropbear
			
			mkdir -p $resource_basedir/htvcenter/etc/dropbear/
			
			if ! dropbearkey -t rsa -f $resource_basedir/htvcenter/etc/dropbear/dropbear_rsa_host_key; then
				echo "ERROR: Could not create host key with dropbearkey. Please check to have dropbear installed correctly!"
				return 1
			fi
			# get the public key of the htvcenter server			
			wget http://$htvcenter_SERVER/htvcenter/boot-service/htvcenter-server-public-rsa-key
			
			
			if [ ! -d /root/.ssh ]; then
				mkdir -p /root/.ssh
				chmod 700 /root/.ssh
			fi
			
			if [ ! -f /root/.ssh/authorized_keys ]; then
				
				mv -f htvcenter-server-public-rsa-key /root/.ssh/authorized_keys
				chmod 600 /root/.ssh/authorized_keys
			else
			
				htvcenter_HOST=`cat htvcenter-server-public-rsa-key | awk {' print $3 '}`
				if grep $htvcenter_HOST /root/.ssh/authorized_keys 1>/dev/null; then
					
					sed -i -e "s#.*$htvcenter_HOST.*##g" /root/.ssh/authorized_keys
				fi
				
				cat htvcenter-server-public-rsa-key >> /root/.ssh/authorized_keys
				rm -f htvcenter-server-public-rsa-key
				chmod 600 /root/.ssh/authorized_keys
			fi
			# start dropbear
			
			#echo "dropbear -p $resource_execdport -r $resource_basedir/htvcenter/etc/dropbear/dropbear_rsa_host_key"
			dropbear -p $resource_execdport -r $resource_basedir/htvcenter/etc/dropbear/dropbear_rsa_host_key

}

	# re-get parameters

case "$1" in
	integrate)
		shift
		if [ $# == 0 ]; then
			usage
			exit 0
		fi
		while [ $# -ne 0 ]; do
			case "$1" in
				-q)
					htvcenter_SERVER=$2
					;;
				-i)
			esac
			shift
		done
		if [ "$htvcenter_SERVER" == "" ]; then
			echo "htvcenter_SERVER: Missing htvcenter-server ip-address !"
			usage
			exit 1
		fi
		if [ "$htvcenter_WEB_PROTOCOL" == "" ]; then
			htvcenter_WEB_PROTOCOL=http
		fi
		export USER
		export PASSWORD
		export htvcenter_SERVER
		export INTERFACE
		export APPLIANCE_NAME
		export htvcenter_WEB_PROTOCOL
		htvcenter_integrate_local_server
		;;

	remove)
		shift
		if [ $# == 0 ]; then
			usage
			exit 0
		fi
		while [ $# -ne 0 ]; do
			case "$1" in
				-u)
					USER=$2
					;;
				-p)
					PASSWORD=$2
					;;
				-q)
					htvcenter_SERVER=$2
					;;
				-n)
					APPLIANCE_NAME=$2
					;;
				-s)
					htvcenter_WEB_PROTOCOL=$2
					;;
			esac
			shift
		done
		if [ "$USER" == "" ]; then
			echo "ERROR: Missing username !"
			usage
			exit 1
		fi
		if [ "$PASSWORD" == "" ]; then
			echo "PASSWORD: Missing password !"
			usage
			exit 1
		fi
		if [ "$htvcenter_SERVER" == "" ]; then
			echo "htvcenter_SERVER: Missing htvcenter-server ip-address !"
			usage
			exit 1
		fi
		if [ "$htvcenter_WEB_PROTOCOL" == "" ]; then
			htvcenter_WEB_PROTOCOL=http
		fi
		export USER
		export PASSWORD
		export htvcenter_SERVER
		export APPLIANCE_NAME
		export htvcenter_WEB_PROTOCOL
		htvcenter_remove_local_server $USER $PASSWORD $htvcenter_SERVER
		;;

	*)
		usage
		exit 0
		;;

esac