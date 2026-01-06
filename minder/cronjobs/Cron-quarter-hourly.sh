. /etc/minder/quarter-hourly.conf
export TZ
# get current time
export CURRTIME=`date +"%H%M"`
HOD=`date +"%H"`
export HOD
MOD=`date +"%M"`
export MOD
echo "Current Time:"$CURRTIME >> /data/logs/doquarter-hourly.log
#======================================================================
for qhridx in ${QHRTIMES}
do
        #echo "${QHRSTARTTIME[${qhridx}]}" 
        STARTTIME="${QHRSTARTTIME[${qhridx}]}" 
        LASTTIME="${QHRLASTTIME[${qhridx}]}" 
        #=================================================================================
        echo "Start   Time:"$STARTTIME >> /data/logs/doquarter-hourly.log
        echo "Last    Time:"$LASTTIME >> /data/logs/doquarter-hourly.log
        if [ "$CURRTIME" -le "$LASTTIME" -a "$CURRTIME" -ge "$STARTTIME"  ] 
        then
#
# ether run the test sftps or the prod ones depending in which server we are
#
                #su bdcs -c "/data/minder/script/sftpppin.sh prod >> /data/tmp/sftpppin.log 2>&1 &" 
#
                #su bdcs -c "/data/minder/script/doppsend.sh prod >> /data/tmp/sftpout.log 2>&1 &"
                #/data/minder/cronjobs/getpickmove.sh >>/data/tmp/getpickmove.qrh.log 2>&1
###
		#if [ $MOD -eq 00  -o $MOD -eq 30 ]
		#then
			/data/minder/cronjobs/hourlybackup.sh >> /data/logs/cron.quarterhourly.log 2>&1 
        	#fi
		#here
                /data/minder/cronjobs/check.bdcsprint.sh >>/data/tmp/check.bdcsprint.log 2>&1
                chmod 666 /data/tmp/check.bdcsprint.log
                chmod 666 /data/tmp/bdcs.check.log
                #/data/minder/cronjobs/getloggedin.sh  >>/data/tmp/check.loggedin.log 2>&1
        fi
        #======================================================================

done
#
#
