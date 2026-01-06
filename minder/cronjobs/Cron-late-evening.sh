/data/minder/cronjobs/backup.web.sh >> /data/logs/cron.late-evening.log 2>&1 
#	
DOW=`date +"%w"`
export DOW
HOUR=`date +"%H"`
export HOUR

if [ "$DOW" -eq 6 ] 
then
	/data/minder/cronjobs/dailybackup.sh >> /data/logs/cron.late-evening.log 2>&1 
fi
