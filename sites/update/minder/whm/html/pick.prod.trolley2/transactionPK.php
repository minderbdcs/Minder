<?php
session_start();

/**
 * get ISSNs on the Device
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkDevice
 * @return array
 */
function getISSNonDevice ($Link, $wkDevice )
{
	// device  = tran_device
	$wkResult = array();
	$Query = "select i1.prod_id, p2.pick_order, p2.pick_label_no, p2.prod_id, p2.ssn_id, sum(p1.qty_picked)  
                  from pick_item p2 
                  join pick_item_detail p1 on p2.pick_label_no = p1.pick_label_no
                  join issn i1 on p1.ssn_id = i1.ssn_id
                  where p2.device_id = '" . $wkDevice . "'
                  and   p2.pick_line_status = 'PL' 
                  and   p1.pick_detail_status = 'PL' 
                  and   p1.device_id = '" . $wkDevice . "'
                  and   i1.locn_id = '" . $wkDevice . "'
                  and   i1.current_qty > 0
                  group by i1.prod_id , p2.pick_label_no, p2.pick_order, p2.prod_id, p2.ssn_id ";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Pick Items!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkIssn = array();
		$wkIssn['ISSN.PROD_ID']  = $Row[0];
		$wkIssn['PICK_ORDER']  = $Row[1];
		$wkIssn['PICK_LABEL_NO']  = $Row[2];
		$wkIssn['PROD_ID']  = $Row[3];
		$wkIssn['SSN_ID']  = $Row[4];
		$wkIssn['QTY_PICKED']  = $Row[5];
		$wkResult[]  = $wkIssn;
	}
	//release memory
	ibase_free_result($Result);
	//echo('issns:' . print_r($wkResult,true));
	return $wkResult;
}

/**
 * get default device for the company that the order is for
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkOrder 
 * @return array
 */
function getZoneDevice ($Link, $wkOrder )
{
	$wkResult = "";
	$Query = "select z1.default_device_id   
                  from pick_order p1
                  join zone z1 on p1.company_id  = z1.company_id
                  where p1.pick_order = '" . $wkOrder . "' ";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Pick Order!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkResult  = $Row[0];
	}
	//release memory
	ibase_free_result($Result);
	return $wkResult;
}


