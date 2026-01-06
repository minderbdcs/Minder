DOW=`date +"%w"`
export DOW
HOUR=`date +"%H"`
export HOUR

if [ "$DOW" -eq 0 ] 
then
	/data/minder/cronjobs/getarchive.sh >> /data/logs/cron.mid-evening.log 2>&1 
	echo "would run archive" >> /data/logs/cron.mid-evening.log 2>&1 
fi
/data/minder/cronjobs/backup.sh >> /data/logs/cron.mid-evening.log 2>&1 
/data/minder/cronjobs/fullbackup.sh >> /data/logs/cron.mid-evening.log 2>&1 
#
#/data/minder/cronjobs/getarchive.sh >> /data/logs/cron.mid-evening.log 2>&1 
