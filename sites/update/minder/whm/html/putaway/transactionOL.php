<?php
{
	$tran_type = 'SSN';
	$transaction_type = 'TROL';
	$from_location = '';
	$ssn_from = '';
	if (isset($_POST['from_location']))
	{
		$from_location = $_POST['from_location'];
	}
	if (isset($_GET['from_location']))
	{
		$from_location = $_GET['from_location'];
	}
	if (isset($_POST['ssn']))
	{
		$ssn_from = $_POST['ssn'];
	}
	if (isset($_GET['ssn']))
	{
		$ssn_from = $_GET['ssn'];
	}
	if (strlen($ssn_from) > 8)
	{
		// a location
		header("Location: getfromssn.php?location=".$ssn_from);
		exit();
	}

	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: getfromssn.php?location=".$from_location."&message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	if ($tran_type == "PRODUCT" )
	{
		$my_object = $product_from ;
		$my_source = 'SSBBSKSSS';
		$tran_tranclass = "P";
		$tran_qty = $qty_from;
	}
	elseif ($tran_type == "SSN")
	{
		$my_object = $ssn_from;
		$my_source = 'SSBSSKSSS';
		//$tran_tranclass = "A";
		$tran_tranclass = "W";
		$tran_qty = 1;
	}
	else
	{
		$my_object = '';
		$my_source = 'SSSBSSSSS';
		//$tran_tranclass = "A";
		$tran_tranclass = "W";
		$tran_qty = 0;
	}

	$location = $from_location;
	$my_sublocn = "";
	$my_ref = "putaway transfer to device" ;

	include("transaction.php");
	$my_message = "";
	$my_message = dotransaction_response($transaction_type , $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
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
		header("Location: getfromssn.php?location=".$from_location."&".$my_message);
		exit();
	}

	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
	}
	
	//commit
	ibase_commit($dbTran);
	
	//close
	//ibase_close($Link);

	// ok done transfer to hh
	if ($tran_type == "PRODUCT" )
	{
		// go to get the to location
		header("Location: getfromssn.php?location=".$from_location);
	}
	elseif ($tran_type == "SSN")
	{
		// go to get the to location
		header("Location: getfromssn.php?location=".$from_location);
	}
	else
	{
		// go to get the to location
		header("Location: getfromssn.php?location=".$from_location);
	}
}
?>
