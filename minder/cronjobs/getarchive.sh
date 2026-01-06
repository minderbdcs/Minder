:
#
# archive data to archive database
#

#  move to XX zero qty issns
#python /data/minder/python/script/archiveissn.py 
#=======================================================

. /etc/minder/MINDER.password
PATH=${PATH}:/opt/firebird/bin:/data/minder/script
#echo "path" $PATH
DOW=`date +"%w"`
export DOW
MYPROG=`basename $0`
if [ $DOW -eq 0 ]
then
        isql -user $ISC_USER -password $ISC_PASSWD  -i /data/minder/script/archiveprint.sql  $ISC_HOST:$ISC_DB
	RETCODE=$?
	#echo "isqlfb archiveprint returned ${RETCODE}" >> /data/minder/logs/$0.log
	echo "isqlfb archiveprint returned ${RETCODE}" >> /data/minder/logs/`basename $0.log`
	#  move to XX zero qty issns
	python /data/minder/python/script/archiveissn.py  db=$ISC_DB host=$ISC_HOST user=$ISC_USER passwd=$ISC_PASSWD tmp=/data/tmp
	RETCODE=$?
	#echo "python archivessn returned ${RETCODE}" >> /data/minder/logs/$0.log
	echo "python archivessn returned ${RETCODE}" >> /data/logs/${MYPROG}.log
fi

. /etc/minder/SYSDBA.password
isql -user $ISC_USER -password $ISC_PASSWD -i /data/minder/script/allowdelete.sql  $ISC_HOST:$ISC_DB
RETCODE=$?
#echo "isqlfb allowdelete returned ${RETCODE}" >> /data/minder/logs/$0.log
echo "isqlfb allowdelete returned ${RETCODE}" >> /data/logs/${MYPROG}.log

# archive data to archive database
#python /data/minder/python/script/archive.py /data/tmp/archive.log
python /data/minder/python/script/archive.py  db=$ISC_DB archivedb=$ISC_ARCHIVE_DB host=$ISC_HOST user=$ISC_USER passwd=$ISC_PASSWD tmp=/data/tmp

isql -user $ISC_USER -password $ISC_PASSWD -i /data/minder/script/stopdelete.sql  $ISC_HOST:$ISC_DB
RETCODE=$?
#echo "isqlfb stopdelete returned ${RETCODE}" >> /data/minder/logs/$0.log
echo "isqlfb stopdelete returned ${RETCODE}" >> /data/logs/${MYPROG}.log

