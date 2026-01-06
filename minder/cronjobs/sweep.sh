#!/bin/bash
#
# sweep  the database and ancillary processing
#
# Find where Firebird is installed on this server
. /etc/minder/MINDER.firebird
# Obtain the sysdba password
. /etc/minder/MINDER.password
. /etc/minder/SYSDBA.password
# Obtain the database names
. /etc/minder/MINDER.databases
# Obtain backup requirements
. /etc/minder/MINDER.backups

PATH=${PATH}:${FBHOME}/bin:/data/minder/cronjobs
export PATH

GFIX=${FBHOME}/bin/gfix
ISQL=${FBHOME}/bin/isql

HAVEPROCESS=`ls /data/tmp/run.sweep 2>/dev/null | wc -l `
echo "haveprocess:" $HAVEPROCESS
date
if [ "$HAVEPROCESS" -eq "0" ] 
then
	echo "haveprocess:" $HAVEPROCESS " eq 0"

	HAVEMEM=`free -m | awk '/Mem/ {print $4}'`
	#if [ "$HAVEMEM" -gt "512" ] 
	#if [ "$HAVEMEM" -gt "400" ] 
	#if [ "$HAVEMEM" -gt "200" ] 
	if [ "$HAVEMEM" -gt "100" ] 
	then
		echo "more than 512m available "  >> /data/tmp/sweep.log
		cd /data/tmp

		date >  /data/tmp/run.sweep
		date >> /data/tmp/sweep.log
		${ISQL} -u $ISC_USER -p $ISC_PASSWD  127.0.0.1:minder >> /data/tmp/sweep.log <<EOF
SHOW DATABASE;
EXIT;
EOF

		${GFIX} -sweep -user $ISC_USER -password $ISC_PASSWD 127.0.0.1:minder  2>>/data/tmp/sweep.log

		echo "sweep completed"  >> /data/tmp/sweep.log
	else
		echo "not enough free memory for sweep "  >> /data/tmp/sweep.log
		echo "$HAVEMEM"  >> /data/tmp/sweep.log
	fi
	rm -f /data/tmp/run.sweep
else
	echo "sweep already running "  >> /data/tmp/sweep.log
fi
#${GFIX} -sweep -user sysdba -password masterkey 127.0.0.1:minder 2>&1 >>/data/minder/backup/backup.log
#${GFIX} -sweep -user sysdba -password masterkey 127.0.0.1:archive  2>&1 >>/data/minder/backup/backup.log
