<?php
	$location = $tran_device;
	$qty = 0;
	$my_object = '';
	$tran_type = "PKCA";
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
