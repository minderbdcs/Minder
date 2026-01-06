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

HOD=`date +"%H"`
HOD=`date +"%H-%M"`
export HOD

MDRBfields=(`echo $MDRBminder | tr "." "\n"`)
MDRBPfx=${MDRBfields[0]}

# Database backups go here
DBBACKUP_DIR=${MDRBbackupdir}/${TODAY}
if [ ! -d ${DBBACKUP_DIR} ]; then
    mkdir -p ${DBBACKUP_DIR}
fi

# log file backups go here
BACKUP_DIR=${MDRBminderdir}/${TODAY}

if [ ! -d ${BACKUP_DIR} ]; then
    mkdir -p ${BACKUP_DIR}
fi

date >> /data/minder/backup/backup.nbk.log
${NBACKUP} -U $ISC_USER -P $ISC_PASSWD -B 2 minder ${DBBACKUP_DIR}/${MDRBminder%%fbk}2.${HOD}.nbk 2>&1 >>/data/minder/backup/backup.nbk.log
NBACKUPFILETO=${MDRBminder%%fbk}2.nbk 
NBACKUPFILEFROM=${DBBACKUP_DIR}/${MDRBminder%%fbk}2.${HOD}.nbk 
chgrp bdcs $NBACKUPFILEFROM
date >> /data/minder/backup/backup.nbk.log
#Verify required

# now put on mirror machine
if [  ! -z "${ISC_MIRROR_HOST}" ]; then
	if [ -e "${NBACKUPFILEFROM}" ]; then
		#scp ${NBACKUPFILEFROM}  ${ISC_MIRROR_HOST}:/backup/mirror/${NBACKUPFILETO}
		#ssh $ISC_MIRROR_HOST -C "date > /backup/mirror/${MDRBPfx}.hourly.trg"
		sudo -u bdcs bash -c "scp ${NBACKUPFILEFROM}  ${ISC_MIRROR_HOST}:/backup/mirror/${NBACKUPFILETO}"
		sudo -u bdcs bash -c "ssh $ISC_MIRROR_HOST -C \"date > /backup/mirror/${MDRBPfx}.hourly.trg\""
	fi
# now put other file on mirror machine
	rsync -avp /data/minder/TAX_INVOICE ${ISC_MIRROR_HOST}:/data/minder
	#sudo -u bdcs bash -c "rsync -avpze \"ssh\" /data/minder/TAX_INVOICE  ${ISC_MIRROR_HOST}:/data/minder"
	rsync -avp /data/minder/Printers ${ISC_MIRROR_HOST}:/data/minder
	#sudo -u bdcs bash -c "rsync -avpze \"ssh\" /data/minder/Printers  ${ISC_MIRROR_HOST}:/data/minder"

# what about /etc/hosts and cups printers
#rsync -avp /etc/hosts ${ISC_MIRROR_HOST}:/etc
#rsync -avp /etc/cups/printers.conf ${ISC_MIRROR_HOST}:/etc/cups
fi
