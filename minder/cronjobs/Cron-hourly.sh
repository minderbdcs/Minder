
#/data/minder/cronjobs/getpickmove.sh >> /data/logs/cron.hourly.log 2>&1

HOD=`date +"%H"`
export HOD

#if [ $HOD -lt 11  -o $HOD -gt 17 ]
if [ $HOD -lt 13  -o $HOD -gt 17 ]
then
	#/data/minder/cronjobs/hourlybackup.sh >> /data/logs/cron.hourly.log 2>&1 
	echo "$HOD was doing hourlybackup.sh"
else
	echo "outside hour range cronhourly" >> /data/logs/cron.hourly.log
	date  >> /data/logs/cron.hourly.log

fi
# this is for dr machines hourly
#/data/minder/cronjobs/hourlyrestore.sh >> /data/logs/cron.hourly.log 2>&1 
