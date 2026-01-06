set echo;
select cast(cast('NOW' as timestamp) as char(26)) from control;
/* EXECUTE  PROCEDURE RESET_ISSN_ORDERS ('OP TO AS NO STOCK'); */
select cnt from RESET_ISSN_ORDERS ('OP TO AS NO STOCK','');
select cast(cast('NOW' as timestamp) as char(26)) from control;
/* EXECUTE  PROCEDURE RESET_ISSN_ORDERS ('AS TO OP HAVE STOCK'); */
select cnt from  RESET_ISSN_ORDERS ('AS TO OP HAVE STOCK','');
select cast(cast('NOW' as timestamp) as char(26)) from control;
/* EXECUTE  PROCEDURE RESET_ISSN_ORDERS ('OP TO AS OVER PICK'); */
select cnt from RESET_ISSN_ORDERS ('OP TO AS OVER PICK','');
select cast(cast('NOW' as timestamp) as char(26)) from control;
select cnt from RESET_ISSN_ORDERS ('RECALC CHANNEL','');
select cast(cast('NOW' as timestamp) as char(26)) from control;
exit;
