<?php
session_start();


/**
 * getOrderDetails
 *
 * @param $Link
 * @param string $orderNo
 * @return array or null
 */
function getOrderDetails($Link, $orderNo) {
    $result = array();
    $sql = 'SELECT COMPANY_ID, PICK_ORDER_TYPE, PICK_ORDER_SUB_TYPE FROM PICK_ORDER WHERE  PICK_ORDER = ?';
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        $r = ibase_execute($q, $orderNo);
        if ($r) {
            //$d = ibase_fetch_row($r);
            $d = ibase_fetch_assoc($r);
            if ($d) {
                $result = $d;
            }
            ibase_free_result($r);
        }
        ibase_free_query($q);
    }
    return $result;
}


function logtime2( $Link,  $message)
{
	$Query = "";
	//$log = fopen('/tmp/issn.orders2.log' , 'a');
	$log = fopen('/data/tmp/pkul.log' , 'a');
		$wk_current_time = "";
		$Query = "select cast(cast('NOW' as timestamp) as char(24)) from control ";
		$Query = "select cast('NOW' as timestamp) from control ";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to query table control<BR>\n");
			exit();
		}
		else
		if (($Row = ibase_fetch_row($Result)))
		{
			$wk_current_time =  $Row[0];
		}
		else
		{
			$wk_current_time = "";
		}
		
		//release memory
		//$Result->free();
		//ibase_free_result($Result);
		$wk_current_micro = microtime();
		$wk_current_time .= " " . $wk_current_micro;
		$wk_message = str_replace("'", "`", $message);
		
		// 1st try ip address of handheld
	if (isset($_COOKIE['LoginUser']))
	{
		list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
		$userline = sprintf("%s  by %s on %s", $wk_current_time, $tran_user, $tran_device);
	} else {
		$userline = sprintf("%s  ", $wk_current_time );
	}

	fwrite($log, $userline);
	fwrite($log, $message);
	fwrite($log,"  ");
	fwrite($log,"  \n");
	fclose($log);
}

/****************************************************************************************************************/
if (isset($_COOKIE['LoginUser']))
{
	$tran_type = "PKUL";
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
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
	
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: pick_Menu.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	include "logme.php";
	include "transaction.php";
	$my_object = '';
	if (isset($_COOKIE['BDCSData']))
	{
		//echo "cookie:" . $_COOKIE["BDCSData"];
		//list($label_no, $order, $ssn, $prod_no, $uom, $description, $required_qty, $location, $scanned_ssn, $picked_qty) = explode("|", $_COOKIE["BDCSData"] . "||||||||||");
	}
	{
		$wk_cookie = getBDCScookie($Link, $tran_device, "BDCSData" );
		list($label_no, $order, $ssn, $prod_no, $uom, $description, $required_qty, $location, $scannedssn, $picked_qty) = explode("|", $wk_cookie);
	}
		

	
	//$location is the device
	$my_ref = '';
	if (isset($_POST['reference']))
	{
		$my_ref = $_POST['reference'];
	}
	if (isset($_GET['reference']))
	{
		$my_ref = $_GET['reference'];
	}
	if (isset($_POST['prod']))
	{
		$prod_no = $_POST['prod'];
	}
	if (isset($_GET['prod']))
	{
		$prod_no = $_GET['prod'];
	}
	if (isset($_POST['order']))
	{
		$order = $_POST['order'];
	}
	if (isset($_GET['order']))
	{
		$order = $_GET['order'];
	}
logtime2( $Link,  __LINE__ . ":" . __FUNCTION__ . " PKUL start");
	//echo "picked qty " . $picked_qty;
	$my_sub_locn = "";
	$my_object = $order;

	$my_source = 'SSBSSKSSS';
	$tran_tranclass = "o";

	$tran_qty = 200;
	//echo "tran qty " . $tran_qty;

	/* write transaction */
	$orderData = getOrderDetails($Link, $order);
	$my_order = $order ;
	$my_company = $orderData['COMPANY_ID'];
	$my_order_type = $orderData['PICK_ORDER_TYPE'];
	$my_order_subtype = $orderData['PICK_ORDER_SUB_TYPE'];

	$my_message = "";
	$my_message = dotransaction($tran_type, $tran_tranclass, $my_object, $location, $my_sub_locn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device,"N","",$my_company, $my_order, $my_order_type, $my_order_subtype);
	if ($my_message > "") {
		list($my_mess_field, $my_mess_label) = explode("=", $my_message);
		$my_responsemessage = urldecode($my_mess_label) . " ";
	} else {
		$my_responsemessage = " ";
	}
	if (($my_responsemessage == " ") or
            ($my_responsemessage == ""))
	{
		$my_responsemessage = "Processed successfully ";
	}
	if ($my_responsemessage == "Processed successfully ")
	{
		$my_responsemessage = "OK";	
	} else {
		$wk_isok = "F";
	}
	$wk_mymessage .= "PKUL:" . $my_responsemessage;
logtime2( $Link,  __LINE__ . ":" . __FUNCTION__ . " PKUL stop ");
		
	$Query = "select 1 from pick_item ";
	$Query .= " where pick_label_no = '" . $label_no . "'";
	$Query .= " and pick_line_status = 'PG'";
	$Query .= " and device_id = '".$tran_device."'";
	//print($Query);
	
	$continue_this_line = 0;
	$continue_cnt = 0;

	if (!($Result = ibase_query($Link, $Query)))
	{
		print("Unable to Read Total!<BR>\n");
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
		$Query = "select first 1 1, pick_label_no from pick_item ";
		$Query .= " where pick_line_status in ('AL', 'PG')";
		$Query .= " and device_id = '".$tran_device."'";
		//print($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			print("Unable to Read Pick Item!<BR>\n");
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

	$Query = "select first 1 1, pick_label_no from pick_item ";
	$Query .= " where pick_line_status in ('PL')";
	$Query .= " and device_id = '".$tran_device."'";
	//print($Query);
	
	$despatch_cnt = 0;
	if (!($Result = ibase_query($Link, $Query)))
	{
		print("Unable to Read Total!<BR>\n");
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
	ibase_commit($dbTran);
	//echo("continue line " . $continue_this_line . " cont " . $continue_cnt . " cont label " . $continue_label . " despatch " . $despatch_cnt . " label " . $despatch_label . "L");
	
	//close
	//ibase_close($Link);

	//want to go to pick screen
	//header("Location: transactionUA.php");
	//want to go to pick screen
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
			header("Location: gettolocn.php?order=" . urlencode($order));
		}
		else
		{
			header("Location: pick_Menu.php");
		}
	}
}
?>
