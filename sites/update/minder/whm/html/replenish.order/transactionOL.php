<?php
if (isset($_COOKIE['LoginUser']))
{
	$tran_type = "TROL";
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	require_once "logme.php";
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);

	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: replenish_Menu.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	$ssn = '';
	$label_no = '';
	$order = '';
	$prod_no = '';
	$description = '';
	$uom = '';
	$order_qty = 0;
	$picked_qty = 0;
	$required_qty = 0;
	$scanned_ssn = '';
	
	$my_object = '';
	//if (isset($_COOKIE['BDCSData']))
	{
		//echo "cookie:" . $_COOKIE["BDCSData"];
		//list($label_no, $order, $ssn, $prod_no, $uom, $description, $required_qty, $location, $scanned_ssn, $picked_qty) = explode("|", $_COOKIE["BDCSData"]);
	}
	$wk_cookie = getBDCScookie($Link, $tran_device, "BDCSData" );
	list($label_no, $order, $ssn, $prod_no, $uom, $description, $required_qty, $location, $scanned_ssn, $picked_qty) = explode("|", $wk_cookie);
		

	if (isset($old_picked_qty))
	{
		$picked_qty = $old_picked_qty;
	}
	$qty = $picked_qty;
	//echo "picked qty " . $picked_qty;
	if ($ssn <> "")
	{
		$my_object = $scanned_ssn;
		$my_sublocn = $label_no;
	}
	else
	{
		// a product
		$my_object = $prod_no;
		$my_sublocn = "";
	}

	$Query = "select p1.company_id  "; 
	$Query .= "from transfer_request p1 ";
	$Query .= " where p1.trn_line_no = '" . $label_no . "'" ;
	$Query .= " and device_id = '".$tran_device."'";
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		$my_message = "message=Unable+to+Get+Product!" ;
	}
	if (($Row = ibase_fetch_row($Result)))
	{
		$wk_company = $Row[0];
		ibase_free_result($Result); 
		unset($Result); 
	}

	
	//$location is the device
	$my_ref = 'Replenish to Device ';
	$have_ref = "N";
	if (isset($_POST['reference']))
	{
		$my_ref .= $_POST['reference'];
		$have_ref = "Y";
	}
	if (isset($_GET['reference']))
	{
		$my_ref .= $_GET['reference'];
		$have_ref = "Y";
	}

	$my_source = 'SSBSSKSSS';
	if ($ssn <> "")
	{
		$tran_tranclass = "A";
	}
	else
	{
		$tran_tranclass = "P";
	}

	$tran_qty = $qty;
	//echo "tran qty " . $tran_qty;
	$my_tran_prod_id = $wk_prod;
	$my_tran_company_id = $wk_company;

	/* =========================================================== */

	include ("transaction.php");

	$my_message = "";
	$my_message = dotransaction_response($tran_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, $my_tran_prod_id, $my_tran_company_id);

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
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	
	if ($my_responsemessage <> "Processed successfully ")
	{
		//$message .= $my_responsemessage;
		//echo $my_message;
		header("Location: replenish_Menu.php?" . $my_message);
		exit();
	}
	/* =========================================================== */
		
	/*
	get the total to pick from the passing screen
	= required qty
	if picked_qty < required_qty
		if reference is not empty
			update status to pl
		else
			update status for product to pg
	else
		update status to pl
	*/
	if ($picked_qty < $required_qty)
	{
		if ($have_ref == "Y")
		{
			$Query = "update transfer_request set trn_status='PL' ";
			$Query .= " where  prod_id = '".$prod_no."'";
			$Query .= " and company_id = '".$company_id."'";
			$Query .= " and device_id = '".$tran_device."'";
		}
		else
		{
			$Query = "update transfer_request set trn_status='PG' ";
			$Query .= " where  prod_id = '".$prod_no."'";
			$Query .= " and company_id = '".$company_id."'";
			$Query .= " and device_id = '".$tran_device."'";
		}
	}
	else
	{
		$Query = "update transfer_request set trn_status='PL' ";
		$Query .= " where  prod_id = '".$prod_no."'";
		$Query .= " and company_id = '".$company_id."'";
		$Query .= " and device_id = '".$tran_device."'";
	}
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Update Lines!<BR>\n");
		exit();
	}
	$Query = "select 1 from transfer_request ";
	$Query .= " where trn_line_no = '" . $label_no . "'";
	$Query .= " and trn_status = 'PG'";
	$Query .= " and device_id = '".$tran_device."'";
	//echo($Query);
	
	$continue_this_line = 0;
	$continue_cnt = 0;

	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Total!<BR>\n");
		exit();
	}
	else
	if (($Row = ibase_fetch_row($Result)))
	{
		$continue_this_line =  $Row[0];
		$continue_cnt = 1;
	}
	
	//release memory
	ibase_free_result($Result);
	//commit
	ibase_commit($dbTran);
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	if ($continue_cnt == 0)
	{
		$Query = "select first 1 1, trn_line_no from transfer_request ";
		$Query .= " where trn_status in ('AL', 'PG')";
		$Query .= " and device_id = '".$tran_device."'";
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Pick Item!<BR>\n");
			exit();
		}
		else
		if (($Row = ibase_fetch_row($Result)))
		{
			$continue_cnt = 1;
			$continue_label = $Row[1];
		}
	
		//release memory
		ibase_free_result($Result);
	}

	$Query = "select first 1 1, trn_line_no from transfer_request ";
	$Query .= " where trn_status in ('PL')";
	$Query .= " and device_id = '".$tran_device."'";
	//echo($Query);
	
	$despatch_cnt = 0;
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Total!<BR>\n");
		exit();
	}
	else
	if (($Row = ibase_fetch_row($Result)))
	{
		$despatch_cnt =  1;
		$despatch_label = $Row[1];
	}
	
	//release memory
	ibase_free_result($Result);
	
	//commit
	//ibase_commit($dbTran);
	//echo("continue line " . $continue_this_line . " cont " . $continue_cnt . " cont label " . $continue_label . " despatch " . $despatch_cnt . " label " . $despatch_label . "L");
	
	//close
	//ibase_close($Link);

	//want to go to replenish screen
	// if this current line is still pg then go to ssn
	if ($continue_cnt > 0)
	{
		header("Location: getfromlocn.php");

	}
	else
	{
		if ($despatch_cnt > 0)
		{
			// go to confirm pick despatch
			// else cancel
			// if confirm pick despatch
			// must pick despatch all to default locn
			// then go to despatch menu ...
			header("Location: gettoso.php?prod=" . urlencode($prod_no));
		}
		else
		{
			header("Location: replenish_Menu.php");
		}
	}
}
?>
