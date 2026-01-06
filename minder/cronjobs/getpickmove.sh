:
# reset the statuses of the AS and OP status pick items
# 
PATH=${PATH}:/opt/firebird/bin:/data/minder/script
#echo "path" $PATH
. /etc/minder/MINDER.password
DOW=`date +"%w"`
export DOW
HOUR=`date +"%H"`
export HOUR

echo "prod"
if [ "$HOUR" -gt 20 ] 
then
	isql -user $ISC_USER -password $ISC_PASSWD -i /data/minder/script/resetPickItem.sql  $ISC_HOST:$ISC_DB
else
	isql -user $ISC_USER -password $ISC_PASSWD -i /data/minder/script/resetPickItem.sql  $ISC_HOST:$ISC_DB
fi
echo "end prod"

