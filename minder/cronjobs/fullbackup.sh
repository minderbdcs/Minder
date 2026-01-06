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

GBAK=${FBHOME}/bin/gbak
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
rm -f /data/minder/backup/${MDRBminder}
date >> /data/minder/backup/backup.log
${GBAK} -user $ISC_USER -password $ISC_PASSWD -b -v 127.0.0.1:minder ${DBBACKUP_DIR}/${MDRBminder} 2>&1 >>/data/minder/backup/backup.log
date >> /data/minder/backup/backup.log
${GBAK} -user $ISC_USER -password $ISC_PASSWD -b -v 127.0.0.1:archive ${DBBACKUP_DIR}/${MDRBarchive} 2>&1 >>/data/minder/backup/backup.arc.log
GBACKUPFILEARC=${DBBACKUP_DIR}/${MDRBarchive} 
date >> /data/minder/backup/backup.log
date >> /data/minder/backup/backup.nbk.log
if [ $DOW -eq 6 ]
then
	${NBACKUP} -U $ISC_USER -P $ISC_PASSWD -B 0 minder ${DBBACKUP_DIR}/${MDRBminder%%fbk}0.nbk 2>&1 >>/data/minder/backup/backup.nbk.log
	NBACKUPFILE=${DBBACKUP_DIR}/${MDRBminder%%fbk}0.nbk 
	${NBACKUP} -U $ISC_USER -P $ISC_PASSWD -B 1 minder ${DBBACKUP_DIR}/${MDRBminder%%fbk}1.nbk 2>&1 >>/data/minder/backup/backup.nbk.log
	NBACKUPFILE2=${DBBACKUP_DIR}/${MDRBminder%%fbk}1.nbk 

else
	${NBACKUP} -U $ISC_USER -P $ISC_PASSWD -B 1 minder ${DBBACKUP_DIR}/${MDRBminder%%fbk}1.nbk 2>&1 >>/data/minder/backup/backup.nbk.log
	NBACKUPFILE=${DBBACKUP_DIR}/${MDRBminder%%fbk}1.nbk 
fi
date >> /data/minder/backup/backup.nbk.log
#Verify required

# now put on mirror machine
date +"%c.%N" >> /data/minder/backup/backup.nbk.log
if [ ! -z "${ISC_MIRROR_HOST}" ]; then
	# if have a mirror host
	if [ -e "${NBACKUPFILE}" ]; then
		#scp ${NBACKUPFILE}  ${ISC_MIRROR_HOST}:/backup/mirror
		rsync -avpz ${NBACKUPFILE}  ${ISC_MIRROR_HOST}:/backup/mirror
		date +"%c.%N" >> /data/minder/backup/backup.nbk.log
		echo "copied "${NBACKUPFILE} >> /data/minder/backup/backup.nbk.log
		rsync -avpz ${NBACKUPFILE}  ${ISC_MIRROR_HOST2}:/backup/mirror
		echo "copied "${NBACKUPFILE}" to "${ISC_MIRROR_HOST2} >> /data/minder/backup/backup.nbk.log
	fi
# remove remote level 2 file
	ssh $ISC_MIRROR_HOST -C "rm -f /backup/mirror/${MDRBPfx}*.2.nbk"
# only do this on saturday
	if [ $DOW -eq 6 ]
	then
		# remove level 1 file
		ssh $ISC_MIRROR_HOST -C "rm -f /backup/mirror/${MDRBPfx}*.1.nbk"
		date >> /data/minder/backup/backup.nbk.log
		if [ -e "${NBACKUPFILE2}" ]; then
			scp ${NBACKUPFILE2}  ${ISC_MIRROR_HOST}:/backup/mirror
			scp ${NBACKUPFILE2}  ${ISC_MIRROR_HOST2}:/backup/mirror
		fi
	fi
	if [ -e "${NBACKUPFILE}" ]; then
		if [ $DOW -eq 6 ]
		then
			if [ -e "${NBACKUPFILE2}" ]; then
				ssh $ISC_MIRROR_HOST -C "date > /backup/mirror/${MDRBPfx}.daily.trg"
			else
				ssh $ISC_MIRROR_HOST -C "date > /backup/mirror/${MDRBPfx}.full.trg"
			fi
		else
			ssh $ISC_MIRROR_HOST -C "date > /backup/mirror/${MDRBPfx}.daily.trg"
		fi
	fi
	if [ -e "${GBACKUPFILEARC}" ]; then
		# remove archive file
		ssh $ISC_MIRROR_HOST -C "rm -f /backup/mirror/${MDRBarchive}"
		date >> /data/minder/backup/backup.nbk.log
		scp ${GBACKUPFILEARC}  ${ISC_MIRROR_HOST}:/backup/mirror
	fi
fi

# zip up created .fbk file
gzip ${DBBACKUP_DIR}/${MDRBarchive} 2>&1 >>/data/minder/backup/backup.arc.log
gzip ${DBBACKUP_DIR}/${MDRBminder} 2>&1 >>/data/minder/backup/backup.log
#
cd /
#Do we need to back up ALL the software every day?
#tar -cjf ${BACKUP_DIR}/data.tar data/minder data/fbdb
tar -cjf ${BACKUP_DIR}/data.tar --atime-preserve data/minder 2>&1 >> /data/minder/backup/backup.log
