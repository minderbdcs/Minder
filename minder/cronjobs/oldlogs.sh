#!/bin/bash
#
# Trim the log files and tmp files
#
# Find where Firebird is installed on this server
. /etc/minder/MINDER.firebird
# Obtain the sysdba password
. /etc/minder/MINDER.password
# Obtain the database names
. /etc/minder/MINDER.databases
# Obtain backup requirements
. /etc/minder/MINDER.backups

PATH=${PATH}:${FBHOME}/bin:/data/minder/cronjobs
export PATH

GBAK=${FBHOME}/bin/gbak
#
# remove old  backups and logged data
#
find /tmp -name "????-??-??" -type d -mtime +5 -exec rm -rf '{}' \;
find /tmp -maxdepth 1 -type f -empty -mtime +0 -exec rm -rf '{}' \;

find /backup/fbdb -name "????????" -type d -mtime +2 -exec rm -rf '{}' \;
find /backup/minder -name "????????" -type d -mtime +2 -exec rm -rf '{}' \;

find /data/minder/tmp -name "????-??-??" -type d -mtime +15 -exec rm -rf '{}' \;
find /data/minder/tmp/minder -name "????-??-??" -type d -mtime +7 -exec rm -rf '{}' \;
find /data/backup/web -name "????????" -type d -mtime +2 -exec rm -rf '{}' \;

