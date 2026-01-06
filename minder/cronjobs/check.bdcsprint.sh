#!/bin/bash
#
# function to downshift
# usage: cmmd=$(down_shift $cmmd)
function down_shift {
#echo $1 | tr "[:upper:]" "[:lower:]"
echo $1 | tr '[A-Z]' '[a-z]'
} # end down_shift

# define function for check
checkrunning() {
	WKFN=$1
	WKFNLOG=$2
	# now down shift the WKFN
	startlog=$(down_shift $WKFN)
	#echo "startlog"$startlog
	# check process for WKFN is running OK and restart if not running
	echo "checking "${WKFN}
	#
	# get last date from /data/tmp/print${WKFNLOG}.minder.log
	LASTDT=`stat /data/tmp/print${WKFNLOG}.minder.log |awk -F': ' '/Modify: /{print $2}'`
	#echo $LASTDT
	LASTDT2=`echo $LASTDT | awk -F+ '{print $1}'`
	#echo $LASTDT2
	#
	# get current date
	touch /data/tmp/bdcs.check.log
	NOWDT=`stat /data/tmp/bdcs.check.log |awk -F': ' '/Modify: /{print $2}'`
	NOWDT2=`echo $NOWDT | awk -F+ '{print $1}'`
	echo $NOWDT2
	#
	# do diff
	AGE=`echo $(( ( $(date -ud "$NOWDT2" +'%s') - $(date -ud "$LASTDT2" +'%s') ) ))`
	
	# if diff > 65 seconds then must restart the print${WKFN}File.py process
	#
	if [ $AGE -gt "125" ] 
	then
		echo ${WKFN}" Age is too big "$NOWDT2" vs "$LASTDT2
		PYTHON="/usr/bin/python2"
	    	myprog="/data/minder/python/script/print${WKFN}File.fdb.py"
	       	#pkill -f "python $myprog"
	       	pkill -f "$PYTHON $myprog"
		export USER="bdcs"
	    	. /etc/minder/MINDER.password
	        myopts="db=minder host=$ISC_HOST user=$ISC_USER passwd=$ISC_PASSWD tmp=/data/tmp condwait=60 "
	        #su  ${USER} -c "cd /data/tmp && python $myprog $myopts >print${startlog}fl.log  2>&1 & "
		me=`id -u -n`
		if [ "$me" = "root" ]
		then
        		#runuser -l ${USER} -c "cd /data/tmp && python $myprog $myopts >print${startlog}f1.log 2>&1 & "
			#su bdcs -c "cd /data/tmp && python $myprog $myopts >>print${startlog}f1.log 2>&1 & "
			cd /data/tmp && $PYTHON $myprog $myopts >>print${startlog}f1.log 2>&1 & 
		else
        		#cd /data/tmp && python $myprog $myopts >print${startlog}f1.log 2>&1 & 
        		cd /data/tmp && $PYTHON $myprog $myopts >print${startlog}f1.log 2>&1 & 
		fi
	fi
	#
}
#########################################################################################################################
# check Send is running OK and restart if not running
. /etc/minder/backup.conf
export TZ
#
DOW=`date +"%w"`
export DOW
HOUR=`date +"%H"`
export HOUR

if [ "$HOUR" -gt 19 -o "$HOUR" -lt 6 ] 
then
	# do nothing
	echo "outside time for checkbdcsprint"
else
	checkrunning Send Send
	checkrunning Queue Queue
	checkrunning Create Write
fi
#
#
