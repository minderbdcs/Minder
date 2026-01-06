#
echo " start"
date
# save the current db
gbak -b -v -user sysdba -password masterkey 127.0.0.1:minder /data/asset.rf/backup/last.minder.gbk 2> /data/asset.rf/backup/mdr.log
echo " after gbak"
date
# export old files
cd /data/ftp/default/ftproot/export
rm -f *
cd /data/ftp/default/ftproot/import
rm -f *
echo " export the datasets"
date
python /data/asset.rf/python/export/export.old.csv.py
#python /data/asset.rf/python/export/export.old.master.csv.py
#
echo " copy files to import"
date
cp /data/ftp/default/ftproot/export/*csv /data/ftp/default/ftproot/import
#
# clear the datasets
echo " clear the database"
date
isql-fb -u sysdba -p masterkey 127.0.0.1:minder <<EOF
alter trigger stop_delete_pick_item inactive;
alter trigger save_pick_item inactive;
alter trigger tg_add_pick_item_line_no inactive;
alter trigger save_pick_order inactive;
alter trigger save_grn inactive;
alter trigger save_grn_order inactive;
alter trigger update_pick_item_time  inactive;
alter trigger add_pick_item_time  inactive;
alter trigger update_pick_order_time  inactive;
alter trigger add_pick_order_time  inactive;
/* ssn */
alter trigger insert_ssn_po_price   inactive;
alter trigger insert_ssn_status     inactive;
alter trigger add_ssn_time          inactive;
alter trigger update_ssn_ssnstatus  inactive;
alter trigger update_ssn_location   inactive;
alter trigger ssn_model             inactive;
alter trigger update_ssn_ssndescription   inactive;
alter trigger update_ssn_time        inactive;
/* issn */
alter trigger insert_issn_status     inactive;
alter trigger add_issn_replenish  inactive;
alter trigger add_issn_time          inactive;
alter trigger update_issn_location   inactive;
alter trigger update_issn_replenish  inactive;
alter trigger update_issn_replenish_from  inactive;
alter trigger update_issn_time       inactive;
alter trigger update_issn_ssnstatus  inactive;
/* pick item */
alter trigger add_wip_item_ordering  inactive;
alter trigger update_wip_item_ordering  inactive;
/* pick order */
alter trigger add_wip_order_ordering  inactive;
alter trigger update_wip_order_ordering  inactive;
/* */
commit;
delete from issn;
commit;
delete from pick_order;
commit;
delete from pick_item;
commit;
delete from pick_item_detail;
commit;
delete from pick_item_line_no;
commit;
delete from pick_despatch;
commit;
delete from pack_id;
commit;
delete from ssn_hist;
commit;
delete from ssn;
commit;
delete from grn;
commit;
delete from grn_order;
commit;
delete from grn_cancel;
commit;
delete from grn_order_cancel;
commit;
delete from ssn_test;
commit;
delete from ssn_test_results;
commit;
delete from pick_item_cancel;
commit;
delete from transactions_archive;
commit;
delete from product_cond_status;
commit;
delete from product_condition;
commit;
exit;
EOF
# import the files
echo " import the files"
date
cd /data/ftp/default/ftproot/import
for i in `ls *csv`
do
	echo $i
	date
	python /data/asset.rf/python/import/importfile.csv2.py $i /tmp/import.$i.log
