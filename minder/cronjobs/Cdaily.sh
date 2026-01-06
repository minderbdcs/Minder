:
#
# backup the database and ancillary processing
#
#
# 
#echo "path" $PATH
PATH=${PATH}:/opt/firebird/bin:/data/minder/script
#echo "path" $PATH


cd /data/tmp
. /etc/minder/MINDER.password
isql -u $ISC_USER -p $ISC_PASSWD  $ISC_HOST:$ISC_DB <<EOF
delete from pick_item_line_no where pick_order in (select p1.pick_order from pick_item_line_no p1 left outer join pick_    order p2 on p1.pick_order=p2.pick_order where p2.pick_order is null);
update issn set prev_wh_id=wh_id,prev_locn_id=locn_id,wh_id='XX',locn_id='00000000' where wh_id<'XA' and current_qty=0     ;
update issn set prev_wh_id=wh_id,prev_locn_id=locn_id,wh_id='XX',locn_id='00000000',current_qty=0 where wh_id<'XA' and     current_qty is null ;
update issn set prev_wh_id=wh_id,prev_locn_id=locn_id,wh_id='XX',locn_id='00000000' where wh_id is null  ;
update pick_item set device_id='C1' where device_id <>'C1' and pick_line_status='DS' ;
update pick_item_detail set device_id='C1' where device_id<>'C1'  and pick_detail_status='DS' ;
/* logout devices logged in for more than four hours */
/*
update sys_equip set  current_logged_on = null, current_person = null where current_logged_on < 'TODAY' ;
update sys_equip set  current_logged_on = null, current_person = null where current_logged_on < dateadd(hour,-4,cast('NOW' as timestamp)) ;
*/
update sys_equip set  current_logged_on = null, current_person = null where coalesce((select max(t1.trn_date) from transactions_archive t1 where t1.device_id = sys_equip.device_id and t1.trn_date > 'TODAY'),'TODAY') < dateadd(hour,-4,cast('NOW' as timestamp)) ;

update sys_user  set  login_date = null, device_id = null where not exists (select se.device_id from sys_equip se where     se.current_person = sys_user.user_id and se.device_type in ('HH','PC') );
exit;
EOF


#/etc/init.d/bdcsprint restart
#/etc/init.d/cmdr restart   
#/etc/init.d/httpd restart
