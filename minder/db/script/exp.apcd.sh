:
#
# export apcd vic 
#
#
# 
echo "start export"
#echo "path" $PATH
PATH=${PATH}:/opt/firebird/bin:/data/minder/script
#echo "path" $PATH

. /etc/minder/MINDER.vic.password
#echo "start isql"
#isql -u $ISC_USER -p $ISC_PASSWD $ISC_HOST:$ISC_DB <<EOF
#update company set company_id='APCDVIC';
#select 'done company', cast(cast('NOW' as timestamp) as varchar(22)) from control;
#commit;
#update ssn set company_id='APCDVIC' where company_id is null;
#select 'done ssn null company', cast(cast('NOW' as timestamp) as varchar(22)) from control;
#update ssn set company_id='APCDVIC' where company_id = '';
#select 'done ssn empty company', cast(cast('NOW' as timestamp) as varchar(22)) from control;
#commit;
#update issn set company_id='APCDVIC';
#select 'done issn empty company', cast(cast('NOW' as timestamp) as varchar(22)) from control;
#commit;
#update pick_order set company_id='APCDVIC';
#select 'done pick_order empty company', cast(cast('NOW' as timestamp) as varchar(22)) from control;
#commit;
#update pack_id set despatch_id = despatch_id + 80000, pack_id = pack_id + 200000 ;
#select 'done pack_id records', cast(cast('NOW' as timestamp) as varchar(22)) from control;
#commit;
#update pick_despatch set despatch_id = despatch_id + 80000  ;
#select 'done pick_despatch records', cast(cast('NOW' as timestamp) as varchar(22)) from control;
#commit;
#update pick_item_detail set despatch_id = despatch_id + 80000, pick_detail_id = pick_detail_id + 900000  ;
#select 'done pick_item_detail records', cast(cast('NOW' as timestamp) as varchar(22)) from control;
#commit;
#exit;
#EOF

python /data/minder/db/python/export/f.client.db.csv.fdb.py db=$ISC_DB host=$ISC_HOST user=$ISC_USER passwd=$ISC_PASSWD > /data/tmp/f.export.log 2>&1 
cd /data/tmp/export
# transactions becomes transactions archive
mv TRANSACTIONS.csv TRANSACTIONS_ARCHIVE.csv
# no GENERIC use extension nsv
mv GENERIC.csv GENERIC.nsv
#
#
# need to change the company from "ACS" to "APCDVIC"
# note ISSN SSN GRN PICK_ORDER
#
# need to add to the locations SY OA
#
# numeric PACK_ID, PICK_DESPATCH and PICK_ITEM_DETAIL  DESPATCH_ID PACK_ID and PICK_DETAIL_ID
# proposed to add an fixed no to the pack_id's and despatch_ids in the three tables
# not required for APCDMEL or APCD
#
# now do the import
cp -f *.csv /data/tmp/import




