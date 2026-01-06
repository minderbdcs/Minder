<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
include "../login.inc";
?>
<html>
 <head>
 </head>
 <body>
<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include "logme.php";




if (isset($_GET['message']))
{
	$message = $_GET['message'];
}
if (isset($_POST['message']))
{
	$message = $_GET['message'];
}
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<br>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

if (isset($_POST['matchorders']))
{
	$matchorders = $_POST['matchorders'];
}
if (isset($_GET['matchorders']))
{
	$matchorders = $_GET['matchorders'];
}
if (isset($matchorders))
{
	if ($matchorders <> "")
	{
		setBDCScookie($Link, $tran_device, "MATCHORDERS", $matchorders  );
	}
}

if (isset($_POST['selorder']))
{
	$selorder = $_POST['selorder'];
}
if (isset($_GET['selorder']))
{
	$selorder = $_GET['selorder'];
}
if (isset($selorder))
{
	if ($selorder <> "")
	{
		setBDCScookie($Link, $tran_device, "SELORDER", $selorder  );
	}
}

/*
===============================================================================================================

get a device to use - use the servers ip = 'MC' 
*/
	$wk_pick_device = 'MC';

/*
get a free block
*/
	$wk_query_free = "
select first 1 w1.wh_id,w1.locn_id from location w1 
left outer join issn i1 on w1.wh_id=i1.wh_id and w1.locn_id = i1.locn_id
where w1.locn_id starting 'B'
and w1.wh_id < 'XA'
and w1.wh_id > ''
and w1.moveable_locn = 'T'
and i1.locn_id is null
order by w1.wh_id,w1.locn_id desc";
	// since we are now going to DS use the first despatch location in wh
	$wk_query_free = "select first 1 location.wh_id, location.locn_id from location 
join control on control.record_id = 1
where location.wh_id = control.default_wh_id and location.move_stat = 'DS' and location.store_type='DS' and location.store_area='DS'
 order by location.locn_id ";

	if (!($Result = ibase_query($Link, $wk_query_free)))
	{
		echo("Unable to Get Free Block for Choice!<br>\n");
		//exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_block_wh_id = $Row[0];
		$wk_block_locn_id = $Row[1];
	}
	//release memory
	ibase_free_result($Result);

/*
update order to be DA is it was DX
*/
	$wk_message = "";
	$wk_query_order = "update pick_order set pick_status = 'DA' where pick_status='DX' and pick_order='" . $selorder . "'";
	if (!($Result = ibase_query($Link, $wk_query_order)))
	{
		echo("Unable to Update Order!<br>\n");
		echo $wk_query_order;
		echo("<br>\n");
		$wk_message = $wk_message .  "Unable to Update Order!";
		//exit();
	}
	//release memory
	ibase_free_result($Result);

/*
update lines to be  PL to Update Order that were DX or DC - change despatch location to be block
*/
	//$wk_query_line = "update pick_item set pick_line_status = 'PL',despatch_location='" . $wk_block_locn_id . "',device_id='" . $wk_pick_device . "' where pick_line_status in ('DX','DC','DS') and pick_order='" . $selorder . "'";
	//$wk_query_line = "update pick_item set pick_line_status = 'DS',despatch_location='" . $wk_block_locn_id . "',device_id='" . $wk_pick_device . "' where pick_line_status in ('DX','DC','DS') and pick_order='" . $selorder . "'";
	$wk_query_line = "update pick_item set pick_line_status = 'DS',despatch_location='" . $wk_block_locn_id . "',device_id='" . $wk_pick_device . "' where pick_line_status in ('DX','DC','DS','AS') and pick_order='" . $selorder . "'";
	if (!($Result = ibase_query($Link, $wk_query_line)))
	{
		echo("Unable to Update Line!<br>\n");
		echo $wk_query_line;
		echo("<br>\n");
		$wk_message = $wk_message .  " Unable to Update Line!";
		//exit();
	}
	//release memory
	ibase_free_result($Result);
	//11.03.16
	// check if have any DS status PIs have a qty picked
	// if not
	// update all pi's to make the picked_qty = pick_order_qty where picked_qty is null
	$wk_pi_qty_found = 0;
	$wk_query_pi_qty_exist = "select count(*) from pick_item where pick_line_status in ('DS') and picked_qty > 0 and pick_order='" . $selorder . "'";
	if (!($Result = ibase_query($Link, $wk_query_pi_qty_exist)))
	{
		echo("Unable to Get PI Qty Count!<br>\n");
		//exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_pi_qty_found = $Row[0];
	}
	//release memory
	ibase_free_result($Result);

	if ($wk_pi_qty_found == 0)
	{
		$wk_query_line_qty = "update pick_item set picked_qty = pick_order_qty  where pick_line_status in ('DS') and picked_qty is null and pick_order='" . $selorder . "'";
		if (!($Result = ibase_query($Link, $wk_query_line_qty)))
		{
			echo("Unable to Update PI Qtys!<br>\n");
			echo $wk_query_line_qty;
			echo("<br>\n");
			$wk_message = $wk_message .  " Unable to Update PI Qtys!";
			//exit();
		}
		//release memory
		ibase_free_result($Result);
	}

	$wk_pid_found = 0;
	$wk_pi_found = 0;
	$wk_query_pid_exist = "select count(*) from pick_item_detail where pick_detail_status in ('DS') and pick_order='" . $selorder . "'";
	if (!($Result = ibase_query($Link, $wk_query_pid_exist)))
	{
		echo("Unable to Get PID Count!<br>\n");
		//exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_pid_found = $Row[0];
	}
	//release memory
	ibase_free_result($Result);

