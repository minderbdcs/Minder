. /etc/minder/quarter-hourly.conf
export TZ
# get current time
export CURRTIME=`date +"%H%M"`
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
                #/data/minder/cronjobs/getpickmove.sh >>/data/tmp/getpickmove.log 2>&1
                ##/data/minder/cronjobs/check.bdcsprint.sh >>/data/tmp/check.bdcsprint.log 2>&1
                chmod 666 /data/tmp/check.bdcsprint.log
                chmod 666 /data/tmp/bdcs.check.log
        fi
        #======================================================================

done
#
#

