#!/bin/bash
 
# function to downshift
# usage: cmmd=$(down_shift $cmmd)
function down_shift {
#echo $1 | tr "[:upper:]" "[:lower:]"
echo $1 | tr '[A-Z]' '[a-z]'
} # end down_shift

start() {
	WKFN=$1
	# now down shift the WKFN
	startlog=$(down_shift $WKFN)
	. /etc/minder/sh/MINDER.password
	RETVAL=0
	export USER="bdcs"
        myprog="/data/minder/python/script/print${WKFN}File.fdb.py"
        myopts="db=minder host=$ISC_HOST user=$ISC_USER passwd=$ISC_PASSWD tmp=/data/tmp condwait=30 condlimit=150"
	su  ${USER} -c "cd /data/tmp && python $myprog $myopts >print${startlog}fl.log  2>&1 "
        RETVAL=$?
}

#==========================================
# 
# passed the process to run Send Queue or Create
# if within the allowed time range then loop starting the process
# otherwise sleep
#
. /etc/minder/bdcsloop
# get current time
export CURRTIME=`date +"%H%M"`
# if time < stop time 
while [  ! -e /tmp/bdcsloop.stop ] 
do
	# next run
	if [  "$CURRTIME" -le "$BDCSLOOPSERVERSTOP"  ] && [  "$CURRTIME" -ge "$BDCSLOOPSERVERSTART" ]
	then start $1
	else sleep "${BDCSLOOPSERVERSLEEPSECS}"
	fi
	# get current time
	export CURRTIME=`date +"%H%M"`
done

