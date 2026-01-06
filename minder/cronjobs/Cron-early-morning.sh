:
#echo "path" $PATH
PATH=${PATH}:/opt/firebird/bin:/data/minder/script
#echo "path" $PATH


cd /data/tmp


/etc/init.d/bdcsprint restart
/etc/init.d/cmdr restart   
#/etc/init.d/httpd restart
#/usr/sbin/service httpd restart
# if we use systemctl for services rather then sys5
if [ -x /etc/init.d/httpd ]
then
    /etc/init.d/httpd restart
else
    systemctl restart httpd
    # if we use php-fpm
    systemctl status php-fpm
    result=$?
    if [ $result -eq 0 ]
    then
        systemctl restart php-fpm
    else
        # so try php56-php-fpm
        systemctl status php56-php-fpm
        result=$?
        if [ $result -eq 0 ]
        then
            systemctl restart php56-php-fpm
        fi
    fi
fi
#
# now run the timezone update
#
. /etc/minder/MINDER.password
python /data/minder/python/script/WHTimezone.py tmp=/data/tmp db=$ISC_DB host=$ISC_HOST user=$ISC_USER passwd=$ISC_PASSWD 