done
# reset triggers 
echo " reset the triggers"
date
isql-fb -u sysdba -p masterkey 127.0.0.1:minder <<EOF
alter trigger stop_delete_pick_item active;
alter trigger save_pick_item active;
alter trigger tg_add_pick_item_line_no active;
alter trigger save_grn active;
alter trigger save_grn_order active;
commit;
exit;
EOF
# update issn for company 
echo " set issn company"
date
# update issn set company_id = (select company_id from control) where company_id = '';
isql-fb -u sysdba -p masterkey 127.0.0.1:minder <<EOF
update issn set company_id = (select company_id from control) where company_id = '';
update issn set prod_id = null where prod_id = ''  ;
update issn set company_id = 'AITS' where prod_id is not null ;
commit;
exit;
EOF
# update ssn company
echo " set ssn company"
date
isql-fb -u sysdba -p masterkey 127.0.0.1:minder <<EOF
update ssn set company_id = (select company_id from control) where company_id = '';
update ssn set prod_id = null where prod_id = ''  ;
update ssn set company_id = 'AITS' where prod_id is not null ;
update ssn set ssn_type = null where ssn_type = ''  ;
update ssn set label_date = null where label_date < '1901-01-01';
update ssn set create_date = label_date ;
update ssn set created_by='imported' where created_by = '' ;
commit;
exit;
EOF
# update pick_order company
echo " set pick order company "
date
isql-fb -u sysdba -p masterkey 127.0.0.1:minder <<EOF
update pick_order  set company_id = (select company_id from control) where company_id = '';
update pick_order  set company_id = (select company_id from control) where company_id is null;
commit;
exit;
EOF
# update pick_order wh
echo " update pick_order wh"
date
isql-fb -u sysdba -p masterkey 127.0.0.1:minder <<EOF
update pick_order  set wh_id   = (select default_wh_id  from control) where wh_id is null ;
update pick_order  set wh_id   = (select default_wh_id  from control) where wh_id = '' ;
commit;
update pick_item  set prod_id   = null where prod_id = '' ;
update pick_item  set ssn_id   = null where ssn_id = '' ;
commit;
update pick_order  set company_id = 'AITS' where pick_order in (
 select p1.pick_order from pick_order p1 where  exists(select  first 1  p2.prod_id from pick_item p2 where p2.pick_order=p1.pick_order and p2.prod_id is not null) 
 and (not  exists(select  first 1  p3.ssn_id from pick_item p3 where p3.pick_order=p1.pick_order and p3.ssn_id is not null) ) 
)
 ;
commit;
exit;
EOF
# reset triggers 
echo " reset the triggers"
date
isql-fb -u sysdba -p masterkey 127.0.0.1:minder <<EOF
alter trigger update_pick_item_time  active;
alter trigger add_pick_item_time  active;
alter trigger update_pick_order_time  active;
alter trigger add_pick_order_time  active;
commit;
/* ssn */
alter trigger insert_ssn_po_price   active;
alter trigger insert_ssn_status     active;
alter trigger add_ssn_time          active;
alter trigger update_ssn_ssnstatus  active;
alter trigger update_ssn_location   active;
alter trigger ssn_model             active;
alter trigger update_ssn_ssndescription   active;
alter trigger update_ssn_time       active;
/* issn */
alter trigger insert_issn_status     active;
alter trigger add_issn_replenish  active;
alter trigger add_issn_time          active;
alter trigger update_issn_location   active;
alter trigger update_issn_replenish  active;
alter trigger update_issn_replenish_from  active;
alter trigger update_issn_time       active;
alter trigger update_issn_ssnstatus  active;
/* pick item */
alter trigger add_wip_item_ordering  active;
alter trigger update_wip_item_ordering active;
/* pick order */
alter trigger add_wip_order_ordering  active;
alter trigger update_wip_order_ordering active;
exit;
EOF
# reload the current db
gbak -b -v -user sysdba -password masterkey 127.0.0.1:minder /data/asset.rf/backup/last.minder2.gbk 2> /data/asset.rf/backup/mdr2.log
#
# must ensure that others are not attached to db
#
/etc/init.d/httpd stop
/etc/init.d/tomcat55 stop
/etc/init.d/cmdr stop
/etc/init.d/bdcsprint stop
#/etc/init.d/bdcssoap stop
#
gbak -r -REP -v -user sysdba -password masterkey  /data/asset.rf/backup/last.minder2.gbk 127.0.0.1:minder 2> /data/asset.rf/backup/mdr3.log
gbak -r -REP -v -user sysdba -password masterkey  /data/asset.rf/backup/last.minder2.gbk 127.0.0.1:test   2> /data/asset.rf/backup/mdr4.log
echo " finished"
date
