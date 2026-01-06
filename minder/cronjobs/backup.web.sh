:
#
# clean up Print queues and process extension 
#
#
# 
PATH=${PATH}:/opt/firebird/bin:/data/minder/script

. /etc/minder/MINDER.password
# note added backup conf to hold days ages to hold before deletion
. /etc/minder/backup.conf

/etc/init.d/cmdr  stop 
/etc/init.d/bdcsprint stop

#
# remove old logged data
#
. /data/minder/script/oldlogs.sh


for printer_log in /data/minder/Printers/P*
do
  python /data/minder/python/script/savetoday.py ${printer_log}
  python /data/minder/python/script/savetodaysext.py ${printer_log} prn
  python /data/minder/python/script/savetodaysext.py ${printer_log} pdf
done

# Virtual Printer used for various processing e.g. exporting despatches to legacy ERP
# need to look in SYS_EQUIP
if [ -d "/data/minder/EV" ] 
then
  python /data/minder/python/script/savetodaysext.py /data/minder/EV dxt
  python /data/minder/python/script/savetodaysext.py /data/minder/EV pat
  python /data/minder/python/script/savetodaysext.py /data/minder/EV ext
  python /data/minder/python/script/savetodaysext.py /data/minder/EV exp
  python /data/minder/python/script/savetodaysext.py /data/minder/EV processed
fi

# handle other special virtual printers....
#TODO
# need to look in SYS_EQUIP

python /data/minder/python/script/savetodayslog.py /data/tmp
python /data/minder/python/script/savetodaysext.py /data/tmp prn
python /data/minder/python/script/savetodaysext.py /data/tmp sql
python /data/minder/python/script/savetodaysext.py /data/tmp pdf
python /data/minder/python/script/savetodaysext.py /data/tmp id
python /data/minder/python/script/savetodaysext.py /data/tmp file
python /data/minder/python/script/savetodaysext.py /data/tmp awbs
python /data/minder/python/script/savetodaysext.py /data/tmp repparms

python /data/minder/python/script/savetodayslog.py /data/logs
python /data/minder/python/script/savetodayslog.py /data/logs/httpd
> /data/tmp/import.log

#FTP Import processing
python /data/minder/python/script/savetodaysext.py /data/ftp/default/ftproot/in processed

#FTP Export processing - for Couriers and Legacy ERP
if [ -d "/data/ftp/default/ftproot/out" ] 
then
  python /data/minder/python/script/savetodaysext.py /data/ftp/default/ftproot/out sent
  python /data/minder/python/script/savetodaysext.py /data/ftp/default/ftproot/out send
  python /data/minder/python/script/savetodaysext.py /data/ftp/default/ftproot/out senx
  python /data/minder/python/script/savetodaysext.py /data/ftp/default/ftproot/out sendf
fi
if [ -d "/data/ftp/default/ftproot/import" ] 
then
  python /data/minder/python/script/savetodaysext.py /data/ftp/default/ftproot/import psv
  python /data/minder/python/script/savetodaysext.py /data/ftp/default/ftproot/import log
fi
if [ -d "/data/ftp/default/ftproot/import2" ] 
then
  python /data/minder/python/script/savetodaysext.py /data/ftp/default/ftproot/import2 psv
  python /data/minder/python/script/savetodaysext.py /data/ftp/default/ftproot/import2 log
  python /data/minder/python/script/savetodaysext.py /data/ftp/default/ftproot/import2 pxlsx
  python /data/minder/python/script/savetodaysext.py /data/ftp/default/ftproot/import2 CSV
fi

/etc/init.d/bdcsprint start
/etc/init.d/cmdr  start
#/sbin/service httpd restart

# For BlueScope  senddata.sh
> /data/tmp/sftpin.log
> /data/tmp/sftpout.log

chown bdcs /data/tmp/sftpin.log
chown bdcs /data/tmp/sftpout.log
find /data/tmp -name "????-??-??" -type d -exec chmod a+w '{}' \;
