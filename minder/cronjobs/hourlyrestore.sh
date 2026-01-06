#!/bin/bash
#
# restore the mirror database
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
export HOD

MDRBfields=(`echo $MDRBminder | tr "." "\n"`)
MDRBPfx=${MDRBfields[0]}

date >> /data/minder/backup/backup.nbk.log
################################################################################
#
# check for trg file in /backup/mirror
#
# if found
# delete the  current database file  - maybe save this
# 
# create a new file using nbackup
# if the trigger file is mdr.full.trg just use the 1 file
# if the trigger file is mdr.daily.trx use the 2 files
# if the trigger file is mdr.hourly.trg use all 3 files
#
# then rename the trigger file
#################################################################################
cd /backup/mirror
# calc the db file
MDRBminderfile=`egrep '^minder' /opt/firebird/aliases.conf | fgrep -v '#' | awk '{print $3}'`
if [ -e "${MDRBPfx}.full.trg" ]; then
	echo "found full trigger" >> /data/minder/backup/backup.nbk.log
	# rm db file
	rm -f ${MDRBminderfile}

	${NBACKUP} -U $ISC_USER -P $ISC_PASSWD -R  minder ${MDRBminder%%fbk}0.nbk 2>&1 >>/data/minder/backup/backup.nbk.log

	mv ${MDRBPfx}.full.trg  ${MDRBPfx}.full.done 
	chown firebird:firebird ${MDRBminderfile}
fi
if [ -e "${MDRBPfx}.daily.trg" ]; then
	echo "found daily trigger" >> /data/minder/backup/backup.nbk.log
	# rm db file
	rm -f ${MDRBminderfile}

	${NBACKUP} -U $ISC_USER -P $ISC_PASSWD -R  minder ${MDRBminder%%fbk}0.nbk ${MDRBminder%%fbk}1.nbk 2>&1 >>/data/minder/backup/backup.nbk.log

	mv ${MDRBPfx}.daily.trg  ${MDRBPfx}.daily.done 
	chown firebird:firebird ${MDRBminderfile}
fi
if [ -e "${MDRBPfx}.hourly.trg" ]; then
	echo "found hourly trigger" >> /data/minder/backup/backup.nbk.log
	# rm db file
	rm -f ${MDRBminderfile}

	${NBACKUP} -U $ISC_USER -P $ISC_PASSWD -R  minder ${MDRBminder%%fbk}0.nbk ${MDRBminder%%fbk}1.nbk ${MDRBminder%%fbk}2.nbk 2>&1 >>/data/minder/backup/backup.nbk.log

	mv ${MDRBPfx}.hourly.trg  ${MDRBPfx}.hourly.done 
	chown firebird:firebird ${MDRBminderfile}
fi


date >> /data/minder/backup/backup.nbk.log


