. /etc/minder/quarter-hourly.conf
export TZ
# get current time
export CURRTIME=`date +"%H%M"`
HOD=`date +"%H"`
export HOD
MOD=`date +"%M"`
export MOD
echo "Current Time:"$CURRTIME 
#======================================================================
#
PATH=$PATH:/usr/sbin
#
# check for changes in ip
CURRENTIP=`ip addr show | grep "inet "`
if [ ! -e /data/tmp/bdcs.ip ]
then
	touch /data/tmp/bdcs.ip
fi
LASTIP=`cat /data/tmp/bdcs.ip`
if [ "$CURRENTIP" != "$LASTIP" ] 
then
	# ip changed
	echo "IP changed"
	/data/minder/cronjobs/Cron-reboot.sh
	rm /data/tmp/bdcs.ip
	echo  "$CURRENTIP" > /data/tmp/bdcs.ip
	/usr/local/bin/bdcsconn.apcd  >> /data/tmp/cron.bdcs.log 2>&1
fi
###

#
#
