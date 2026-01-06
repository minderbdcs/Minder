#!/bin/bash
#
# import vic csvs 
#
#
# 
#echo "path" $PATH
PATH=${PATH}:/opt/firebird/bin:/data/minder/script
#echo "path" $PATH

#. /etc/minder/MINDER.password
. /etc/minder/MINDER.live.password

cd /data/tmp/import
#
# need to edit the SYS_EQUIP entry for NA to become OA
# need to edit the SSN_HIST entrys for device NA to become OA
# need to edit the TRANSACTIONS_ARCHIVE entrys for NA to become OA
#for i in SYS_EQUIP.csv SSN_HIST.csv TRANSACTIONS_ARCHIVE.csv
#do
#	echo $i
#	sed 's/"NA"/"OA"/' < $i > $i.new
#	mv $i $i.orig
#	mv $i.new $i
#done
#
#
for i in *csv
do
	echo $i
	RESULT=0
	python /data/minder/db/python/import/importfile.csv.fdb.py $i /data/tmp/$i.log db=$ISC_DB host=$ISC_HOST user=$ISC_USER passwd=$ISC_PASSWD > /data/tmp/f.import.$i.log 2>&1 
	RESULT=$?
	if [ "$RESULT" -eq "0" ] 
	then
		mv $i $i.psv
	fi
done
#
#
# numeric PACK_ID, PICK_DESPATCH and PICK_ITEM_DETAIL  DESPATCH_ID PACK_ID and PICK_DETAIL_ID
# proposed to add an fixed no to the pack_id's and despatch_ids in the three tables
#
# now do the import

# need to add to the locations SY OA
# have to get pack_id and pick_despatch to allow import using 0 record_id
#
isql -u $ISC_USER -p $ISC_PASSWD $ISC_HOST:$ISC_DB <<EOF
select 'start isql adjustments', cast(cast('NOW' as timestamp) as varchar(22)) from control;
update or insert into location(wh_id,locn_id,locn_name) values('SY','OA','OA Transit');
select 'done adding transit location for NA', cast(cast('NOW' as timestamp) as varchar(22)) from control;
commit;
/*
;set generator despatch_id_gen to 50020000;  
;set generator despatch_pack_id_gen to 50020000 ; 
;set generator pick_detail_gen to 50500000;  
*/
commit;
exit;
EOF



