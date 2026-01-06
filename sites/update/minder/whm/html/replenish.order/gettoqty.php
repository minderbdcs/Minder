<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
include "../login.inc";
?>
<html>
 <head>
  <title>Replenish get the TO Qty</title>
<link rel=stylesheet type="text/css" href="product.css">
<?php
include "viewport.php";
?>
<?php
require_once 'DB.php';
require 'db_access.php';
include "logme.php";

/**
 * get Transfer Requests on the Device
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkDevice
 * @param string $prod_no
 * @param string $company_id
 * @param string $zone
 * @param string $toWh
 * @return array
 */
function getRequestsonDevice ($Link, $wkDevice , $prod_no, $company_id, $zone, $toWh )
{
	// device  = tran_device
	$wkResult = array();
	$Query = "select  trn_line_no, trn_priority, prod_id, company_id, to_wh_id,  qty   
	          from transfer_request  
	          where device_id = '".$wkDevice."'
	            and trn_status in ('PL','PG')
	            and prod_id = '" . $prod_no . "'
	            and company_id = '" . $company_id . "'
	            and zone_c  = '" . $zone . "'
	            and to_wh_id = '" . $toWh . "'
	            order by trn_priority";
	//echo ($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Requests!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkIssn = array();
		$wkIssn['TRN_LINE_NO']  = $Row[0];
		$wkIssn['TRN_PRIORITY']  = $Row[1];
		$wkIssn['PROD_ID']  = $Row[2];
		$wkIssn['COMPANY_ID']  = $Row[3];
		$wkIssn['TO_WH_ID'] = $Row[4];
		$wkIssn['QTY']  = $Row[5];

		$wkResult[]  = $wkIssn;
	}
	//release memory
	ibase_free_result($Result);
	//echo('issns:' . print_r($wkResult,true));
	return $wkResult;
}

/**
 * spread qty  among Requests on the Device
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkDevice
 * @return array
 */
function spreadQty ($Link, $wkDevice , $prod_no, $company_id, $zone, $toWh, $wkSpreadQty )
{
	// device  = tran_device

	$wk_qty_left = $wkSpreadQty;
	$wkRequests = array();
	$wkRequests =  getRequestsonDevice ($Link, $wkDevice , $prod_no, $company_id, $zone, $toWh );
	foreach ($wkRequests as $wk_ondevice_id => $wk_ondevice2)
	{
		//echo(print_r($wk_ondevice2,true));
		$this_qty = $wk_ondevice2['QTY'];
		$take_qty = min($this_qty, $wk_qty_left);
		if ($take_qty <0)
		{
			$take_qty = 0;
		}
		//$wk_ondevice2['SPREAD_QTY'] = $take_qty;
		$wk_qty_left -= $take_qty;
		if ($wk_qty_left <0)
		{
			$wk_qty_left = 0;
		}
		if ($take_qty > 0)
		{
			$Query = "update transfer_request set qty =  qty - " . $take_qty . " where trn_line_no = '" . $wk_ondevice2['TRN_LINE_NO'] . "'";
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to Update Spread Lines!<BR>\n");
				exit();
			}
		}

	}
}

// ===============================================================================================

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
$ssn = '';
$prod_no = '';
$location = '';
$company_id = '';
$order_qty = 0;
$picked_qty = 0;
$required_qty = 0;
$place_qty = 0;
$nolocations = 0;
	
$current_despatchZone = getBDCScookie($Link, $tran_device, "CURRENT_DESPATCH_ZONE"  );

if (isset($_POST['product']))
{
	$prod_no = $_POST['product'];
}
if (isset($_GET['product']))
{
	$prod_no = $_GET['product'];
}

if (isset($_POST['company']))
{
	$company_id = $_POST['company'];
}
if (isset($_GET['company']))
{
	$company_id = $_GET['company'];
}
if (isset($_POST['location']))
{
	$location = $_POST['location'];
}
if (isset($_GET['location']))
{
	$location = $_GET['location'];
}

if (isset($_POST['nolocations']))
{
	$nolocations = $_POST['nolocations'];
}
if (isset($_GET['nolocations']))
{
	$nolocations = $_GET['nolocations'];
}

if (isset($_POST['description']))
{
	$description = $_POST['description'];
}
if (isset($_GET['description']))
{
	$description = $_GET['description'];
}

if (isset($_POST['qtypicked']))
{
	$place_qty = $_POST['qtypicked'];
}
if (isset($_GET['qtypicked']))
{
	$place_qty = $_GET['qtypicked'];
}
// forget the passed nolocations - ie force each location to be scanned
$nolocations = 0;

