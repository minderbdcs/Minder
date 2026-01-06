<?php
/*
	object appends account in wrong position
	source is cut off at 3 bytes
*/
if (isset($_COOKIE['LoginUser']))
{
	$tran_type = "DSOT";
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	
	if (isset($_POST['location']))
	{
		$location = $_POST['location'];
	}
	if (isset($_GET['location']))
	{
		$location = $_GET['location'];
	}
	if (isset($_POST['order']))
	{
		$order = $_POST['order'];
	}
	if (isset($_GET['order']))
	{
		$order = $_GET['order'];
	}
	if (isset($_POST['consignment']))
	{
		$consignment = $_POST['consignment'];
	}
	if (isset($_GET['consignment']))
	{
		$consignment = $_GET['consignment'];
	}
	if (isset($_POST['carrier']))
	{
		$carrier = $_POST['carrier'];
	}
	if (isset($_GET['carrier']))
	{
		$carrier = $_GET['carrier'];
	}
	if (isset($_POST['pallet_type']))
	{
		$pallet_type = $_POST['pallet_type'];
	}
	if (isset($_GET['pallet_type']))
	{
		$pallet_type = $_GET['pallet_type'];
	}
	if (isset($_POST['pallet_qty']))
	{
		$pallet_qty = $_POST['pallet_qty'];
	}
	if (isset($_GET['pallet_qty']))
	{
		$pallet_qty = $_GET['pallet_qty'];
	}
	if (isset($_POST['carton_qty']))
	{
		$carton_qty = $_POST['carton_qty'];
	}
	if (isset($_GET['carton_qty']))
	{
		$carton_qty = $_GET['carton_qty'];
	}
	if (isset($_POST['satchel_qty']))
	{
		$satchel_qty = $_POST['satchel_qty'];
	}
	if (isset($_GET['satchel_qty']))
	{
		$satchel_qty = $_GET['satchel_qty'];
	}
	if (isset($_POST['weight']))
	{
		$weight = $_POST['weight'];
	}
	if (isset($_GET['weight']))
	{
		$weight = $_GET['weight'];
	}
	if (isset($_POST['volume']))
	{
		$volume = $_POST['volume'];
	}
	if (isset($_GET['volume']))
	{
		$volume = $_GET['volume'];
	}
	if (isset($_POST['payer']))
	{
		$payer = $_POST['payer'];
	}
	if (isset($_GET['payer']))
	{
		$payer = $_GET['payer'];
	}
	if (isset($_POST['account']))
	{
		$account = $_POST['account'];
	}
	if (isset($_GET['account']))
	{
		$account = $_GET['account'];
	}
	if (isset($_POST['label_qty']))
	{
		$label_qty = $_POST['label_qty'];
	}
	if (isset($_GET['label_qty']))
	{
		$label_qty = $_GET['label_qty'];
	}
	if (isset($_POST['printer']))
	{
		$printer = $_POST['printer'];
	}
	if (isset($_GET['printer']))
	{
		$printer = $_GET['printer'];
	}
	if (isset($_POST['service']))
	{
		$service = $_POST['service'];
	}
	if (isset($_GET['service']))
	{
		$service = $_GET['service'];
	}


	if (isset($carrier))
	{
		list($carrier_id, $carrier_awb_isso) = explode("|", $carrier . "|");
	}
	$my_object = '';
		
	$my_object = sprintf("%-20.20s%s", $consignment , $account);
	$my_sublocn = $carrier_id;
	$my_ref = '';
	$my_ref = sprintf("%04d%-10.10s%04d%04d%05d%05d%s", $pallet_qty , $pallet_type, $carton_qty, $satchel_qty, $weight, $volume, $payer);
	if ($pallet_type == "NONE")
		$my_ref .= 'S';
	else
		$my_ref .= 'P';
	$my_ref .= $service;
	$my_ref .= '|' . $printer;

	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: getcarrier.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	$my_source = 'SSBSSKSSS';
	if (isset($location))
	{
		$tran_tranclass = "L";
		$my_source2 = substr($my_source,0,2) . "L" . substr($my_source,3,6);
		$my_source = $my_source2;
	}
	else
	{
		if (isset($order))
		{
			$tran_tranclass = "S";
			$my_source2 = substr($my_source,0,2) . "O" . substr($my_source,3,6);
			$my_source = $my_source2;
		}
	}
	$tran_qty = $label_qty;

	$Query = "select trn_type from carrier where carrier_id = '$carrier'";
	if (($Result = ibase_query($Link, $Query)))
	{
		if (($Row = ibase_fetch_row($Result)))
		{
			$tran_type =  $Row[0];
			ibase_free_result($Result); 
			unset($Result); 
		}
	}

	if (isset($location))
	{
		$my_location = $location;
	}
	else
	if (isset($order))
	{
		$my_location = $order;
	}
	include('transaction.php');
	$my_message = "";
	$my_message = dotransaction_response($tran_type, $tran_tranclass, $my_object, $my_location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
	if ($my_message > "")
	{
		list($my_mess_field, $my_mess_label) = explode("=", $my_message);
		$my_responsemessage = urldecode($my_mess_label) . " ";
	}
	else
	{
		$my_responsemessage = "";
	}
	$wk_responsemessage_saved = $my_responsemessage;
       	$wkParams = explode ("|", $my_responsemessage );
        //echo("Params:");
       	//var_dump($wkParams);
	$my_responsemessage = $wkParams[0] . " ";
	if ($my_responsemessage <> "Processed successfully ")
	{
		//$message .= $my_responsemessage;
		//echo ("<b><FONT COLOR=RED>$my_responsemessage</FONT></b>\n");
		header("Location: getcarrier.php?" . $my_message );
		exit();
	}

	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
	}
	
	//commit
	ibase_commit($dbTran);
	
	//ibase_close($Link);

	//want to go to scan labels for connote screen
	if ($tran_type == "DSST")
	{
		//header("Location: despatch_menu.php");
		header("Location: getaddrlabels.php?consignment=$consignment&label_qty=$label_qty&printer=$printer");
	}
	else
	{
		header("Location: transactionOL.php?consignment=$consignment&label_qty=$label_qty&printer=$printer&class=O");
	}
}
?>
