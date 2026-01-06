#!/bin/sh
#
#  full backup of Minder Code for roll-back 

TODAY=`date "+%Y%m%d"`
BACKUP_DIR=/data/backup/web/${TODAY}

if [ ! -d ${BACKUP_DIR} ]; then
    mkdir -p ${BACKUP_DIR}
    chown bdcs:bdcs ${BACKUP_DIR}
    chmod 770 ${BACKUP_DIR}
fi

cd /
#TODO use rsync for an intellegent use of space
tar -cj --atime-preserve -f ${BACKUP_DIR}/data.tar data/minder data/tmp data/ftp data/sites
    chown bdcs:bdcs ${BACKUP_DIR}/*
    chmod 660 ${BACKUP_DIR}/*
