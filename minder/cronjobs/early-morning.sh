:
#echo "path" $PATH
PATH=${PATH}:/opt/firebird/bin:/data/minder/script
#echo "path" $PATH


cd /data/tmp


/etc/init.d/bdcsprint restart
/etc/init.d/cmdr restart   
/etc/init.d/httpd restart
