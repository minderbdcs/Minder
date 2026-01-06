#:
# check Send is running OK and restart if not running
#
# get last date from /data/tmp/printSend.minder.log
LASTDT=`stat /data/tmp/printSend.minder.log |awk -F': ' '/Modify: /{print $2}'`
echo $LASTDT
LASTDT2=`echo $LASTDT | awk -F+ '{print $1}'`
echo $LASTDT2
#
# get current date
touch /data/tmp/bdcs.check.log
NOWDT=`stat /data/tmp/bdcs.check.log |awk -F': ' '/Modify: /{print $2}'`
NOWDT2=`echo $NOWDT | awk -F+ '{print $1}'`
echo $NOWDT2
#
# do diff
# echo $(( ( $(date -ud '2003-08-02 17:24:33' +'%s') - $(date -ud '2003-04-21 22:55:02' +'%s') ) )) seconds
#echo $(( ( $(date -ud "$NOWDT2" +'%s') - $(date -ud "$LASTDT2" +'%s') ) ))
AGE=`echo $(( ( $(date -ud "$NOWDT2" +'%s') - $(date -ud "$LASTDT2" +'%s') ) ))`

# if diff > 65 seconds then must restart the printSendFile.py process
#
if [ $AGE -gt "90" ] 
then
	echo "Send Age is too big"
    	myprog="/data/minder/python/script/printSendFile.fdb.py"
       	pkill -f "python $myprog"
	export USER="bdcs"
    	. /etc/minder/sh/MINDER.password
        myopts="db=minder host=$ISC_HOST user=$ISC_USER passwd=$ISC_PASSWD tmp=/data/tmp condwait=60"
        #runuser -l ${USER} -c "cd /data/tmp && python $myprog $myopts >printsendfl.log  2>&1 & "
        su  ${USER} -c "cd /data/tmp && python $myprog $myopts >printsendfl.log  2>&1 & "
fi
#
# check Queue is running OK and restart if not running
#
# get last date from /data/tmp/printQueue.minder.log
LASTDT=`stat /data/tmp/printQueue.minder.log |awk -F': ' '/Modify: /{print $2}'`
echo $LASTDT
LASTDT2=`echo $LASTDT | awk -F+ '{print $1}'`
echo $LASTDT2
#
# get current date
touch /data/tmp/bdcs.check.log
NOWDT=`stat /data/tmp/bdcs.check.log |awk -F': ' '/Modify: /{print $2}'`
NOWDT2=`echo $NOWDT | awk -F+ '{print $1}'`
echo $NOWDT2
#
# do diff
# echo $(( ( $(date -ud '2003-08-02 17:24:33' +'%s') - $(date -ud '2003-04-21 22:55:02' +'%s') ) )) seconds
#echo $(( ( $(date -ud "$NOWDT2" +'%s') - $(date -ud "$LASTDT2" +'%s') ) ))
AGE=`echo $(( ( $(date -ud "$NOWDT2" +'%s') - $(date -ud "$LASTDT2" +'%s') ) ))`

# if diff > 65 seconds then must restart the printQueueFile.py process
#
if [ $AGE -gt "90" ] 
then
	echo "Queue Age is too big"
        myprog3="/data/minder/python/script/printQueueFile.fdb.py"
       	pkill -f "python $myprog3"
	export USER="bdcs"
    	. /etc/minder/sh/MINDER.password
        myopts="db=minder host=$ISC_HOST user=$ISC_USER passwd=$ISC_PASSWD tmp=/data/tmp condwait=60"
        #runuser -l ${USER} -c "cd /data/tmp && python $myprog3 $myopts >printqueuefl.log  2>&1 & "
        su  ${USER} -c "cd /data/tmp && python $myprog3 $myopts >printqueuefl.log  2>&1 & "
fi
#
#
# check Create is running OK and restart if not running
#
# get last date from /data/tmp/printWrite.minder.log
LASTDT=`stat /data/tmp/printWrite.minder.log |awk -F': ' '/Modify: /{print $2}'`
echo $LASTDT
LASTDT2=`echo $LASTDT | awk -F+ '{print $1}'`
echo $LASTDT2
#
# get current date
touch /data/tmp/bdcs.check.log
NOWDT=`stat /data/tmp/bdcs.check.log |awk -F': ' '/Modify: /{print $2}'`
NOWDT2=`echo $NOWDT | awk -F+ '{print $1}'`
echo $NOWDT2
#
# do diff
# echo $(( ( $(date -ud '2003-08-02 17:24:33' +'%s') - $(date -ud '2003-04-21 22:55:02' +'%s') ) )) seconds
#echo $(( ( $(date -ud "$NOWDT2" +'%s') - $(date -ud "$LASTDT2" +'%s') ) ))
AGE=`echo $(( ( $(date -ud "$NOWDT2" +'%s') - $(date -ud "$LASTDT2" +'%s') ) ))`

# if diff > 65 seconds then must restart the printCreateFile.py process
#
if [ $AGE -gt "90" ] 
then
	echo "Create Age is too big"
        myprog2="/data/minder/python/script/printCreateFile.fdb.py"
       	pkill -f "python $myprog2"
	export USER="bdcs"
    	. /etc/minder/sh/MINDER.password
        myopts="db=minder host=$ISC_HOST user=$ISC_USER passwd=$ISC_PASSWD tmp=/data/tmp condwait=60"
        #runuser -l ${USER} -c "cd /data/tmp && python $myprog2 $myopts >printcreatefl.log  2>&1 & "
        su  ${USER} -c "cd /data/tmp && python $myprog2 $myopts >printcreatefl.log  2>&1 & "
fi
