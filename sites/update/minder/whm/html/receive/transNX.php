<?php
//echo("in transnx");
if (isset($_COOKIE['LoginUser']))
{
	$tran_type = "GRNX";
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	include("transaction.php");
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	
/*
	if (isset($_COOKIE['BDCSData']))
	{
		list($received_qty, $type, $order, $line, $carrier, $vehicle, $container, $pallet_type, $pallet_qty, $consignment) = explode("|", $_COOKIE["BDCSData"]);
	}
*/
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: getdelivery.php?message=Can+t+connect+to+DATABASE!"  );
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
/*
	$received_qty = getBDCScookie($Link, $tran_device, "received_qty" );
	$type = getBDCScookie($Link, $tran_device, "type" );
	$order = getBDCScookie($Link, $tran_device, "order" );
	$line = getBDCScookie($Link, $tran_device, "line" );
	$carrier = getBDCScookie($Link, $tran_device, "carrier" );
	$vehicle = getBDCScookie($Link, $tran_device, "vehicle" );
	$container = getBDCScookie($Link, $tran_device, "container" );
	$pallet_type = getBDCScookie($Link, $tran_device, "pallet_type" );
	$pallet_qty = getBDCScookie($Link, $tran_device, "pallet_qty" );
	$consignment = getBDCScookie($Link, $tran_device, "consignment" );
	$grn = getBDCScookie($Link, $tran_device, "grn" );
	$problem = getBDCScookie($Link, $tran_device, "problem" );
*/

	if (isset($_POST['grn']))
	{
		$grn = $_POST['grn'];
	}
	if (isset($_GET['grn']))
	{
		$grn = $_GET['grn'];
	}

	$entrydate = date("d-m-Y H:i:s");
	$comment = "Cancelled at (" . $entrydate;
	$comment .= ") by (" . $tran_user . ")";

	$my_object = substr($comment, 0, 30);
		
	$location = substr($comment, 30, 10);
	$my_sublocn = $grn;

	$my_ref = '';
	$my_ref = substr($comment, 40, 40);
	$my_source = 'SSBSSKSSS';
	$tran_tranclass = "L";
	$tran_qty = 0;


	
	$my_message = "";
/*
	$my_message = dotransaction_response("GRNC", "C", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
	if ($my_message > "")
	{
		list($my_mess_field, $my_mess_label) = explode("=", $my_message);
		$my_responsemessage = urldecode($my_mess_label) . " ";
	}
	else
	{
		$my_responsemessage = "";
	}
	if ($my_responsemessage <> "Processed successfully ")
	{
		//$message .= $my_responsemessage;
		//echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
		header("Location: getdelivery.php?" . $my_message );
		exit();
	}
*/

	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
	}
		
	
	//commit
	ibase_commit($dbTran);
	
	//want to go to verify
	header("Location: receive_menu.php" );
	// include "receive_menu.php";

}
?>
