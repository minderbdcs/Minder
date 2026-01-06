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
	AGE=`expr $(date +%s) - $(date +%s -r /data/tmp/print${WKFNLOG}.minder.log)`
	
	# if diff > 70 seconds then must restart the print${WKFN}File.py process
	# since in startup use wait of 60 seconds
	#
	if [ $AGE -gt "40" ] 
	then
		echo ${WKFN}" Age is too big "$NOWDT2" vs "$LASTDT2
	    	myprog="/data/minder/python/script/print${WKFN}File.fdb.py"
	       	pkill -f "python $myprog"
		export USER="bdcs"
	    	. /etc/minder/sh/MINDER.password
	        myopts="db=minder host=$ISC_HOST user=$ISC_USER passwd=$ISC_PASSWD tmp=/data/tmp condwait=30"
	        su  ${USER} -c "cd /data/tmp && python $myprog $myopts >print${startlog}fl.log  2>&1 & "
	fi
	#
}
##############################################################################################################
# check Send is running OK and restart if not running
checkrunning Send Send
checkrunning Queue Queue
checkrunning Create Write
#
#
