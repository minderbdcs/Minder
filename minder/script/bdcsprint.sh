#!/bin/bash
#
# chkconfig: - 91 35
# description: Starts and stops cmdrserver. \
#	       used to provide remote X administration services.

# Source function library.
. /etc/init.d/functions

# Source networking configuration.
. /etc/sysconfig/network

# Check that networking is up.
[ ${NETWORKING} = "no" ] && exit 0

echo $PATH
PATH=$PATH:/opt/firebird/bin
echo $PATH
unset CMDRSERVERARGS
unset CMDRSERVERSTOP
unset CMDRSERVERSTART
unset CMDRSERVERSLEEPSECS
CMDRSERVERS=""
[ -f /data/asset.rf/script/bdcsprintservers ] && . /data/asset.rf/script/bdcsprintservers

prog=$"BDCSPRINT server"

start() {
    echo -n $"Starting $prog: "
    ulimit -S -c 0 >/dev/null 2>&1
    RETVAL=0
    NOSERV=1
    HAVEPROCESS=0
    for display in ${CMDRSERVERS}
    do
        NOSERV=
        echo  "${display} "
	unset BASH_ENV ENV
	DISP="${display%%:*}"
	export CMDRCMD="${CMDRSERVERARGS[${DISP}]}"
	export CMDRLOG="${CMDRSERVERLOG[${DISP}]}"
	export CMDROPT="${CMDRSERVEROPT[${DISP}]}"
	export CMDRSTOPTIME="${CMDRSERVERSTOP}"
	export CMDRSTARTTIME="${CMDRSERVERSTART}"
	export CMDRSLEEPSECS="${CMDRSERVERSLEEPSECS}"
	#echo  "${CMDRDIR}${CMDRFILES}"
	#
	date
	echo " ${CMDRCMD} ${CMDROPT}  " 
	#HAVEPROCESS=`ps -ef | fgrep  "python ${CMDRCMD}" | wc -l `
    	HAVEPROCESS=0
	HAVEPROCESS=`ps -ef | fgrep  "python ${CMDRCMD} ${CMDROPT}" | wc -l `
	export HAVEPROCESS
	echo "haveprocess:" $HAVEPROCESS
	if [ "$HAVEPROCESS" -eq "0" ] 
	then
		echo "haveprocess:" $HAVEPROCESS " eq -1"
		cd /data/tmp && python ${CMDRCMD} ${CMDROPT} >${CMDRLOG} 2>&1 & 
	fi
	if [ "$HAVEPROCESS" -eq "1" ] 
	then
		echo "haveprocess:" $HAVEPROCESS " eq 0"
		cd /data/tmp && python ${CMDRCMD} ${CMDROPT} >${CMDRLOG} 2>&1 & 
	fi
	if [ "$HAVEPROCESS" -eq "2" ] 
	then
		echo "haveprocess:" $HAVEPROCESS " eq 1"
	fi
	if [ "$HAVEPROCESS" -gt "2" ] 
	then
		echo "haveprocess:" $HAVEPROCESS " gt 1"
	fi
    done
    if test -n "$NOSERV"; then echo -n "no displays configured "; fi
    [ "$RETVAL" -eq 0 ] && success $"bdcsprintserver startup" || \
        failure $"bdcsprintserver start"
    echo
    [ "$RETVAL" -eq 0 ] && touch /var/lock/subsys/bdcsprint
}


# See how we were called.
case "$1" in
  start)
 	rm -f /tmp/bdcsprint.stop
	start
	sleep  "${CMDRSERVERSLEEPSECS}"
	# get current time
	export CURRTIME=`date +"%H%M"`
    	# if time < stop time 
	while [  ! -e /tmp/bdcsprint.stop ] 
	do
    		# next run
		if [  "$CURRTIME" -le "$CMDRSERVERSTOP"  ] && [  "$CURRTIME" -ge "$CMDRSERVERSTART" ]
		then start
		fi
		sleep "${CMDRSERVERSLEEPSECS}"
		# get current time
		export CURRTIME=`date +"%H%M"`
	done
	;;
  *)
	echo $"Usage: $0 {start}"
	exit 1
esac

