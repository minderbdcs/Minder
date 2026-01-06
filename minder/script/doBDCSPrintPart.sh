#!/bin/bash
# look for any running python printxxxxxFile print file send
# if found any then do nothing
# else run printxxxxxFile
echo $0
systemtype=$1
export systemtype
processtype=$2
export processtype
echo $1
echo $2
echo $3
echo $4
echo $5
echo $6
. /etc/minder/MINDER.password
# systemtype is the db alias to use
# processtype is the python task to use
#sleep 3
sleep 1
HAVEPROCESS=`pgrep -f "python /data/minder/python/script/print${processtype}File.lnx.py db=${systemtype}" | wc -l `
echo "haveprocess:" $HAVEPROCESS
date
if [ "$HAVEPROCESS" -eq "0" ]
then
        echo "haveprocess:" $HAVEPROCESS " eq 0"
        # create a flag file
        #date >> /data/tmp/print.${processtype}.${systemtype}
        echo "python /data/minder/python/script/print${processtype}File.lnx.py db=${systemtype} tmp=/data/tmp >>/data/tmp/print${processtype}fl.${systemtype}.log 2>&1"
        #python /data/minder/python/script/print${processtype}File.lnx.py db=${systemtype} tmp=/data/tmp >>/data/tmp/print${processtype}fl.${systemtype}.log 2>&1
        python /data/minder/python/script/print${processtype}File.lnx.py db=${systemtype} tmp=/data/tmp user=${ISC_USER} host=${ISC_HOST} passwd=${ISC_PASSWD} >>/data/tmp/print${processtype}fl.${systemtype}.log 2>&1
        #rm /tmp/print.${processtype}.${systemtype}
else
        echo "haveprocess:" $HAVEPROCESS " not eq 0 - so no print"
fi