function getcurrentqty($Link, $prod_no, $company_id, $wk_2_wh_id, $wk_2_locn_id, $allowed_status)
{
	$wk_current_qty = 0;
	$Query2 = "select sum(s3.current_qty) "; 
	$Query2 .= "from issn s3  ";
	$Query2 .= " where s3.prod_id = '".$prod_no."'";
	$Query2 .= " and s3.company_id = '".$company_id."'";
	$Query2 .= " and s3.current_qty > 0 ";
	$Query2 .= " and (s3.wh_id = '" . $wk_2_wh_id."' and s3.locn_id = '" . $wk_2_locn_id ."') ";
	$Query2 .= " and pos('" . $allowed_status . "',s3.issn_status,0,1) > -1";
	$Query2 .= " and (s3.wh_id < 'X' or s3.wh_id > 'X~') ";
	//echo($Query2);

	if (!($Result2 = ibase_query($Link, $Query2)))
	{
		echo("Unable to Read ISSNs!<BR>\n");
		exit();
	}
	else
	{
		if ( ($Row2 = ibase_fetch_row($Result2)) ) 
		{
			$wk_current_qty = $Row2[0];
		}
	}
	//release memory
	ibase_free_result($Result2);

	return $wk_current_qty;
} /* end function get current qty */

$Query = "select pick_import_ssn_status from control "; 
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Control<BR>\n");
	$allowed_status = ",,";
}
else
{
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$allowed_status = $Row[0];
	}
	else
	{
		$allowed_status = ",,";
	}
}
//release memory
ibase_free_result($Result);
$wh_device_wh = "";
$Query = "select first 1 wh_id from location "; 
$Query .= "where locn_id = '" . $tran_device . "' "; 
$Query .= " and (wh_id not starting 'X') ";
$Query .= " order by wh_id  ";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Location<BR>\n");
	$wk_device_wh = "";
}
else
{
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_device_wh = $Row[0];
	}
}
//release memory
ibase_free_result($Result);

