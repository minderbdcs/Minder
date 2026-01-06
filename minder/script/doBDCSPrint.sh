#!/bin/sh
# look for any new *.queue file in /data/tmp/minder
# if found any then do doBDCSPrintPart for the db 
cd /tmp

. /etc/minder/MINDER.password
#minder 
#nohup python /data/minder/python/script/autocompile.py /data/tmp/minder queue "/data/minder/script/doBDCSPrintPart.sh minder Queue  >>/data/tmp/autoprint.queue.minder.2.log 2>&1 " >>/data/tmp/autocompile.queue.minder.log 2>&1 &
nohup python /data/minder/python/script/autocompile.py /data/tmp/minder queue "/data/minder/script/doBDCSPrintPart.sh ${ISB_DB} Queue ${ISC_HOST} ${ISC_USER} ${ISC_PASSWD}  >>/data/tmp/autoprint.queue.minder.2.log 2>&1 " >>/data/tmp/autocompile.queue.minder.log 2>&1 &
#nohup python /data/minder/python/script/autocompile.py /data/tmp/minder create "/data/minder/script/doBDCSPrintPart.sh minder Create >>/data/tmp/autoprint.create.minder.2.log 2>&1 " >>/data/tmp/autocompile.create.minder.log 2>&1 &
nohup python /data/minder/python/script/autocompile.py /data/tmp/minder create"/data/minder/script/doBDCSPrintPart.sh ${ISB_DB} Create ${ISC_HOST} ${ISC_USER} ${ISC_PASSWD}  >>/data/tmp/autoprint.create.minder.2.log 2>&1 " >>/data/tmp/autocompile.create.minder.log 2>&1 &
#nohup python /data/minder/python/script/autocompile.py /data/tmp/minder send "/data/minder/script/doBDCSPrintPart.sh minder Send >>/data/tmp/autoprint.send.minder.2.log 2>&1 " >>/data/tmp/autocompile.send.minder.log 2>&1 &
nohup python /data/minder/python/script/autocompile.py /data/tmp/minder send "/data/minder/script/doBDCSPrintPart.sh minder Send >>/data/tmp/autoprint.send.minder.2.log 2>&1 " >>/data/tmp/autocompile.send.minder.log 2>&1 &
nohup python /data/minder/python/script/autocompile.py /data/tmp/minder send"/data/minder/script/doBDCSPrintPart.sh ${ISB_DB} Send ${ISC_HOST} ${ISC_USER} ${ISC_PASSWD}  >>/data/tmp/autoprint.send.minder.2.log 2>&1 " >>/data/tmp/autocompile.send.minder.log 2>&1 &

#
