<?php
if (isset($_COOKIE['LoginUser']))
{
	
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	
	//$location is the device from and over sized type
	$location = $tran_device . 'T';
	$qty = 1;
	$trans_type = "TRRT";
	$my_source = 'SSBSSKSSS';
	$my_ref = 'Transfer back and increase priority ';
	$my_object = ''; /* product or order to transfer */
	$tran_tranclass = "K"; /* 'P' or 'O' */
	$my_sublocn = "C0"; /* to device */
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: replenish_Menu.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

/*
	echo ($tran_tranclass);
	exit();
*/
	$tran_qty = $qty;

	/* first get the company, product and order for the 1st line */
	
	$wk_prod = "";
	$Query = "select first 1 p1.prod_id  "; 
	$Query .= "from transfer_request p1 ";
	$Query .= "left outer join location l1 on l1.wh_id = p1.to_wh_id and l1.locn_id = p1.to_locn_id ";
	$Query .= " where p1.trn_status in ('AL','PG') " ;
	$Query .= " and device_id = '".$tran_device."'";
	$Query .= " order by  p1.trn_priority,l1.locn_seq,p1.to_wh_id,p1.to_locn_id";
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		$my_message = "message=Unable+to+Get+Product!" ;
		header("Location: replenish_Menu.php?" . $my_message);
		exit();
	}
	if (($Row = ibase_fetch_row($Result)))
	{
		$wk_prod = $Row[0];
		ibase_free_result($Result); 
		unset($Result); 
	}

	$my_sublocn = "NULL";
	$tran_tranclass = "P";
	if ($tran_tranclass == 'P')
	{
		$my_object = $wk_prod;
	}

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
	//echo $my_message;

	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
	}
	
	//commit
	ibase_commit($dbTran);
	
	if ($my_responsemessage <> "Processed successfully ")
	{
		//$message .= $my_responsemessage;
		//echo $my_message;
		header("Location: replenish_Menu.php?" . $my_message);
		exit();
	}
	header("Location: replenish_Menu.php");
}
else
{
	header("Location: replenish_Menu.php?message=Not+Logged+In");
}
?>