if ($nolocations == 1)
{
	// only 1 location left so all goes to it
	
	//$location is the device from and over sized type
	$picked_qty = getcurrentqty($Link, $prod_no, $company_id, $wk_device_wh, $tran_device, $allowed_status);
	//$tran_qty = 0;
	$tran_qty = $picked_qty;
	if ($picked_qty == "")
	{
		$tran_qty = 0;
	}
	$trans_type = "TRIL";
	$my_source = 'SSBSSKSSS';
	$my_ref = 'Replenish to Location of product ';
	$my_object = $prod_no; /* product to transfer */
	$tran_tranclass = "P"; /* 'P'  */
	$my_sublocn = ""; /* to device */
	$my_tran_prod_id = $prod_no;
	$my_tran_company_id = $company_id;
	include ("transaction.php");
	$my_message = "";
	//$my_message = dotransaction_response($trans_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
	$my_message = dotransaction_response($trans_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, $my_tran_prod_id, $my_tran_company_id);
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

	
	if ($my_responsemessage <> "Processed successfully ")
	{
		$message = $my_responsemessage;
		//echo $my_message;
		//header("Location: replenish_Menu.php?" . $my_message);
		//exit();
	}
	else
	{
		// now update the transfer request
		$Query = "update transfer_request set trn_status='CN', device_id=null ";
		$Query .= " where to_wh_id = '";
		$Query .= substr($location,0,2);
/*
		$Query .= "' and to_locn_id = '";
		$Query .= substr($location,2,strlen($location) - 2);
*/
		$Query .= "' and prod_id = '".$prod_no."'";
		$Query .= "' and company_id = '".$company_id."'";
		$Query .= " and device_id = '".$tran_device."'";
		$Query .= " and zone_c = '".$current_despatchZone."'";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Update Lines!<BR>\n");
			exit();
		}
		$Query = "select first 1 1 from transfer_request ";
		$Query .= " where trn_status in ('PL','PG')";
		$Query .= " and device_id = '".$tran_device."'";
		//echo($Query);
		
		$continue_cnt = 0;
	
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Total!<BR>\n");
			exit();
		}
		else
		if (($Row = ibase_fetch_row($Result)))
		{
			$continue_cnt = $Row[0];
		}
		
		//release memory
		ibase_free_result($Result);
		//commit
		ibase_commit($dbTran);
		$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	
		// now go to the next screen
		if ($continue_cnt == 0)
		{
			//header("Location: replenish_Menu.php");
			$wk_next_screen = "replenish_Menu.php";
		}	
		else
		{
			//header("Location: gettoso.php");
			$wk_next_screen = "gettoso.php";
		}	
	}	
}
else
{
	if ($place_qty > 0)
	{
		// place this qty of the product into that location
		$tran_qty = $place_qty;
		$trans_type = "TRIL";
		$my_source = 'SSBSSKSSS';
		$my_ref = 'Replenish to Location - with split';
		$my_object = $prod_no; /* product to transfer */
		$tran_tranclass = "P"; /* 'P'  */
		$my_sublocn = ""; /* to device */
		$my_tran_prod_id = $prod_no;
		$my_tran_company_id = $company_id;
		include ("transaction.php");
		$my_message = "";
		//$my_message = dotransaction_response($trans_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
		$my_message = dotransaction_response($trans_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, $my_tran_prod_id, $my_tran_company_id);
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
		
		if ($my_responsemessage <> "Processed successfully ")
		{
			$message = $my_responsemessage;
			//echo $my_message;
			//header("Location: replenish_Menu.php?" . $my_message);
		}
		else
		{
			// need to update transfer requests for upto this qty of this product and company in zone
			// not just all
			// eg have on device 96 in request 5 in zone 3
			// place 10 in ds locn
			// that leaves 86 to place in other locations in zone
			// so leave the status as PL until have placed all the stock on the device
			// adjust the qty down to zero but leave as PL
			// then go to gettolocn without a transfer request qty - only the remaing qty on device
			// now update the transfer request

			// get the qty still on device
			$picked_qty = getcurrentqty($Link, $prod_no, $company_id, $wk_device_wh, $tran_device, $allowed_status);
			if ($picked_qty > 0)
			{
				// have to spread the place_qty among the transfer requests that are PL PG for prod
				$Query = "update transfer_request set qty = x ";
				$Query .= " where prod_id = '".$prod_no."'";
				$Query .= "' and company_id = '".$company_id."'";
				$Query .= " and to_wh_id = '";
				$Query .= substr($location,0,2);
				$Query .= "' and device_id = '".$tran_device."'";
				$Query .= " and zone_c = '".$current_despatchZone."'";
				$wk_to_wh = substr($location,0,2);
				spreadQty($Link, $tran_device, $prod_no, $company_id, $current_despatchZone, $wk_to_wh, $picked_qty);
				$Query = "select record_id from control ";
			} else {
				// none left so cancel it
				$Query = "update transfer_request set trn_status='CN', device_id=null,qty = null ";
				$Query .= " where prod_id = '".$prod_no."'";
				$Query .= " and company_id = '".$company_id."'";
				$Query .= " and to_wh_id = '";
				$Query .= substr($location,0,2);
				$Query .= "' and device_id = '".$tran_device."'";
				$Query .= " and zone_c = '".$current_despatchZone."'";
			}
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to Update Lines!<BR>\n");
				exit();
			}
			$Query = "select first 1 1 from transfer_request ";
			$Query .= " where trn_status in ('PL','PG')";
			$Query .= " and device_id = '".$tran_device."'";
			//echo($Query);
		
			$continue_cnt = 0;
	
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to Read Total!<BR>\n");
				exit();
			}
			else
			if (($Row = ibase_fetch_row($Result)))
			{
				$continue_cnt = $Row[0];
			}
		
			//release memory
			ibase_free_result($Result);
			//commit
			ibase_commit($dbTran);
			$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	
			// now go to the next screen
			if ($continue_cnt == 0)
			{
				//header("Location: replenish_Menu.php");
				$wk_next_screen = "replenish_Menu.php";
			}	
			else
			{
				if ($picked_qty > 0)
				{
					$wk_next_screen = "gettolocn.php";
				} else {
					//header("Location: gettoso.php");
					$wk_next_screen = "gettoso.php";
				}
			}	
		}	
	}
}

echo("</head>\n");
echo("<body bgcolor=\"#FFFFF0\">\n");
include "2buttons.php";
echo("<h2>Replenish - Placed Quantity</h2>\n");
if (isset($message))
{
	echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
}

if (isset($wk_next_screen))
{
	echo("<FORM action=\"" . $wk_next_screen . "\" method=\"post\" name=gotnextpage>\n");
	echo("<INPUT type=\"submit\" name=\"accept\" value=\"Accept\">\n");
	if (isset($picked_qty))
	{
		if ($picked_qty > 0)
		{
			echo("<INPUT type=\"hidden\"  name=\"product\" value=\"$prod_no\">");
			echo("<INPUT type=\"hidden\"  name=\"description\" value=\"$description\" >");
			echo("<INPUT type=\"hidden\"  name=\"company\"  value=\"$company_id\">");
		}
	}
	echo("</FORM\n>");
}

