#!/bin/bash
#

NAME=$1
SIZE=$2

QUERY="SELECT resource_image FROM resource_info WHERE resource_vname = '$NAME'"
logger "[RESIZE SCRIPT] Starting resize script actions for $NAME instance"

htvcenter_SERVER_CONF="/usr/share/htvcenter/etc/htvcenter-server.conf"
htvcenter_DB_BACKUP_DIR="/var/spool/htvcenter_db"
htvcenter_IMAGE_DB="htvcenter"


if [ ! -e "$htvcenter_SERVER_CONF" ]; then
        echo "ERROR: $htvcenter_SERVER_CONF is not existing"
        exit 1
fi
. $htvcenter_SERVER_CONF

if  [ "$htvcenter_DATABASE_PASSWORD" != "" ]; then
	DB_PASS="-p$htvcenter_DATABASE_PASSWORD"
fi

if ! which mysql 1>/dev/null 2>&1; then
	echo "ERROR: Mysql client 'mysql' not installed/found on this system" | logger
	exit 1
else
	export MYSQL_CLIENT=`which mysql`
fi

MYSQL="$MYSQL_CLIENT -N -B -u $htvcenter_DATABASE_USER $DB_PASS --host $htvcenter_DATABASE_SERVER $htvcenter_DATABASE_NAME -e "
MYSQL_IMAGE="$MYSQL_CLIENT -f -N -B -u $htvcenter_DATABASE_USER $DB_PASS --host $htvcenter_DATABASE_SERVER $htvcenter_IMAGE_DB "
logger "[RESIZE SCRIPT] Waiting for image creation of $NAME instance"
CLOUD_IMAGE_ID=`$MYSQL "$QUERY"`
if [ "$CLOUD_IMAGE_ID" == "" ]; then
		logger "[RESIZE SCRIPT] Empty resource name for $NAME instance - exit with error"
		exit 0
fi


CLOUD_IMAGE_ID=`$MYSQL "$QUERY"`
TIMEOUT=0
while [ "$CLOUD_IMAGE_ID" == "idle" ]
do
	sleep 10
	TIMEOUT=$(($TIMEOUT+10))
	CLOUD_IMAGE_ID=`$MYSQL "$QUERY"`
	logger "[RESIZE SCRIPT] Waiting for image creation of $NAME instance (TIME: $TIMEOUT seconds)"
	if [ "$TIMEOUT" == "300" ]; then
		logger "[RESIZE SCRIPT] Resize have been closed by timeout and was not done for $NAME instance"
		exit 0
	fi

done
logger "[RESIZE SCRIPT] Image name ($CLOUD_IMAGE_ID) have been taked for $NAME instance"


#logger "[RESIZE SCRIPT] State is active, just wait 30 second pause and will execute resize for $NAME instance"
#sleep 30

cd /usr/share/htvcenter/storage
CMD="qemu-img resize $CLOUD_IMAGE_ID $SIZE"
logger "[RESIZE SCRIPT] Executing command $CMD ..."

qemu-img resize "$CLOUD_IMAGE_ID" "$SIZE"
logger "[RESIZE SCRIPT] resize of $CLOUD_IMAGE_ID for $NAME instance was done!"

