:
#
# check cmdr is running
#
#
# 
#echo "path" $PATH
PATH=${PATH}:/opt/firebird/bin:/data/asset.rf/script
#echo "path" $PATH

date

RETVAL=0
RUNNING=`pgrep -u root -P 1 cmdr `
RETVAL=$?
if [ "$RETVAL" -eq 1 ]; then
	/etc/init.d/cmdr start
	echo "service cmdr started"
else
	let RETVAL=0
fi
RETVAL=$?