if (isset($_COOKIE['LoginUser']))
{
	$tran_type = "PKIL";
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	

	$qty = 1;
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: pick_Menu.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	require_once('logme.php');
	require_once('transaction.php');
	$my_object = '';
	$my_source = 'SSBSSKSSS';
	$my_ref = '';
	$order_no='';
	if (isset($_POST['order_no']))
	{
		$order_no = $_POST['order_no'];
	}
	if (isset($_GET['order_no']))
	{
		$order_no = $_GET['order_no'];
	}
	$my_object = $order_no;
	if (isset($_POST['location']))
	{
		$location = $_POST['location'];
	}
	if (isset($_GET['location']))
	{
		$location = $_GET['location'];
	}

	if (isset($_POST['wh_id']))
	{
		$wk_wh_id = $_POST['wh_id'];
	}
	if (isset($_GET['wh_id']))
	{
		$wk_wh_id = $_GET['wh_id'];
	}
	if (isset($_POST['locn_id']))
	{
		$wk_locn_id = $_POST['locn_id'];
	}
	if (isset($_GET['locn_id']))
	{
		$wk_locn_id = $_GET['locn_id'];
	}

	if (isset($_POST['qty']))
	{
		$qty = $_POST['qty'];
	}
	if (isset($_GET['qty']))
	{
		$qty = $_GET['qty'];
	}
	$my_location = $tran_device . "        ";
	$wk_to_device = getZoneDevice($Link, $order_no);
	$wk_sublocn = ($wk_to_device == "") ?  $tran_device : $wk_to_device;
	$tran_qty = $qty;
	$wk_ref = $wk_wh_id . "|" . $wk_locn_id . "|" . "transfer to Conveyor: Location Pick";

	/* write transaction */
	$my_message = "";
	$my_message = dotransaction("TRPK", "L", $order_no, $my_location, $wk_sublocn, $wk_ref , 0, $my_source, $tran_user, $tran_device, "N");
	//echo($my_message);
	if ($my_message > "")
	{
		list($my_mess_field, $my_mess_label) = explode("=", $my_message);
		$my_responsemessage = urldecode($my_mess_label) . " ";
	}
	else
	{
		$my_responsemessage = "";
	}
	if ($my_responsemessage == "")
	{
		$my_responsemessage = "Processed successfully ";
	}
	//echo($my_responsemessage);
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
		
	$Query = "select 1 from pick_item ";
	$Query .= " where pick_line_status in ('AL','PG')";
	$Query .= " and device_id = '".$tran_device."'";
	//print($Query);
	
	if (!($Result = ibase_query($Link, $Query)))
	{
		print("Unable to Read Total!<BR>\n");
		exit();
	}
	$continue_cnt = 0;
	if (($Row = ibase_fetch_row($Result)))
	{
		$continue_cnt =  $Row[0];
	}
	
	//release memory
	ibase_free_result($Result);
	
	$wkOnDevice =  getISSNonDevice ($Link, $tran_device );

	// if all lines on this device for this order
	// are not ( AL or PG )
	// then do transfer to default device
	$Query = "select 1 from pick_item ";
	$Query .= " where pick_line_status in ('AL','PG')";
	$Query .= " and device_id = '".$tran_device."'";
	$Query .= " and pick_order = '".$order_no."'";
	//print($Query);
	
	if (!($Result = ibase_query($Link, $Query)))
	{
		print("Unable to Read Total!<BR>\n");
		exit();
	}
	$continue_order_cnt = 0;
	if (($Row = ibase_fetch_row($Result)))
	{
		$continue_order_cnt =  $Row[0];
	}
	
	//release memory
	ibase_free_result($Result);
	//if ($continue_order_cnt == -10)
	if ($continue_order_cnt == 0)
	{
		// transfer order from this device to conveyor device
		// from my device to device for company of order
		// object = order
		// location = from device
		// sub locn = to device
		// ref = comment
		$my_location = $tran_device . "        ";
		$wk_to_device = getZoneDevice($Link, $order_no);
		$wk_sublocn = ($wk_to_device == "") ?  $tran_device : $wk_to_device;
    		$printerId = isset($_POST['printer_id']) ? $_POST['printer_id'] : '';
		$my_message = "";
		$my_message = dotransaction("TRPK", "o", $order_no, $my_location, $wk_sublocn, "transfer to Conveyor:Finished Pick", 0, $my_source, $tran_user, $tran_device, "N");
		//echo($my_message);
		if ($my_message > "")
		{
			list($my_mess_field, $my_mess_label) = explode("=", $my_message);
			$my_responsemessage = urldecode($my_mess_label) . " ";
		}
		else
		{
			$my_responsemessage = "";
		}
		if ($my_responsemessage == "")
		{
				$my_responsemessage = "Processed successfully ";
		}
		//echo($my_responsemessage);
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
		// transfer orders trolley locations to the conveyor location
		//$wk_trolley_locns = getZoneDevice($Link, $order);
	}

	//commit
	ibase_commit($dbTran);
	
	//close
	//ibase_close($Link);

	//want to go to pick screen
	// if anymore product on the device  (not in a temporary location)
	// then go back to gettolocn

	$wkMode = count($wkOnDevice) > 0 ? 'Place' : 'Move';
	if ($wkMode == "Place")
	{
		header("Location: gettolocn.php");
	}
	else
	{
		if ($continue_cnt > 0)
		{
			header("Location: getfromlocn.php");
		}
		else
		{
			header("Location: pick_Menu.php");
		}
	}
}
?>
