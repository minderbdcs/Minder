<?php
session_start();

if (isset($_COOKIE['LoginUser']))
{
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	include 'logme.php' ;
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	
	$received_qty = "";
	$carrier= "";
	$vehicle= "";
	$container= "";
	$pallet_type= "";
	$pallet_qty= "";
	$consignment= "";
	$problem= "";
	$other1= "";

	if (isset($_COOKIE['BDCSData']))
	{
		list($received_qty, $type, $order, $line, $carrier, $vehicle, $container, $pallet_type, $pallet_qty, $consignment, $grn, $problem) = explode("|", $_COOKIE["BDCSData"]);
	}
{
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: verifyLP.php?message=Can+t+connect+to+DATABASE!"  );
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

}

	setBDCScookie($Link, $tran_device, "grn", "");
	setBDCScookie($Link, $tran_device, "order", "");
	setBDCScookie($Link, $tran_device, "line", "");

	setBDCScookie($Link, $tran_device, "received_qty", $received_qty);
	setBDCScookie($Link, $tran_device, "carrier", $carrier);
	setBDCScookie($Link, $tran_device, "vehicle", $vehicle);
	setBDCScookie($Link, $tran_device, "container", $container);
	setBDCScookie($Link, $tran_device, "pallet_type", $pallet_type);
	setBDCScookie($Link, $tran_device, "pallet_qty", $pallet_qty);
	setBDCScookie($Link, $tran_device, "consignment", $consignment);
	setBDCScookie($Link, $tran_device, "problem", $problem);

	setBDCScookie($Link, $tran_device, "retfrom", "");
	setBDCScookie($Link, $tran_device, "product", "");
	setBDCScookie($Link, $tran_device, "label_qty1", "");
	setBDCScookie($Link, $tran_device, "ssn_qty1", "");
	setBDCScookie($Link, $tran_device, "weight_qty1", "");
	setBDCScookie($Link, $tran_device, "weight_uom", "");
	//setBDCScookie($Link, $tran_device, "printer", "");
	setBDCScookie($Link, $tran_device, "received_ssn_qty", "");
	setBDCScookie($Link, $tran_device, "location", "");
	setBDCScookie($Link, $tran_device, "uom", "");
	setBDCScookie($Link, $tran_device, "printed_ssn_qty", "");
	setBDCScookie($Link, $tran_device, "other1", $other1);
	setBDCScookie($Link, $tran_device, "WH_ID", "");

	//commit
	ibase_commit($dbTran);
	
	//want to go to 
	{
		header("Location: getdelivery.php" );
	}
}
?>
