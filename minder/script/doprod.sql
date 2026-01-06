select cast(cast("NOW" as date) as char(22)) from control;
execute procedure update_legacy_product;
select cast(cast("NOW" as date) as char(24)) from control;
