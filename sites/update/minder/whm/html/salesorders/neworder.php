<?php
session_start();
/*
use the 1st byte of the order type as the prefix
select sale_order_id  from get_sale_order_no( prefix)

insert into pick_order (and thus pick_order_line_no)

go of to the 1st header part for this order

*/

{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	include "logme.php";
	
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: Order_Menu.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

 	$wk_order_type = getBDCScookie($Link, $tran_device, "OrderType");
 	$wk_order_prefix = substr($wk_order_type,0,1);

	// must get the next order no
	$Query = "select sale_order_id from get_sale_order_no('" . $wk_order_prefix . "') " ;
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		// no result 
		header("Location: Order_Menu.php?message=No+Next+Order!");
		exit();
	}
	else
	if (($Row = ibase_fetch_row($Result)))
	{
		$wk_next_order = $Row[0];
		ibase_free_result($Result); 
		unset($Result); 
	}

	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
	}
	
	// must get the default terms , payment method
	$Query = " SELECT DEFAULT_TERM, DEFAULT_PAYMENT_METHOD, DEFAULT_CARRIER_ID FROM CONTROL";
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		// no result 
		$wk_term = "";
		$wk_payment = "";
		$wk_carrier = "";
	}
	else
	if (($Row = ibase_fetch_row($Result)))
	{
		$wk_term = $Row[0];
		$wk_payment = $Row[1];
		$wk_carrier = $Row[2];
		ibase_free_result($Result); 
		unset($Result); 
	}

	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
	}
	
	// ok done transfer to hh
	if (isset($wk_next_order))
	{
		// check the order exists
		$Query = "select 1 from pick_order where pick_order='" . $wk_next_order . "' "; 
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Order!<BR>\n");
			//exit();
		}
		$wk_data = "";
		while ( ($Row = ibase_fetch_row($Result)) ) {
			if ($Row[0] > "")
			{
				$wk_data = $Row[0];
			}
		}
		//echo $wk_data;
		if ($wk_data <> 1)
		{
			$Query = "insert into pick_order(pick_order, pick_status, create_date, pick_order_type, terms, payment_method, ship_via, created_by) values ('" . $wk_next_order . "','UC','NOW','" . $wk_order_type . "','" . $wk_term . "','" . $wk_payment . "','" . $wk_carrier . "','" . $tran_user . "') ";
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to Add Order!<BR>\n");
				//exit();
			}
			//release memory
			//ibase_free_result($Result);
		}
		//commit
		ibase_commit($dbTran);

		//close
		//ibase_close($Link);

		// go to get the to location
		header("Location: GetOrdCust.php?salesorder=" . $wk_next_order);
	}
	else
	{
		//commit
		ibase_commit($dbTran);

		//close
		//ibase_close($Link);
		header("Location: Order_Menu.php?message=No+Next+Order2!");
	}
}
?>
