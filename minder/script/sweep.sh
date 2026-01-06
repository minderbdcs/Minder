:
#
# sweep  the database and ancillary processing
#
#
# 
#echo "path" $PATH
PATH=${PATH}:/opt/firebird/bin:/data/asset.rf/script
#echo "path" $PATH



HAVEPROCESS=`ls /tmp/run.sweep 2>/dev/null | wc -l `
echo "haveprocess:" $HAVEPROCESS
date
#if [ "$HAVEPROCESS" -eq "1" ] 
if [ "$HAVEPROCESS" -eq "0" ] 
then
	echo "haveprocess:" $HAVEPROCESS " eq 0"

	HAVEMEM=`free -m | awk '/Mem/ {print $4}'`
	if [ "$HAVEMEM" -gt "30" ] 
	then
		echo "more than 30m available "  >> /tmp/sweep2.log
		cd /tmp

		date >  /tmp/run.sweep

		date >> /tmp/sweep2.log

		gfix -sweep -user sysdba -password masterkey 127.0.0.1:minder  2>>/tmp/sweep2.log

		echo "sweep completed"  >> /tmp/sweep2.log
		rm -f /tmp/run.sweep
	else
		echo "not enough free memory for sweep "  >> /tmp/sweep2.log
		echo "$HAVEMEM"  >> /tmp/sweep2.log
	fi
else
	echo "sweep already running "  >> /tmp/sweep2.log
fi
