#!/bin/bash
#
# backup the database, and the minder directory tree
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

NBACKUP=${FBHOME}/bin/nbackup

TODAY=`date "+%Y%m%d"`

DOW=`date +"%w"`
export DOW

# Database backups go here
DBBACKUP_DIR=${MDRBbackupdir}/${TODAY}
if [ ! -d ${DBBACKUP_DIR} ]; then
    mkdir -p ${DBBACKUP_DIR}
fi

MDRBfields=(`echo $MDRBminder | tr "." "\n"`)
MDRBPfx=${MDRBfields[0]}

# log file backups go here
BACKUP_DIR=${MDRBminderdir}/${TODAY}

if [ ! -d ${BACKUP_DIR} ]; then
    mkdir -p ${BACKUP_DIR}
fi

# Why not just move this?
date >> /data/minder/backup/backup.nbk.log
# only do this on saturday
if [ $DOW -eq 6 ]
then
	${NBACKUP} -U $ISC_USER -P $ISC_PASSWD -B 1 minder ${DBBACKUP_DIR}/${MDRBminder%%fbk}1.nbk 2>&1 >>/data/minder/backup/backup.nbk.log
	NBACKUPFILE=${DBBACKUP_DIR}/${MDRBminder%%fbk}1.nbk 
	chgrp bdcs $NBACKUPFILE

date >> /data/minder/backup/backup.nbk.log
#Verify required

	# remove remote level 2 file
	#ssh $ISC_MIRROR_HOST -C "rm -f /backup/mirror/${MDRBPfx}*.2.nbk"
	sudo -u bdcs bash -c "ssh $ISC_MIRROR_HOST -C \"rm -f /backup/mirror/${MDRBPfx}*.2.nbk\""

	# now put on mirror machine
	if [ -e "${NBACKUPFILE}" ]; then
		#scp ${NBACKUPFILE}  ${ISC_MIRROR_HOST}:/backup/mirror
		#ssh $ISC_MIRROR_HOST -C "date > /backup/mirror/${MDRBPfx}.daily.trg"
		sudo -u bdcs bash -c "scp ${NBACKUPFILE}  ${ISC_MIRROR_HOST}:/backup/mirror"
		sudo -u bdcs bash -c "ssh $ISC_MIRROR_HOST -C \"date > /backup/mirror/${MDRBPfx}.daily.trg\""
	fi
fi

