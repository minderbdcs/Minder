select cast(cast("NOW" as date) as char(22)) from control;
execute procedure prn_request_archive;
select cast(cast("NOW" as timestamp) as char(22)) from control;
/*
update purchase_order_line set po_line_status='CN' where po_line_status in ('OP','CF') and exists(select purchase_order from purchase_order where purchase_order.purchase_order=purchase_order_line.purchase_order and purchase_order.po_status in ('CN','CL'));
select cast(cast("NOW" as timestamp) as char(22)) from control;
update purchase_order_line set po_line_status='CN' where po_line_status in ('OP','CF') and not exists(select purchase_order from purchase_order where purchase_order.purchase_order=purchase_order_line.purchase_order );
select cast(cast("NOW" as timestamp) as char(22)) from control;
*/
exit;

