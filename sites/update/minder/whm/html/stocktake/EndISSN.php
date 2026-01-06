<?php
include "../login.inc";
	require_once('DB.php');
	require('db_access.php');
	include "transaction.php";
	include "logme.php";
//setcookie("BDCSData","");
	if (isset($_COOKIE['SaveUser']))
	{
		list($tran_user, $tran_device,$UserType) = explode("|", $_COOKIE['SaveUser']);
	}
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: Stocktake_menu.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);


	{
		// do transactions
		$my_source = 'SSBSSKSSS';
		$tran_qty = 0;
		$location = "";
		$my_object = "";
		$my_sublocn = "";
		$my_ref = "End Stocktake Location" ;
	
		$my_message = "";
		$tran_tran = "STLX";
		$my_message = dotransaction_response($tran_tran, "A", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
		if ($my_message > "")
		{
			list($my_mess_field, $my_mess_label) = explode("=", $my_message);
			$my_message_response = urldecode($my_mess_label) . " || ";
			list($my_mess, $wk_prodlocn) = explode("|", $my_message_response);
			if (!isset($message))
			{
				$message = "";
			}
			$message .= $my_mess ;
		}
		else
		{
			if (!isset($message))
			{
				$message = "";
			}
			$message .= "Closed Location " ;
		}
	}
	setBDCScookie($Link, $tran_device, "stocktakelocation", "");
//commit
//ibase_commit($dbTran);
ibase_close($Link);
	header("Location: Stocktake_menu.php");
?>
