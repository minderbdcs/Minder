:
#
# save logged in users 
#

#=======================================================

. /etc/minder/MINDER.password
PATH=${PATH}:/opt/firebird/bin:/data/minder/script
#echo "path" $PATH
DOW=`date +"%w"`
export DOW
MYPROG=`basename $0`
        isql -user $ISC_USER -password $ISC_PASSWD  -i /data/minder/cronjobs/loggedin.sql   $ISC_HOST:$ISC_DB
	RETCODE=$?
	echo "isqlfb loggedon returned ${RETCODE}" 
	#echo "isqlfb loggedon returned ${RETCODE}" >> /data/minder/logs/$MYPROG.log


