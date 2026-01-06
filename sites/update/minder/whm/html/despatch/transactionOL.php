<?php
if (isset($_COOKIE['LoginUser']))
{
	$tran_type = "DSOL";
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	
	$location = "          ";
	if (isset($_POST['consignment']))
	{
		$consignment = $_POST['consignment'];
	}
	if (isset($_GET['consignment']))
	{
		$consignment = $_GET['consignment'];
	}
	if (isset($_POST['addrlabel']))
	{
		$addrlabel = $_POST['addrlabel'];
	}
	if (isset($_GET['addrlabel']))
	{
		$addrlabel = $_GET['addrlabel'];
	}
	if (!isset($addrlabel))
	{
		$addrlabel = "";
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
	if (isset($_POST['class']))
	{
		$class = $_POST['class'];
	}
	if (isset($_GET['class']))
	{
		$class = $_GET['class'];
	}
	if (!isset($class))
	{
		$class = "L";
	}

	$my_object = '';
		
	$my_object = sprintf("%-20.20s%s", $consignment , '          ');
	$my_sublocn = '';
	$my_ref = '';
	$my_ref = sprintf("%-20.20s", $addrlabel);
	$my_ref .= '|' . $printer;

	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: getcarrier.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	$my_source = 'SSBSSKSSS';
	$tran_tranclass = $class;
	$tran_qty = $label_qty;
	$my_location = "";	
	if (isset($location))
	{
		$my_location = $location;
	}
	else
	{
		if (isset($order))
		{
			$my_location = $order;
		}
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
	if ($my_responsemessage <> "Processed successfully ")
	{
		//$message .= $my_responsemessage;
		//echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
		header("Location: getcarrier.php?" . $my_message);
		exit();
	}

	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
	}
		
	
	//commit
	ibase_commit($dbTran);
	
	$Query = "SELECT count(*) FROM PACK_ID JOIN PICK_DESPATCH ON PICK_DESPATCH.DESPATCH_ID = PACK_ID.DESPATCH_ID WHERE PACK_ID.DESPATCH_LABEL_NO IS NULL  AND PICK_DESPATCH.AWB_CONSIGNMENT_NO = '$consignment'";
	//print($Query);

	if (!($Result = ibase_query($Link, $Query)))
	{
		print("Unable to Query Packs for Consignment!<BR>\n");
		exit();
	}

	while ( ($Row = ibase_fetch_row($Result)) ) {
		$unscanned_qty = $Row[0];
	}
	//ibase_close($Link);

	//want to go to scan labels for connote screen
	if ($class == 'L')
	{
		if (($unscanned_qty > 0) and ($class == 'L'))
		{
			//header("Location: despatch_menu.php");
			header("Location: getaddrlabels.php?consignment=$consignment&label_qty=$label_qty&printer=$printer");
		}
		else
		{
			header("Location: despatch_menu.php");
		}
	}
	else
	{
		header("Location: getaddrlabelsprt.php?consignment=$consignment&label_qty=$label_qty&printer=$printer");
	}
}
?>
