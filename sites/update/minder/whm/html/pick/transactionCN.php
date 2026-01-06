<?php
if (isset($_COOKIE['LoginUser']))
{
	
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	
	//$location is the device
	$location = $tran_device;
	$qty = 0;
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: pick_Menu.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	$my_object = '';
	$tran_type = "PKCA";
	if (isset($_POST['trans_type']))
	{
		$trans_type = $_POST['trans_type'];
	}
	if (isset($_GET['trans_type']))
	{
		$trans_type = $_GET['trans_type'];
	}
	$my_source = 'SSBSSKSSS';
	$my_ref = '';
	$tran_tranclass = "P";
	$tran_qty = $qty;

	$my_sublocn = "";
	include ("transaction.php");

	$my_message = "";
	$my_message = dotransaction_response($trans_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
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
		header("Location: pick_Menu.php?" . $my_message);
		exit();
	}

	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
	}
	
	//commit
	ibase_commit($dbTran);
	
	header("Location: pick_Menu.php");
}
?>
