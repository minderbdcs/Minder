:
#
# remove old logged data
#
# get ages to keep
# has printer folders age
# ftp folders age
# backup folders age
# the list of ftp folders
. /etc/minder/backup.conf
for printerwd in /data/minder/Printers/P*
do
	find $printerwd -name "????-??-??" -type d -mtime $BCKPRINTERAGE -exec rm -rf '{}' \;
	find $printerwd -maxdepth 1 -type f -empty -mtime +0 -exec rm -rf '{}' \;

done
#find /tmp -name "????-??-??" -type d -mtime $BCKPRINTERAGE -exec rm -rf '{}' \;
find /tmp -name "????-??-??" -type d -mtime $BCKTMPAGE -exec rm -rf '{}' \;
#find /scratch/backup/web -name "????????" -type d -mtime $BCKBACKUPAGE -exec rm -rf '{}' \;
find /data/backup/web -name "????????" -type d -mtime $BCKBACKUPAGE -exec rm -rf '{}' \;
find /backup/fbdb -name "????????" -type d -mtime $BCKTMPAGE -exec rm -rf '{}' \;
find /backup/minder -name "????????" -type d -mtime $BCKTMPAGE -exec rm -rf '{}' \;
find /data/tmp -name "????-??-??" -type d -mtime $BCKTMPAGE -exec rm -rf '{}' \;
find /data/logs/httpd -name "????-??-??" -type d -mtime $BCKTMPAGE -exec rm -rf '{}' \;
find /data/logs -name "????-??-??" -type d -mtime $BCKTMPAGE -exec rm -rf '{}' \;
for ftpidx in ${BCKS}
do 
	echo "${BCKFTPARG[${ftpidx}]}" 
	ftpwd="${BCKFTPARG[${ftpidx}]}" 
	find $ftpwd -name "????-??-??" -type d -mtime $BCKFTPAGE -exec rm -rf '{}' \;
done
