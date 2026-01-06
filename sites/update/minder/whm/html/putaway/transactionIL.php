<?php
{
	$tran_type = 'SSN';
	$transaction_type = 'TRIL';
	$to_location = '';
	$ssn_to = '';
	if (isset($_POST['transaction_type']))
	{
		$transaction_type = $_POST['transaction_type'];
	}
	if (isset($_GET['transaction_type']))
	{
		$transaction_type = $_GET['transaction_type'];
	}
	if (isset($_POST['to_location']))
	{
		$to_location = $_POST['to_location'];
	}
	if (isset($_GET['to_location']))
	{
		$to_location = $_GET['to_location'];
	}
	if (isset($_POST['ssn']))
	{
		$ssn_to = $_POST['ssn'];
	}
	if (isset($_GET['ssn']))
	{
		$ssn_to = $_GET['ssn'];
	}
	if (strlen($ssn_to) > 8)
	{
		// a location
		header("Location: gettossn.php?location=".$ssn_to);
		exit();
	}

	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: gettossn.php?location=".$to_location."&message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	if ($tran_type == "PRODUCT" )
	{
		$my_object = $product_to ;
		$my_source = 'SSBBSKSSS';
		$tran_tranclass = "W";
		$tran_qty = $qty_to;
	}
	elseif ($tran_type == "SSN")
	{
		$my_object = $ssn_to;
		$my_source = 'SSBSSKSSS';
		$tran_tranclass = "W";
		$tran_qty = 0;
	}
	else
	{
		$my_object = '';
		$my_source = 'SSSBSSSSS';
		$tran_tranclass = "W";
		$tran_qty = 0;
	}

	$location = $to_location;
	$my_sublocn = "";
	$my_ref = "putaway transfer to location" ;

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
		header("Location: gettossn.php?location=".$to_location."&".$my_message);
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

	$device_wh = '';
	$Query = "select wh_id "; 
	$Query .= "from location "; 
	$Query .= "where locn_id = '".$tran_device."'";
	if (!($Result = ibase_query($Link, $Query)))
	{
		print("Unable to Read location!<BR>\n");
		exit();
	}
	
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$device_wh = $Row[0];
	}

	$device_cnt = 0;
	$Query = "select count(*)";
	$Query .= "from issn  ";
	$Query .= " where issn.issn_status in ('PA')" ;
	$Query .= " and issn.wh_id in ('";
	$Query .= $device_wh."')";
	$Query .= " and issn.locn_id in ('";
	$Query .= $tran_device."')";
	//print($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		print("Unable to Read location!<BR>\n");
		exit();
	}
	
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$device_cnt = $Row[0];
	}

	// ok done transfer to hh
	if ($device_cnt == 0)
	{
		if ($tran_type == "PRODUCT" )
		{
			// go to get the from location
			header("Location: getfromlocn.php");
		}
		elseif ($tran_type == "SSN")
		{
			// go to get the from location
			header("Location: getfromlocn.php");
		}
		else
		{
			// go to get the from location
			header("Location: getfromlocn.php");
		}
	}
	else
	{
		if ($tran_type == "PRODUCT" )
		{
			// go to get the from location
			header("Location: gettossn.php?location=$to_location");
		}
		elseif ($tran_type == "SSN")
		{
			// go to get the from location
			header("Location: gettossn.php?location=$to_location");
		}
		else
		{
			// go to get the from location
			header("Location: gettossn.php?location=$to_location");
		}
	}
}
?>
