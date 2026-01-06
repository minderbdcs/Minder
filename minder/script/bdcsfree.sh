:
#
# check free memory
#
#
# 
#echo "path" $PATH
PATH=${PATH}:/opt/firebird/bin:/data/asset.rf/script
#echo "path" $PATH



HAVEMEM=`free -m | awk '/Mem/ {print $4}'`
echo "havemem:" $HAVEMEM
date
if [ "$HAVEMEM" -lt "40" ] 
then
	echo "havemem:" $HAVEMEM " less than 40m"

	cd /tmp

	date >> /tmp/bdcsfree2.log

	#/etc/init.d/bdcsprint restart 
	#/etc/init.d/tomcat55 restart 
	#/etc/init.d/httpd    restart 
else
	echo "more than 40m available "  
fi
