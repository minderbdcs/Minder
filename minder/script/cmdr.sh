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
#[ ${NETWORKING} = "no" ] && exit 0

unset CMDRSERVERARGS
unset CMDRSERVERARG2
unset CMDRSERVERARG3
unset CMDRSERVERARG4
unset CMDRSERVERSTOP
unset CMDRSERVERSTART
unset CMDRSERVERSLEEPSECS
CMDRSERVERS=""
#[ -f /etc/sysconfig/cmdrservers ] && . /etc/sysconfig/cmdrservers
#[ -f /data/minder/script/cmdrservers ] && . /data/minder/script/cmdrservers
[ -f /etc/minder/cmdrservers ] && . /etc/minder/cmdrservers

source /etc/minder/MINDER.password

prog=$"CMDR server"
type python
#alias

start() {
    echo -n $"Starting $prog: "
    ulimit -S -c 0 >/dev/null 2>&1
    RETVAL=0
    #if [ ! -d /tmp/.X11-unix ]
    #then
    #    mkdir -m 1777 /tmp/.X11-unix || :
    #    restorecon /tmp/.X11-unix 2>/dev/null || :
    #fi
    NOSERV=1
    for display in ${CMDRSERVERS}
    do
        NOSERV=
        #echo -n "${display} "
        echo  "${display} "
	unset BASH_ENV ENV
	DISP="${display%%:*}"
	export USER="${display##*:}"
	export CMDRCMD="${CMDRSERVERARGS[${DISP}]}"
	export CMDRDIR="${CMDRSERVERARG2[${DISP}]}"
	export CMDRFILES="${CMDRSERVERARG3[${DISP}]}"
	export CMDREXT="${CMDRSERVERARG4[${DISP}]}"
	export CMDRSTOPTIME="${CMDRSERVERSTOP}"
	export CMDRSTARTTIME="${CMDRSERVERSTART}"
	export CMDRSLEEPSECS="${CMDRSERVERSLEEPSECS}"
	#echo  "$USER"
	echo  "${CMDRDIR}${CMDRFILES}"
	export CMDRLSOF="lsof | fgrep {}"
	#if cmdrldof returns 0 then file is open
	#if cmdrldof returns 1 then file is not open
	# so use not
	# but try for now file status changed at least 1 minute ago
	# 
	# look for file to cause event
	#
	echo " find ${CMDRDIR} -name \"${CMDRFILES}\" -type f -print  -cmin +0 -exec date \;  -exec ${CMDRCMD} \; -exec mv {} {}${CMDREXT} \;" 

	#find ${CMDRDIR} -name "${CMDRFILES}" -type f -print  -cmin +0   -exec ${CMDRCMD} \; -exec mv {} {}${CMDREXT} \;
	#find ${CMDRDIR} -maxdepth 1 -name "${CMDRFILES}" -type f -print  -cmin +0   -exec ${CMDRCMD} \; -exec mv {} {}${CMDREXT} \;
	#find ${CMDRDIR} -maxdepth 1 -name "${CMDRFILES}" -type f -print  -cmin +1   -exec ${CMDRCMD} \; -exec mv {} {}${CMDREXT} \;
	find ${CMDRDIR} -maxdepth 1 -name "${CMDRFILES}" -type f -print  -cmin +1   -exec ${CMDRCMD} \; -exec mv -f {} {}${CMDREXT} \;
        #runuser -l ${USER} -c "cd ~${USER} && ${CMDRUSERARGS}"
    done
    if test -n "$NOSERV"; then echo -n "no Tasks configured "; fi
    [ "$RETVAL" -eq 0 ] && success $"cmdrserver startup" || \
        failure $"cmdrserver start"
    echo
    #[ "$RETVAL" -eq 0 ] && touch /var/lock/subsys/cmdrserver
}


# See how we were called.
case "$1" in
  start)
 	rm -f /data/tmp/cmdr.stop
	start
	sleep  "${CMDRSERVERSLEEPSECS}"
	# get current time
	export CURRTIME=`date +"%H%M"`
    	# if time < stop time 
	while [  ! -e /data/tmp/cmdr.stop ] 
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

