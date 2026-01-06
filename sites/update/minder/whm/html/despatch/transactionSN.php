<?php
session_start();

require_once('DB.php');
require('db_access.php');
require_once('transaction.php');

//include "checkdata.php";
//require_once "checkdata.php";

if (isset($_COOKIE['LoginUser']))
{
	$tran_type = "PKSN";
	// Set the variables for the database access:
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	$label_no = '';
	$order = '';
	$prod_no = '';
	
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: GetSerial.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	//include "logme.php";
	require_once "logme.php";
	require_once "checkdata.php";
	// want to check the serial_number passed
	$serial_number = "";
	if (isset($_POST['serial_number']))
	{
		$serial_number = $_POST['serial_number'];
	}
	if (isset($_GET['serial_number']))
	{
		$serial_number = $_GET['serial_number'];
	}
	// need a list of data_ids for serials
	$scanned_ssn = "";
	if ($serial_number <> "")
	{
		$wk_intypes = Array();
		$wk_intypes = getDataTypesin("SERIAL_NUMBER");
		$field_type = "none";
		foreach ($wk_intypes as $Key_types => $Value_types) 
		{
			$field_type = checkForTypein($serial_number, $Value_types); 
			if ($field_type != "none")
			{
				if ($startposn > 0)
				{
					$wk_realdata = substr($serial_number,$startposn);
					$scanned_ssn = $wk_realdata;
				} else {
					$scanned_ssn = $serial_number;
				}
				break;
			}
		}
		if ($field_type == "none")
		{
			// no param match
			//$message .= $my_responsemessage;
			$my_message = "message=" . urlencode("Not a Serial Number");
			header("Location: GetSerial2.php?" . $my_message);
			exit();
		}
	}
#############################################
	//execute procedure add_tran('RP','DS000000','SERIALS155117','PKSN','S','NOW','Comment',1,'F','','MASTER   ',0,'D000153','SSSSSSSSS','BDCS','XX')
	$my_object = '';
	// get the passed record id
	$my_record_id = '';
/*
	if (isset($_POST['salesorder']))
	{
		$my_record_id = $_POST['salesorder'];
	}
	if (isset($_GET['salesorder']))
	{
		$my_record_id = $_GET['salesorder'];
	}
*/
	if (isset($_POST['pick_label_no']))
	{
		$my_record_id = $_POST['pick_label_no'];
	}
	if (isset($_GET['pick_label_no']))
	{
		$my_record_id = $_GET['pick_label_no'];
	}
	if ($my_record_id <> "")
	{
		$wk_dummy = 1;
	} else {
		$my_record_id  = getBDCScookie($Link, $tran_device, "pick_label_no"  );
	}
	// get the passed matchorders 
	$wk_matchorders = '';
	if (isset($_POST['matchorders']))
	{
		$wk_matchorders = $_POST['matchorders'];
	}
	if (isset($_GET['matchorders']))
	{
		$wk_matchorders = $_GET['matchorders'];
	}
	// now get the sscc and order for this pack
	$wk_pick_order  = "";
	$wk_company  = "" ;
	$wk_pick_order_type  = "";
	$wk_pick_order_sub_type  = "";
	$wk_pick_prod_id  = "";
	$wk_despatch_location  = "";
	$Query = "select p1.pick_order, p2.company_id ,p2.pick_order_type, p2.pick_order_sub_type , p1.prod_id , control.default_despatch_location  "; 
	$Query .= "from pick_item p1 ";
	$Query .= "join pick_order p2 on p1.pick_order = p2.pick_order ";
	$Query .= "join control  on control.record_id = 1 ";
	$Query .= "where  ";
	$Query .= "  p1.pick_label_no = '" . $my_record_id  . "'";
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read PICKs !<BR>\n");
		exit();
	}
	while (($Row3 = ibase_fetch_row($Result))) {
		$wk_pick_order  = $Row3[0];
		$wk_company  = $Row3[1];
		$wk_pick_order_type  = $Row3[2];
		$wk_pick_order_sub_type  = $Row3[3];
		$wk_pick_prod_id  = $Row3[4];
		$wk_despatch_location  = $Row3[5];
	}

	//release memory
	ibase_free_result($Result);
		
	
	$my_source = 'SSBSSKSSS';
	$tran_tranclass = "N";

	$tran_qty = 1;

	$my_location = $wk_despatch_location ;
	$wk_sublocn = $my_record_id ;
	$my_ref = "Comment";
	$my_message = "";
	$my_message = dotransaction("PKSN", "S", $scanned_ssn , $my_location, $wk_sublocn, $my_ref, 0, $my_source, $tran_user, $tran_device, "Y", $wk_pick_prod_id, $wk_company, $wk_pick_order, $wk_pick_order_type, $wk_pick_order_sub_type, "");
	//echo($my_message);
	if ($my_message > "")
	{
		list($my_mess_field, $my_mess_label) = explode("=", $my_message);
		$my_responsemessage = urldecode($my_mess_label) . " ";
	} else {
		$my_responsemessage = "";
	}
	if ($my_responsemessage == "")
	{
		$my_responsemessage = "Processed successfully ";
	}
	//echo($my_responsemessage);
	if ($my_responsemessage <> "Processed successfully ")
	{
		//$message .= $my_responsemessage;
		header("Location: GetSerial2.php?" . $my_message);
		exit();
	}
	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result);
	}



	//want to go to capture next serial screen
	header("Location: GetSerial2.php?matchorders=" . urlencode($wk_matchorders) . "&" . $my_message);
}
?>