echo("<FORM action=\"gettoqty.php\" method=\"post\" name=getqty>\n");
echo("<FONT size=\"2\">\n");
echo("<TABLE BORDER=\"1\" ALIGN=\"LEFT\">");
echo("<TR><TD>");
if ($ssn == '')
{
	echo("Prod</TD><TD><INPUT type=\"text\" readonly name=\"product\" size=\"30\" value=\"$prod_no\"></TD></TR>");
	echo("<TR><TD colspan=\"4\">");
	echo("<INPUT type=\"text\" readonly name=\"description\" size=\"60\" value=\"$description\" >");
	echo("</TD></TR><TR><TD>Location</TD><TD>");
	echo("<INPUT type=\"text\" readonly name=\"location\" size=\"12\" value=\"".$location ."\">");
	echo("</TD><TD>");
	echo("<INPUT type=\"text\" readonly name=\"company\" size=\"10\" value=\"$company_id\"></TD></TR>");
}
echo ("<TR>\n");
echo("<TD>Device Qty</TD>\n");

$rcount = 0;
// Fetch the results from the database.
{
	$picked_qty = getcurrentqty($Link, $prod_no, $company_id, $wk_device_wh, $tran_device, $allowed_status);
	echo("<TD>".$picked_qty."</TD>\n");
	echo("</TD></TR>");
	echo("<TR>");
	echo("<TD>Qty Reqd</TD>\n");
	$Query = "select sum(qty) from transfer_request ";
	$Query .= " where trn_status in ('PL','PG')";
	$Query .= " and device_id = '".$tran_device."'";
	$Query .= " and to_wh_id = '";
	$Query .= substr($location,0,2);
/*
	$Query .= "' and to_locn_id = '";
	$Query .= substr($location,2,strlen($location) - 2);
*/
	$Query .= "' and prod_id = '".$prod_no."'";
	$Query .= " and company_id = '".$company_id."'";
	$Query .= " and zone_c = '".$current_despatchZone."'";

	//echo($Query);
	$wk_order_qty = "";
	
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Total!<BR>\n");
		exit();
	}
	else
	if (($Row = ibase_fetch_row($Result)))
	{
		$wk_order_qty = $Row[0];
	}
	
	//echo("order qty [" . $wk_order_qty . "]");
	//release memory
	ibase_free_result($Result);

	if ($wk_order_qty == "")
	{
		//echo("equal ''");
		$wk_to_wh = substr($location,0,2);
		$wk_to_locn = substr($location,2,strlen($location) - 2);
		$wk_current_qty = getcurrentqty($Link, $prod_no, $company_id, $wk_to_wh, $wk_to_locn, $allowed_status);
/*
		$Query4 = "select l1.max_qty,l1.min_qty,l1.reorder_qty "; 
		$Query4 .= "from location l1 ";
		$Query4 .= " where l1.wh_id = '".$wk_to_wh."' and ";
		$Query4 .= " l1.locn_id = '".$wk_to_locn."'";
		//echo($Query4);

		if (!($Result4 = ibase_query($Link, $Query4)))
		{
			echo("Unable to Read Locations!<BR>\n");
			exit();
		}

		// Fetch the results from the database.
		$wk_max_qty = 0;
		$wk_min_qty = 0;
		$wk_reorder_qty = 0;
		if ( ($Row4 = ibase_fetch_row($Result4)) ) {
			$wk_max_qty = $Row4[0];
			$wk_min_qty = $Row4[1];
			$wk_reorder_qty = $Row4[2];
		}
		if ($wk_current_qty <= $wk_reorder_qty)
		{
			$order_qty1 = $wk_max_qty - $wk_current_qty;
		}
		else
		{
			$order_qty1 = 0;
		}
*/
		$order_qty1 =  $wk_current_qty;
		echo("<TD>".$order_qty1."</TD>\n");
	}
	else
	{
		// a nonimated qty
		echo("<TD>".$wk_order_qty."</TD>\n");
	}
	echo ("</TR>\n");
}

echo ("</TABLE><br><br><br><br><br><br><br><br><br>\n");
//release memory
//ibase_free_result($Result);

if (!isset($wk_next_screen))
{
	//commit
	ibase_commit($dbTran);
}

//close
//ibase_close($Link);

echo("Qty Placed: <INPUT type=\"text\" name=\"qtypicked\" size=\"4\">");
echo ("<TABLE>\n");
echo ("<TR>\n");
if ($ssn == "")
{
	echo("<TH>Enter Qty</TH>\n");
}
echo ("</TR>\n");
echo ("</TABLE>\n");
/*
*/
{
	// html 4.0 browser
 	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	if ($ssn == "")
	{
		whm2buttons('Accept', 'gettoso.php',"Y","Back_50x100.gif","Back","accept.gif");
	}
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='getfromssn.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
?>
<script type="text/javascript">
<?php
if (isset($wk_next_screen))
{
	echo("document.gotnextpage.submit();\n");
}
else
{
	echo("document.getqty.qtypicked.focus();\n");
}
?>
</script>
</body>
</html>
