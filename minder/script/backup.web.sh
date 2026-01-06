:
#
# backup the database and ancillary processing
#
#
# 
#echo "path" $PATH
PATH=${PATH}:/opt/firebird/bin:/data/minder/script
#echo "path" $PATH

/etc/init.d/cmdr  stop 
/etc/init.d/bdcsprint stop
#/etc/init.d/bdcssoap stop

oldlogs.sh 

for printerwd in /data/minder/Printers/P*
do
	python /data/minder/python/script/savetoday.py $printerwd
	python /data/minder/python/script/savetodaysext.py $printerwd prn
	python /data/minder/python/script/savetodaysext.py $printerwd pdf

done




python /data/minder/python/script/savetodayslog.py /tmp
python /data/minder/python/script/savetodaysext.py /tmp prn
python /data/minder/python/script/savetodaysext.py /tmp sql
python /data/minder/python/script/savetodayslog.py /data/tmp
python /data/minder/python/script/savetodaysext.py /data/tmp prn
python /data/minder/python/script/savetodaysext.py /data/tmp/minder queue
python /data/minder/python/script/savetodaysext.py /data/tmp/minder send
python /data/minder/python/script/savetodaysext.py /data/tmp/minder create



echo "" > /tmp/import.log
echo "" > /tmp/import_test.log


#python /data/minder/python/script/archivetrans.py 
python /data/minder/python/script/archivetrans4.py 

python /data/minder/python/script/savetodaysext.py /data/ftp/default/ftproot/in processed
python /data/minder/python/script/savetodaysext.py /data/ftp/default/ftproot/out sent
python /data/minder/python/script/savetodaysext.py /data/ftp/default/ftproot/out send
python /data/minder/python/script/savetodaysext.py /data/ftp/default/ftproot/out senx
python /data/minder/python/script/savetodaysext.py /data/ftp/default/ftproot/out sendf


/etc/init.d/httpd restart

/etc/init.d/bdcsprint start
#/etc/init.d/bdcssoap start
sleep 300
/etc/init.d/cmdr  start

# senddata.sh
touch /tmp/sftpin.log
touch /tmp/sftpout.log

