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
export LANGUAGE=C
export LANG=C
export LC_ALL=C

function usage() {
		echo "Usage : $0 integrate -s [size] | remove"
}

function integrate_tmpfs() {
#########mount tmpfs#####
#####Create directory to mount tmpfs and to use it for the chunserver
size=$size\G
mkdir /mnt/ramdisk
chmod 777 /mnt/ramdisk
chown mfs:mfs /mnt/ramdisk

#Mount tmpfs (memory) to the directory
mount -t tmpfs -o size=$size tmpfs /mnt/ramdisk
chmod 777 /mnt/ramdisk
chown mfs:mfs /mnt/ramdisk

#Mount automaticaly during the booting:	
echo "tmpfs /mnt/ramdisk tmpfs defaults,size=$size 0 0" >> /etc/fstab
	
#######set the chunkserver file####
#create specific /ets/mfs/mfschunkserver.cfg and /etc/mfs/mfshdd.cfg files in /etc/mfs/ for the tmpfs storage
echo "/mnt/ramdisk" > /etc/mfs/mfshdd2.cfg

#copy mfschunkserver.cfg mfschunkserver2.cfg

cp /etc/mfs/mfschunkserver.cfg.dist /etc/mfs/mfschunkserver2.cfg
		
		
#		LABEL = chunk1,ssd <- use the name you want	
#		CSSERV_LISTEN_PORT = xxxx <- choose two different ports to listen on
#		DATA_PATH = xxxx <- choose two different directories to store chunkserver's stats and lockfile
#		HDD_CONF_FILENAME = xxxx <- create two different hdd files
		
#		LABEL = ramdisk 
#		CSSERV_LISTEN_PORT = 9423 
#		DATA_PATH = /var/lib/mfs2 
#		HDD_CONF_FILENAME = /etc/mfsmfshdd2.cfg
		
sed -i -e s/.*LABEL.*/"LABEL = ramdisk"/g /etc/mfs/mfschunkserver2.cfg
sed -i -e s/.*CSSERV_LISTEN_PORT.*/"CSSERV_LISTEN_PORT = 9423"/g /etc/mfs/mfschunkserver2.cfg
sed -i -e s/.*DATA_PATH.*/"DATA_PATH = \/var\/lib\/mfs2"/g /etc/mfs/mfschunkserver2.cfg
sed -i -e s/.*HDD_CONF_FILENAME.*/"HDD_CONF_FILENAME = \/etc\/mfs\/mfshdd2.cfg"/g /etc/mfs/mfschunkserver2.cfg
	
sed -i -e s/.*LIZARDFSCHUNKSERVER_ENABLE.*/"LIZARDFSCHUNKSERVER_ENABLE=true"/g /etc/default/lizardfs-chunkserver

mkdir /var/lib/mfs2
chown -R mfs:mfs /var/lib/mfs2
chmod -R 755 /var/lib/mfs2

#######Set the goals to store in ramdisk and second copy on any other chunkserver#######
sed -i -e s/.*"20".*/"20 fast : ramdisk _"/g /etc/mfs/mfsgoals.cfg
#echo "40 fast : ramdisk _" >> /etc/mfs/mfsgoals.cfg


cp /etc/init.d/lizardfs-chunkserver /etc/init.d/lizardfs-chunkserver2
sed -i -e s/.*"DEFAULT_CFG=\/etc\/mfs\/mfschunkserver.cfg".*/"DEFAULT_CFG=\/etc\/mfs\/mfschunkserver2.cfg"/g /etc/init.d/lizardfs-chunkserver2
update-rc.d lizardfs-chunkserver2 defaults
/etc/init.d/lizardfs-chunkserver2 start

#####restart the master####
/etc/init.d/lizardfs-master restart
}

function remove_tmpfs() {
/etc/init.d/lizardfs-chunkserver2 stop
update-rc.d -f lizardfs-chunkserver2 remove

rm -rf /etc/mfs/mfschunkserver2.cfg /etc/mfs/mfshdd2.cfg /var/lib/mfs2
umount /mnt/ramdisk
sed -i '/ramdisk/d' /etc/fstab

}

case "$1" in
	integrate)
		shift
		if [ $# == 0 ]; then
			usage
			exit 0
		fi
		while [ $# -ne 0 ]; do
			case "$1" in
				-s)
					size=$2
					;;

			esac
			shift
		done
		if [ "$size" == "" ]; then
			echo "not size defined!"
			usage
			exit 1
		fi

		export size
		integrate_tmpfs
		;;
		
	remove)
		remove_tmpfs
		;;
	*)
		usage
		exit 0
		;;

esac