/*
update pids  to be  PL that were DX or DC - change device, despatch_location
*/
	$wk_query_pid = "update pick_item_detail set pick_detail_status = 'DS',despatch_location='" . $wk_block_locn_id . "',device_id='" . $wk_pick_device . "',despatch_id=null,pack_id=null where pick_detail_status in ('DX','DC','DS') and pick_order='" . $selorder . "'";
	if (!($Result = ibase_query($Link, $wk_query_pid)))
	{
		echo("Unable to Update PID!<br>\n");
		echo $wk_query_pid;
		echo("<br>\n");
		$wk_message = $wk_message .  " Unable to Update PID!";
		//exit();
	}
	//release memory
	ibase_free_result($Result);

	//10.03.16
	// check if have any DS status PIDs
	// if not
	// add pids from pick_items where the SSN_ID is populated

	$wk_pid_found = 0;
	$wk_pi_found = 0;
	$wk_query_pid_exist = "select count(*) from pick_item_detail where pick_detail_status in ('DS') and pick_order='" . $selorder . "'";
	if (!($Result = ibase_query($Link, $wk_query_pid_exist)))
	{
		echo("Unable to Get PID Count!<br>\n");
		//exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_pid_found = $Row[0];
	}
	//release memory
	ibase_free_result($Result);
	$wk_query_pi_exist = "select count(*) from pick_item where pick_line_status in ('DS') and pick_order='" . $selorder . "'";
	if (!($Result = ibase_query($Link, $wk_query_pi_exist)))
	{
		echo("Unable to Get PI Count!<br>\n");
		//exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_pi_found = $Row[0];
	}
	//release memory
	ibase_free_result($Result);

	if ($wk_pi_found > 0)
	{
		if ($wk_pid_found == 0)
		{
			$wk_query_pid_add = "
insert into pick_item_detail( pick_label_no, ssn_id, qty_picked, device_id, user_id, create_date, despatch_location, pick_detail_status)
select pick_item.pick_label_no, pick_item.ssn_id, pick_item.picked_qty, '" . $wk_pick_device . "', 'BDCS', 'NOW', '" . $wk_block_locn_id . "', 'DS'
from pick_item 
where pick_item.pick_order = '" . $selorder . "'
and pick_item.pick_line_status='DS'
and pick_item.ssn_id is not null ";
			if (!($Result = ibase_query($Link, $wk_query_pid_add)))
			{
				echo("Unable to Add PIDs!<br>\n");
				echo $wk_query_pid_add;
				echo("<br>\n");
				$wk_message = $wk_message .  " Unable to Add PIDs!";
				//exit();
			}
			//release memory
			ibase_free_result($Result);


		}
	}

/*
check issn's on device  so that qty > 0 and status and wh_id and locn for block
===============================================================================================================
*/
	// 23/03/16
	// if issn dosn't exist but is in issn_dx then copy it to issn table
	// get issns not found
	$wk_query_issn3 = " (select pid.ssn_id from pick_item_detail pid where pid.pick_order='" . $selorder . "' and (not exists(select i3.ssn_id from issn i3 where i3.ssn_id = pid.ssn_id) ) )  ";
/* ISSN fields */
	$wk_query_issn_fields = "
