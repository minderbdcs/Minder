<?php
session_start();

require_once('DB.php');
require('db_access.php');
require_once('transaction.php');

//include "checkdata.php";
//require_once "checkdata.php";

if (isset($_COOKIE['LoginUser']))
{
	$tran_type = "DSUC";
	// Set the variables for the database access:
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	$label_no = '';
	$order = '';
	$prod_no = '';
	
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: ModCarrier.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	//include "logme.php";
	require_once "logme.php";
	require_once "checkdata.php";
	// want to check the service_id passed
	$service_id = "";
	if (isset($_POST['service_id']))
	{
		$service_id = $_POST['service_id'];
	}
	if (isset($_GET['service_id']))
	{
		$service_id = $_GET['service_id'];
	}
	// need a list of data_ids for serials
	$scanned_ssn = "";
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
	if (isset($_POST['despatch_id']))
	{
		$despatch_id = $_POST['despatch_id'];
	}
	if (isset($_GET['despatch_id']))
	{
		$despatch_id = $_GET['despatch_id'];
	}
	if ($despatch_id <> "")
	{
		$wk_dummy = 1;
	} else {
		$despatch_id  = getBDCScookie($Link, $tran_device, "despatch_id"  );
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
	if ($wk_matchorders <> "")
	{
		$wk_dummy = 1;
	} else {
		$wk_matchorders  = getBDCScookie($Link, $tran_device, "matchorders"  );
	}
	// get the passed matchconnotes 
	$wk_matchconnotes = '';
	if (isset($_POST['matchconnotes']))
	{
		$wk_matchconnotes = $_POST['matchconnotes'];
	}
	if (isset($_GET['matchconnotes']))
	{
		$wk_matchconnotes = $_GET['matchconnotes'];
	}
	if ($wk_matchconnotes <> "")
	{
		$wk_dummy = 1;
	} else {
		$wk_matchconnotes  = getBDCScookie($Link, $tran_device, "matchconnotes"  );
	}
	// get the passed carriers 
	$carrier_id = '';
	if (isset($_POST['carrier_id']))
	{
		$carrier_id = $_POST['carrier_id'];
	}
	if (isset($_GET['carrier_id']))
	{
		$carrier_id = $_GET['carrier_id'];
	}
	if ($carrier_id <> "")
	{
		$wk_dummy = 1;
	} else {
		$carrier_id  = getBDCScookie($Link, $tran_device, "carrier_id"  );
	}
	// now get the sscc and order for this pack
	$wk_pick_order  = "";
	$wk_company  = "" ;
	$wk_pick_order_type  = "";
	$wk_pick_order_sub_type  = "";
	$wk_pick_prod_id  = "";
	$wk_despatch_location  = "";
	$Query = "select p1.pickd_pick_order1, p2.company_id ,p2.pick_order_type, p2.pick_order_sub_type ,  control.default_despatch_location  "; 
	$Query .= "from pick_despatch p1 ";
	$Query .= "join pick_order p2 on p1.pickd_pick_order1 = p2.pick_order ";
	$Query .= "join control  on control.record_id = 1 ";
	$Query .= "where  ";
	$Query .= "  p1.despatch_id = '" . $despatch_id . "'";
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
		$wk_despatch_location  = $Row3[4];
	}

	//release memory
	ibase_free_result($Result);
		
	
	$my_source = 'SSBSSKSSS';
	$tran_tranclass = "N";

	$tran_qty = $despatch_id;

	$my_location = $wk_despatch_location ;
	$wk_object = "" ;
	$wk_sublocn = "" ;
	$my_ref = "|" . $carrier_id . "|" . $service_id . "|";
	$my_message = "";
	$my_message = dotransaction("DSUC", "U", $my_object , $my_location, $wk_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, "Y", $wk_pick_prod_id, $wk_company, $wk_pick_order, $wk_pick_order_type, $wk_pick_order_sub_type, "");
	//echo($my_message);
	echo($my_message);
// getting no response !!!!!!!!!!!!!
// although runs transaction!!!!!!!
	if ($my_message > "")
	{
		list($my_mess_field, $my_mess_label) = explode("=", $my_message);
		$my_responsemessage = urldecode($my_mess_label) . " ";
	} else {
		$my_responsemessage = "";
	}
	if ($my_responsemessage == "")
	{
		$my_responsemessage = "Processed successfully|||";
	}
	//echo($my_responsemessage);
	$my_responsemessage_list = explode("|", $my_responsemessage);
/*
var_dump($my_message);
var_dump($my_responsemessage);
var_dump($my_responsemessage_list);
die;
*/
	if ($my_responsemessage_list[0] <> "Processed successfully")
	{
		//$message .= $my_responsemessage;
		header("Location: ModCarrier3.php?" . $my_message);
		exit();
	}
	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result);
	}



	//want to go to capture next serial screen
	//header("Location: ModCarrier.php?matchorders=" . urlencode($wk_matchorders) . "&" . $my_message);
	header("Location: ModCarrier.php?matchorders=" . urlencode($wk_matchorders) . "&" .  "matchconnotes=" . urlencode($wk_matchconnotes) . "&" . $my_message);
}
?>
