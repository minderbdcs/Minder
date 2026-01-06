#
# description: Starts and stops bdcsprint. \
#	       used to provide label printing to known printers.


PATH=$PATH:/opt/firebird/bin

prog=$"BDCS Print server"

start() {
    echo -n $"Starting $prog: "
    ulimit -S -c 0 >/dev/null 2>&1
    RETVAL=0
    NOSERV=1
        myprog="/data/asset.rf/python/script/printSendFile.py"
        myopts="db=minder"
        cd /tmp && python $myprog $myopts >printsendfl.log  2>&1 & 
        myprog2="/data/asset.rf/python/script/printCreateFile.py"
        cd /tmp && python $myprog2 $myopts >printcreatefl.log  2>&1 & 
}

stop() {
    echo -n $"Shutting down $prog: "
    isql -u sysdba -p masterkey minder << EOF
execute procedure PRN_REQUEST_CP_STOP;
commit;
exit;
EOF
    isql -u sysdba -p masterkey minder << EOF
execute procedure PRN_REQUEST_NP_STOP;
commit;
exit;
EOF
    rm -f /var/lock/subsys/bdcsprint
}

# See how we were called.
case "$1" in
  start)
	start
	;;
  stop)
	stop
	;;
  restart|reload)
	stop
	sleep 3
	start
	;;
  status)
	;;
  *)
	echo $"Usage: $0 {start|stop|restart|status}"
	exit 1
esac

