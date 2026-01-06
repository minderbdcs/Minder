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



python /data/minder/python/script/savetoday.py /data/asset.rf/PA
python /data/minder/python/script/savetodaysext.py /data/asset.rf/PA prn
python /data/minder/python/script/savetoday.py /data/asset.rf/PB
python /data/minder/python/script/savetodaysext.py /data/asset.rf/PB prn
python /data/minder/python/script/savetoday.py /data/asset.rf/PC
python /data/minder/python/script/savetodaysext.py /data/asset.rf/PC prn
python /data/minder/python/script/savetoday.py /data/asset.rf/PD
python /data/minder/python/script/savetoday.py /data/asset.rf/PE
python /data/minder/python/script/savetodaysext.py /data/asset.rf/PE prn
python /data/minder/python/script/savetoday.py /data/asset.rf/PG
python /data/minder/python/script/savetoday.py /data/asset.rf/PGTEST

python /data/minder/python/script/savetodaysext.py /data/asset.rf/PG dxt
python /data/minder/python/script/savetodaysext.py /data/asset.rf/PG pat
python /data/minder/python/script/savetodaysext.py /data/asset.rf/PG ext
python /data/minder/python/script/savetodaysext.py /data/asset.rf/PG exp

python /data/minder/python/script/savetoday.py /data/asset.rf/PH

python /data/minder/python/script/savetodaysext.py /data/asset.rf/PH prn
python /data/minder/python/script/savetodaysext.py /data/asset.rf/PH ext

python /data/minder/python/script/savetoday.py /data/asset.rf/PI

python /data/minder/python/script/savetodaysext.py /data/asset.rf/PI prn
python /data/minder/python/script/savetodaysext.py /data/asset.rf/PI ext
python /data/minder/python/script/savetodaysext.py /data/asset.rf/PI csv

python /data/minder/python/script/savetoday.py /data/asset.rf/PJ

python /data/minder/python/script/savetodaysext.py /data/asset.rf/PJ csv

python /data/minder/python/script/savetoday.py /data/asset.rf/PL
python /data/minder/python/script/savetoday.py /data/asset.rf/PR
python /data/minder/python/script/savetoday.py /data/asset.rf/PS

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
python /data/minder/python/script/savetodaysext.py /data/ftp/default/ftproot/in_test processed
python /data/minder/python/script/savetodaysext.py /data/ftp/default/ftproot/in_test Processed
python /data/minder/python/script/savetodaysext.py /data/ftp/default/ftproot/out_test sent
python /data/minder/python/script/savetodaysext.py /data/ftp/default/ftproot/out_test send
python /data/minder/python/script/savetodaysext.py /data/ftp/default/ftproot/out_test senx
python /data/minder/python/script/savetodaysext.py /data/ftp/default/ftproot/out_test sendf

python /data/minder/python/script/savetodaysext.py /data/asset.rf/PG processed
python /data/minder/python/script/savetodaysext.py /data/asset.rf/PGTEST processed

/etc/init.d/httpd restart

/etc/init.d/bdcsprint start
#/etc/init.d/bdcssoap start
sleep 300
/etc/init.d/cmdr  start

# senddata.sh
touch /tmp/sftpin.log
touch /tmp/sftpout.log

