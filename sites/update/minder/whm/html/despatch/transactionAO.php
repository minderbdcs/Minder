<?php
if (isset($_COOKIE['LoginUser']))
{
	$tran_type = "PLAO";
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	include("transaction.php");
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	
	if (isset($_POST['person']))
	{
		$person = $_POST['person'];
	}
	if (isset($_GET['person']))
	{
		$person = $_GET['person'];
	}
	if (isset($_POST['order']))
	{
		$person = $_POST['order'];
	}
	if (isset($_GET['order']))
	{
		$person = $_GET['order'];
	}
	if (isset($_POST['addressfrom']))
	{
		$addressfrom = $_POST['addressfrom'];
	}
	if (isset($_GET['addressfrom']))
	{
		$addressfrom = $_GET['addressfrom'];
	}
	if (!isset($addressfrom))
	{
		$addressfrom = "M";
	}
	if (isset($_POST['printer']))
	{
		$printer = $_POST['printer'];
	}
	if (isset($_GET['printer']))
	{
		$printer = $_GET['printer'];
	}
	if (isset($_POST['qty']))
	{
		$qty = $_POST['qty'];
	}
	if (isset($_GET['qty']))
	{
		$qty = $_GET['qty'];
	}
	if (!isset($qty))
	{
		//print("no qty\n");
		$qty = 1;
	}
	if (isset($_POST['instruction1']))
	{
		$instruction1 = $_POST['instruction1'];
	}
	if (isset($_GET['instruction1']))
	{
		$instruction1 = $_GET['instruction1'];
	}
	if (isset($_POST['instruction2']))
	{
		$instruction2 = $_POST['instruction2'];
	}
	if (isset($_GET['instruction2']))
	{
		$instruction2 = $_GET['instruction2'];
	}

	//$my_object = sprintf("%-10.10s%s", $person, substr($instruction1,0,20));
	$my_object = $person ;
	$location = substr($instruction1,20,10);
	$my_sublocn = substr($instruction1,30,10);
	//$my_ref = $instruction2 . "|" . $printer;
	//$my_ref = $instruction2 . "|" . $printer . "|" . $instruction1;
	$my_ref = trim($instruction1, "|") . "|" . trim($instruction2, "|") . "|" . trim($printer,"|") ;

	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: getdespatchexit.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	$my_source = 'SSBSSKSSS';
	$tran_tranclass = $addressfrom;
	$tran_qty = $qty;

	$my_message = "";
	$my_message = dotransaction_response($tran_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
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
		header("Location: despatch_menu.php?".$my_message);
		exit();
	}


	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
	}
		
	//commit
	ibase_commit($dbTran);
	
	//want to go to coninuing of exits 
	{
		header("Location: despatch_menu.php");
	}
}
?>
