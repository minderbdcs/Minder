<?php
if (isset($_COOKIE['LoginUser']))
{
	
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	
	//$location is the device from and over sized type
	$location = $tran_device . 'T';
	$qty = 200;
	$trans_type = "TRPK";
	$my_source = 'SSBSSKSSS';
	$my_ref = 'Transfer back and increase priority ';
	$my_object = ''; /* product or order to transfer */
	$tran_tranclass = "K"; /* 'P' or 'O' */
	$my_sublocn = "C0"; /* to device */
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: pick_Menu.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

/*
	echo ($tran_tranclass);
	exit();
*/
	$tran_qty = $qty;

	/* first get the company, product and order for the 1st line */
	
	$wk_company = "";
	$wk_order = "";
	$wk_prod = "";
	$Query = "select first 1 p1.prod_id, p1.pick_order, p3.company_id "; 
	$Query .= "from pick_item p1 ";
	$Query .= "left outer join pick_order p3 on p3.pick_order = p1.pick_order ";
	$Query .= "left outer join location l1 on l1.wh_id = p1.wh_id and l1.locn_id = p1.pick_location ";
	$Query .= " where (p1.pick_line_status in ('AL','PG') " ;
	$Query .= " or (p1.pick_line_status ='PL' AND p1.picked_qty < p1.pick_order_qty and (p1.reason = '' or p1.reason is null)) " ;
	$Query .= ") ";
	$Query .= " and device_id = '".$tran_device."'";
	$Query .= " order by  l1.locn_seq, p1.pick_location";
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		$my_message = "message=Unable+to+Get+Company!" ;
		header("Location: pick_Menu.php?" . $my_message);
		exit();
	}
	if (($Row = ibase_fetch_row($Result)))
	{
		$wk_prod = $Row[0];
		$wk_order = $Row[1];
		$wk_company = $Row[2];
		ibase_free_result($Result); 
		unset($Result); 
	}

        /* then get the to device for that company */
	$Query = "select zone.default_device_id, options.description  "; 
	$Query .= "from company ";
	$Query .= "join options on options.group_code = 'CMPPKPROD' and options.code = company.company_id ";
	$Query .= "join zone on zone.company_id = company.company_id ";
	$Query .= "where company.company_id = '" . $wk_company . "' ";
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		$my_message = "message=Unable+to+Get+Companys+Device!" ;
		header("Location: pick_Menu.php?" . $my_message);
		exit();
	}
	if (($Row = ibase_fetch_row($Result)))
	{
		$my_sublocn = $Row[0];
		$tran_tranclass = $Row[1];
		ibase_free_result($Result); 
		unset($Result); 
	}
	if ($tran_tranclass == 'P')
	{
		$my_object = $wk_prod;
	}
	if ($tran_tranclass == 'O')
	{
		$my_object = $wk_order;
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
		header("Location: pick_Menu.php?" . $my_message);
		exit();
	}
	header("Location: pick_Menu.php");
}
else
{
	header("Location: pick_Menu.php?message=Not+Logged+In");
}
?>