SSN_ID, ORIGINAL_SSN, WH_ID, LOCN_ID,PREV_LOCN_ID, CURRENT_QTY, PREV_QTY, PREV_DATE, ISSN_STATUS, STATUS_CODE,
CREATE_DATE, USER_ID, PROD_ID, INTO_DATE, PREV_WH_ID, PREV_PREV_LOCN_ID, PREV_PREV_WH_ID, AUDITED, PICK_ORDER, KITTED, 
LABEL_DATE, ORIGINAL_QTY, AUDIT_DATE, COMPANY_ID, DIVISION_ID, SERIAL_NUMBER, OTHER1, OTHER2, DESPATCHED_DATE, LAST_UPDATE_DATE, 
PREV_PROD_ID, PREV_PREV_PROD_ID, PROD_ID_UPDATE, PREV_PROD_ID_UPDATE, PREV_PREV_PROD_ID_UPDATE, OTHER3, PACK_ID, DESPATCH_ID,
ISSN_PACKAGE_TYPE, ISSN_PREV_PACKAGE_TYPE, PREV_PICK_ORDER, PREV_PREV_PICK_ORDER, PREV_PACK_ID, PREV_PREV_PACK_ID, PREV_DESPATCH_ID, PREV_PREV_DESPATCH_ID, 
PICKED_QTY, ISSN_DESCRIPTION, OTHER4, ORIGINAL_CREATE_DATE, PREV_ISSN_STATUS, PREV_PREV_ISSN_STATUS  ";
	$wk_query_issn_not_exist = "select count(*) from  " . $wk_query_issn3;
	// insert from dx table for missing
	$wk_query_issn_add = "insert into issn (" . $wk_query_issn_fields . ") select " . $wk_query_issn_fields . " from issn_dx where issn_dx.ssn_id in " . $wk_query_issn3;
	$wk_ii_found = 0;
	if (!($Result = ibase_query($Link, $wk_query_issn_not_exist)))
	{
		echo("Unable to Get Missing ISSN Count!<br>\n");
		//exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_ii_found = $Row[0];
	}
	//release memory
	ibase_free_result($Result);
	if ($wk_ii_found > 0)
	{
		if (!($Result = ibase_query($Link, $wk_query_issn_add)))
		{
			echo("Unable to Add Missing ISSNs!<br>\n");
			echo $wk_query_issn_add;
			echo("<br>\n");
			$wk_message = $wk_message .  " Unable to Add Missing ISSNs!";
			//exit();
		}
		//release memory
		ibase_free_result($Result);
	}

	//$wk_query_issn = "update issn set issn_status = 'PL',wh_id='" . $wk_block_wh_id . "',locn_id='" . $wk_block_locn_id . "',current_qty = case when issn.current_qty = 0 then issn.prev_qty else issn.current_qty end  where issn.ssn_id in (select pid.ssn_id from pick_detail_status pid where pid.pick_order='" . $selorder . "')";
	$wk_query_issn1 = "update issn set issn_status = 'DS',wh_id='" . $wk_block_wh_id . "',locn_id='" . $wk_block_locn_id . "'  where issn.ssn_id in (select pid.ssn_id from pick_item_detail pid where pid.pick_order='" . $selorder . "') and issn.current_qty > 0 ";
	//$wk_query_issn2 = "update issn set issn_status = 'DS',wh_id='" . $wk_block_wh_id . "',locn_id='" . $wk_block_locn_id . "',current_qty = issn.prev_qty  where issn.ssn_id in (select pid.ssn_id from pick_item_detail pid where pid.pick_order='" . $selorder . "') and issn.current_qty = 0";
	$wk_query_issn2 = "update issn set issn_status = 'DS',wh_id='" . $wk_block_wh_id . "',locn_id='" . $wk_block_locn_id . "',current_qty = coalesce(issn.prev_qty,1)  where issn.ssn_id in (select pid.ssn_id from pick_item_detail pid where pid.pick_order='" . $selorder . "') and coalesce(issn.current_qty,0) = 0";
	if (!($Result = ibase_query($Link, $wk_query_issn1)))
	{
		echo("Unable to Update ISSN 1!<br>\n");
		echo $wk_query_issn1;
		echo("<br>\n");
		$wk_message = $wk_message .  " Unable to Update ISSN 1!";
		//exit();
	}
	//release memory
	ibase_free_result($Result);
	if (!($Result = ibase_query($Link, $wk_query_issn2)))
	{
		echo("Unable to Update ISSN 2!<br>\n");
		echo $wk_query_issn2;
		echo("<br>\n");
		$wk_message = $wk_message .  " Unable to Update ISSN 2!";
		//exit();
	}
	//release memory
	ibase_free_result($Result);
	// ok now have an issn is ds and has a qty
	// need to update any pids for the issn with a null qty_picked to use the issns current_qty
	$wk_query_pid4  = "update pick_item_detail p4 set p4.qty_picked = (select i4.current_qty from issn i4 where i4.ssn_id = p4.ssn_id)  where  p4.pick_order='" . $selorder . "' and coalesce(p4.qty_picked,0) = 0 and p4.pick_detail_status = 'DS' and exists (select i5.ssn_id  from issn i5 where i5.ssn_id = p4.ssn_id)";
	if (!($Result = ibase_query($Link, $wk_query_pid4)))
	{
		echo("Unable to Update PID 4!<br>\n");
		echo $wk_query_pid4;
		echo("<br>\n");
		$wk_message = $wk_message .  " Unable to Update PID4!";
		//exit();
	}
	//release memory
	ibase_free_result($Result);


//echo($Query);




	//commit
	ibase_commit($dbTran);
	
	if ($wk_message == "")
	{
		$wk_message = "Successfully Updated Order " .$selorder . " Block " . $wk_block_locn_id;
	}
	//header("Location: getDXtoPL.php");
	echo("<form action=\"getDXtoPL.php\" method=\"post\" name=getperson> \n");
	echo("<INPUT type=\"hidden\" name=\"message\" value=\"$wk_message\"> ");  
	echo("</form>\n");

?>
<script type="text/javascript">
<?php
	if (substr($wk_message,0, 26) == "Successfully Updated Order")
	{
		echo("document.getperson.submit();\n");
	}
?>
</script>
</body>
</html>
