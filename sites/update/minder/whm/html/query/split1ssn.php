<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
include "../login.inc";
?>
<html>
 <head>
  <title>Split Product Load</title>
<?php
include "viewport.php";
if (strpos($_SERVER['HTTP_USER_AGENT'] , "NetFront") === false)
{
	echo('<link rel=stylesheet type="text/css" href="verifyLD.css">');
}
else
{
	echo('<link rel=stylesheet type="text/css" href="verifyLD-netfront.css">');
}
?>
<style type="text/css">
body {
     font-family: sans-serif;
     font-size: 13px;
     border: 0; padding: 0; margin: 0;
}
form, div {
    border: 0; padding:0 ; margin: 0;
}
table {
    border: 0; padding: 0; margin: 0;
}
</style>
<script type="text/javascript">
function errorHandler(errorMessage,url,line) 
{
	document.write("<p><b>Error in JS:</b> "+errorMessage+"<br>")
	document.write("<b>URL:</b> "+url+"<br>")
	document.write("<b>Line:</b> "+line+"</p>")
	return true
}
function processBack() {
	var dowhat;
/* treat as if yes is wanted in cancel */
		document.forms.getssnback.submit();
		return true;
}
function chkNumeric(strString)
{
/* check for valid numerics */
	var strValidChars = "0123456789";
	var strChar;
	var blnResult = true;
	var i;
	if (strString.length == 0) return false;
	for (i = 0; i<strString.length && blnResult == true; i++)
	{
		strChar = strString.charAt(i);
		if (strValidChars.indexOf(strChar) == -1)
		{
			blnResult = false;
		}
	}
	return blnResult;
}
function showComplete2() {
	document.getcomplete1.received_ssn_qty.value = document.getprodqtys.received_ssn_qty.value ;
	document.getcomplete1.location.value = document.getprodqtys.location.value ;
	document.getcomplete1.printer.value = document.getprodqtys.printer.value ;
	document.getcomplete1.product.value = document.getprodqtys.product.value ;
	document.getcomplete1.label_qty1.value = document.getprodqtys.label_qty1.value ;
	document.getcomplete1.ssn_qty1.value = document.getprodqtys.ssn_qty1.value ;
	document.getcomplete1.label_qty2.value = document.getprodqtys.label_qty2.value ;
	document.getcomplete1.ssn_qty2.value = document.getprodqtys.ssn_qty2.value ;
	document.getcomplete1.label_qty3.value = document.getprodqtys.label_qty3.value ;
	document.getcomplete1.ssn_qty3.value = document.getprodqtys.ssn_qty3.value ;
	if (wk_company_cnt < 2)
	{
		document.getcomplete1.owner.value = document.getprodqtys.owner.value ;
	}
	else
	{
		dowhat = document.getprodqtys.owner.selectedIndex ;
		document.getcomplete1.owner.value = document.getprodqtys.owner.options[dowhat].value ;
	}
	document.getcomplete1.submit();
}

function changeQty() {
	var dowhat;
  	var csum;
  if ( document.getprodqtys.received_ssn_qty.value=="")
  {
  	document.getprodqtys.message.value="Must Enter Split Qty";
	document.getprodqtys.received_ssn_qty.focus();
  	return false;
  }
  if ( chkNumeric(document.getprodqtys.received_ssn_qty.value)==false)
  {
  	document.getprodqtys.message.value="Split Qty Not Numeric";
	document.getprodqtys.received_ssn_qty.focus();
  	return false;
  }
  if ( document.getprodqtys.label_qty1.value=="")
  {
	document.getprodqtys.label_qty1.value = "0";
	document.getprodqtys.ssn_qty1.value = "0";
	/* no 1st labels */
  }
  if ( document.getprodqtys.ssn_qty1.value=="")
  {
	document.getprodqtys.ssn_qty1.value = "0";
	/* no 1st ssn qty */
  }
  if ( document.getprodqtys.label_qty2.value=="")
  {
	document.getprodqtys.label_qty2.value = "0";
	document.getprodqtys.ssn_qty2.value = "0";
	/* no 2nd labels */
  }
  if ( document.getprodqtys.ssn_qty2.value=="")
  {
	document.getprodqtys.ssn_qty2.value = "0";
	/* no 1st ssn qty */
  }
  if ( document.getprodqtys.label_qty3.value=="")
  {
	document.getprodqtys.label_qty3.value = "0";
	document.getprodqtys.ssn_qty3.value = "0";
	/* no 3rd labels */
  }
  if ( document.getprodqtys.ssn_qty3.value=="")
  {
	document.getprodqtys.ssn_qty3.value = "0";
	/* no 3rd ssn qty */
  }
	csum = (document.getprodqtys.label_qty1.value * document.getprodqtys.ssn_qty1.value) + 
	(document.getprodqtys.label_qty2.value * document.getprodqtys.ssn_qty2.value) +
	(document.getprodqtys.label_qty3.value * document.getprodqtys.ssn_qty3.value);
	if (csum != document.getprodqtys.received_ssn_qty.value)
	{
  		document.getprodqtys.message.value="Qty dosn't Add Up";
		document.getprodqtys.label_qty1.className = "nomatch";
		document.getprodqtys.label_qty2.className = "nomatch";
		document.getprodqtys.ssn_qty1.className = "nomatch";
		document.getprodqtys.ssn_qty2.className = "nomatch";
		/* document.getprodqtys.label_qty1.focus(); */
  		return false;
	}
	else
	{
  		document.getprodqtys.message.value="Qty Adds Up";
		document.getprodqtys.label_qty1.className = "match";
		document.getprodqtys.label_qty2.className = "match";
		document.getprodqtys.ssn_qty1.className = "match";
		document.getprodqtys.ssn_qty2.className = "match";
	}
  	return true;
}
function processEdit() {
	var dowhat;
  	var csum;
  /* document.getprodqtys.message.value="in process edit"; */
/*
  if ( document.getprodqtys.product.value=="")
  {
  	document.getprodqtys.message.value="Must Enter the Product";
	document.getprodqtys.product.focus();
  	return false;
  }
*/
  if ( document.getprodqtys.received_ssn_qty.value=="")
  {
  	document.getprodqtys.message.value="Must Enter Split Qty";
	document.getprodqtys.received_ssn_qty.focus();
  	return false;
  }
  if ( document.getprodqtys.received_ssn_qty.value=="0")
  {
  	document.getprodqtys.message.value="Must Enter Split Qty";
	document.getprodqtys.received_ssn_qty.focus();
  	return false;
  }
  if ( chkNumeric(document.getprodqtys.received_ssn_qty.value)==false)
  {
  	document.getprodqtys.message.value="Split Qty Not Numeric";
	document.getprodqtys.received_ssn_qty.focus();
  	return false;
  }
  if ( document.getprodqtys.printer.value=="")
  {
  	document.getprodqtys.message.value="Must Select the printer";
	document.getprodqtys.printer.focus();
  	return false;
  }
  if ( document.getprodqtys.label_qty1.value=="")
  {
	document.getprodqtys.label_qty1.value = "0";
	document.getprodqtys.ssn_qty1.value = "0";
	/* no 1st labels */
  }
  if ( document.getprodqtys.ssn_qty1.value=="")
  {
	document.getprodqtys.ssn_qty1.value = "0";
	/* no 1st ssn qty */
  }
  if ( document.getprodqtys.label_qty2.value=="")
  {
	document.getprodqtys.label_qty2.value = "0";
	document.getprodqtys.ssn_qty2.value = "0";
	/* no 2nd labels */
  }
  if ( document.getprodqtys.ssn_qty2.value=="")
  {
	document.getprodqtys.ssn_qty2.value = "0";
	/* no 1st ssn qty */
  }
/* =========================================================================== */

  {
  	if ( chkNumeric(document.getprodqtys.label_qty1.value)==false)
  	{
  		document.getprodqtys.message.value="First Label Qty Not Numeric";
		document.getprodqtys.label_qty1.focus();
  		return false;
  	}
	  if ( chkNumeric(document.getprodqtys.ssn_qty1.value)==false)
  	{
  		document.getprodqtys.message.value="First SSN Qty Not Numeric";
		document.getprodqtys.ssn_qty1.focus();
  		return false;
  	}
  	if ( chkNumeric(document.getprodqtys.label_qty2.value)==false)
  	{
  		document.getprodqtys.message.value="Second Label Qty Not Numeric";
		document.getprodqtys.label_qty2.focus();
  		return false;
  	}
  	if ( chkNumeric(document.getprodqtys.ssn_qty2.value)==false)
  	{
  		document.getprodqtys.message.value="Second SSN Qty Not Numeric";
		document.getprodqtys.ssn_qty2.focus();
  		return false;
  	}
	csum = (document.getprodqtys.label_qty1.value * document.getprodqtys.ssn_qty1.value) + 
	(document.getprodqtys.label_qty2.value * document.getprodqtys.ssn_qty2.value);
	if (csum != document.getprodqtys.received_ssn_qty.value)
	{
  		document.getprodqtys.message.value="Qty dosn't Add Up";
		document.getprodqtys.label_qty1.focus();
  		return false;
	}
  }
  if (csum==document.getprodqtys.received_ssn_qty.value)
  {
	showComplete2();
	return false;
  }
  else
  {
	/* the submit these qtys so far */
	document.getprodqtys.complete.value = " ";
	document.goforw.complete.value = document.getprodqtys.complete.value ;
	document.goforw.product.value = document.getprodqtys.product.value ;
	document.goforw.received_ssn_qty.value = document.getprodqtys.received_ssn_qty.value ;
	document.goforw.location.value = document.getprodqtys.location.value ;
	document.goforw.printer.value = document.getprodqtys.printer.value ;
	document.goforw.label_qty1.value = document.getprodqtys.label_qty1.value ;
	document.goforw.ssn_qty1.value = document.getprodqtys.ssn_qty1.value ;
	document.goforw.label_qty2.value = document.getprodqtys.label_qty2.value ;
	document.goforw.label_qty3.value = document.getprodqtys.label_qty3.value ;
	document.goforw.ssn_qty2.value = document.getprodqtys.ssn_qty2.value ;
	document.goforw.ssn_qty3.value = document.getprodqtys.ssn_qty3.value ;
	if (wk_company_cnt < 2)
	{
		document.goforw.owner.value = document.getprodqtys.owner.value ;
	}
	else
	{
		dowhat = document.getprodqtys.owner.selectedIndex ;
		document.goforw.owner.value = document.getprodqtys.owner.options[dowhat].value ;
	}
	document.goforw.submit();
  }
  return false;
}

function processProduct() {
	var dowhat;
	document.getproduct.received_ssn_qty.value = document.getprodqtys.received_ssn_qty.value ;
	document.getproduct.location.value = document.getprodqtys.location.value ;
	document.getproduct.printer.value = document.getprodqtys.printer.value ;
	document.getproduct.product.value = document.getprodqtys.product.value ;
	document.getproduct.label_qty1.value = document.getprodqtys.label_qty1.value ;
	document.getproduct.label_qty2.value = document.getprodqtys.label_qty2.value ;
	document.getproduct.label_qty3.value = document.getprodqtys.label_qty3.value ;
	document.getproduct.ssn_qty1.value = document.getprodqtys.ssn_qty1.value ;
	document.getproduct.ssn_qty2.value = document.getprodqtys.ssn_qty2.value ;
	document.getproduct.ssn_qty3.value = document.getprodqtys.ssn_qty3.value ;
	if (wk_company_cnt < 2)
	{
		document.getproduct.owner.value = document.getprodqtys.owner.value ;
	}
	else
	{
		dowhat = document.getprodqtys.owner.selectedIndex ;
		document.getproduct.owner.value = document.getprodqtys.owner.options[dowhat].value ;
	}
	document.getproduct.submit();
  return true;
}
/* ===================================================================== */

function recalcCol() {
	var dowhat;
  	var csum;
  if ( document.getprodqtys.received_ssn_qty.value=="")
  {
  	document.getprodqtys.message.value="Must Enter Split Qty";
	document.getprodqtys.received_ssn_qty.focus();
  	return false;
  }
  if ( document.getprodqtys.received_ssn_qty.value=="0")
  {
  	document.getprodqtys.message.value="Must Enter Split Qty";
	document.getprodqtys.received_ssn_qty.focus();
  	return false;
  }
  if ( chkNumeric(document.getprodqtys.received_ssn_qty.value)==false)
  {
  	document.getprodqtys.message.value="Split Qty Not Numeric";
	document.getprodqtys.received_ssn_qty.focus();
  	return false;
  }
  if ( document.getprodqtys.label_qty1.value=="")
  {
	document.getprodqtys.label_qty1.value = "0";
	document.getprodqtys.ssn_qty1.value = "0";
	/* no 1st labels */
  }
  if ( document.getprodqtys.ssn_qty1.value=="")
  {
	document.getprodqtys.ssn_qty1.value = "0";
	/* no 1st ssn qty */
  }
  if ( document.getprodqtys.label_qty2.value=="")
  {
	document.getprodqtys.label_qty2.value = "0";
	document.getprodqtys.ssn_qty2.value = "0";
	/* no 2nd labels */
  }
  if ( document.getprodqtys.ssn_qty2.value=="")
  {
	document.getprodqtys.ssn_qty2.value = "0";
	/* no 1st ssn qty */
  }
  if ( chkNumeric(document.getprodqtys.label_qty1.value)==false)
  {
  	document.getprodqtys.message.value="First Label Qty Not Numeric";
	document.getprodqtys.label_qty1.focus();
  	return false;
  }
  if ( chkNumeric(document.getprodqtys.ssn_qty1.value)==false)
  {
  	document.getprodqtys.message.value="First SSN Qty Not Numeric";
	document.getprodqtys.ssn_qty1.focus();
  	return false;
  }
  if ( chkNumeric(document.getprodqtys.label_qty2.value)==false)
  {
  	document.getprodqtys.message.value="Second Label Qty Not Numeric";
	document.getprodqtys.label_qty2.focus();
  	return false;
  }
  if ( chkNumeric(document.getprodqtys.ssn_qty2.value)==false)
  {
  	document.getprodqtys.message.value="Second SSN Qty Not Numeric";
	document.getprodqtys.ssn_qty2.focus();
  	return false;
  }
	csum = (document.getprodqtys.label_qty1.value * document.getprodqtys.ssn_qty1.value) + 
	(document.getprodqtys.label_qty2.value * document.getprodqtys.ssn_qty2.value);
	if (csum != document.getprodqtys.received_ssn_qty.value)
	{
  		document.getprodqtys.message.value="Qty dosn't Add Up";
		document.getprodqtys.label_qty1.className = "nomatch";
		document.getprodqtys.label_qty2.className = "nomatch";
		document.getprodqtys.ssn_qty1.className = "nomatch";
		document.getprodqtys.ssn_qty2.className = "nomatch";
		/* document.getprodqtys.label_qty1.focus(); */
  		return false;
	}
	else
	{
  		document.getprodqtys.message.value="Qty Adds Up";
		document.getprodqtys.label_qty1.className = "match";
		document.getprodqtys.label_qty2.className = "match";
		document.getprodqtys.ssn_qty1.className = "match";
		document.getprodqtys.ssn_qty2.className = "match";
	}
  return false;
}
/* ====================================================================== */
onerror = errorHandler
</script>
</head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
<body>
<?php
require_once 'DB.php';
require 'db_access.php';
//require 'logme.php';
require_once 'logme.php';

//include "checkdata.php";
require_once "checkdata.php";

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	
$received_ssn_qty = 0;
if (isset($_COOKIE['BDCSData']))
{
	list($received_qty, $type, $order, $line, $carrier, $vehicle, $container, $pallet_type, $pallet_qty, $consignment, $grn, $problem) = explode("|", $_COOKIE["BDCSData"]);
}

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<br>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

function getVehicleOption($Link, $code)
{
	{
		$Query = "select description, description2 from options where group_code='RECEIVE'  and code = 'OTHER|" . $code . "' "; 
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Options!<BR>\n");
			$log = fopen('/tmp/getdelivery.log' , 'a');
			fwrite($log, $Query);
			fclose($log);
			//exit();
		}
		$wk_data = "";
		$wk_data2 = "";
		while ( ($Row = ibase_fetch_row($Result)) ) {
			if ($Row[0] > "")
			{
				$wk_data = $Row[0];
			}
			if ($Row[1] > "")
			{
				$wk_data2 = $Row[1];
			}
		}
		//release memory
		ibase_free_result($Result);
	} 
	return array( $wk_data, $wk_data2);
} // end of function

// ==============================================================================================================


/* =========================================================================================================== */
$other_qty1 = 0;
$other_qty2 = 0;
$other_qty3 = 0;
$other_qty4 = 0;
{
	$received_qty = getBDCScookie($Link, $tran_device, "received_qty" );
	$type = getBDCScookie($Link, $tran_device, "type" );
	$order = getBDCScookie($Link, $tran_device, "order" );
	$line = getBDCScookie($Link, $tran_device, "line" );
	$carrier = getBDCScookie($Link, $tran_device, "carrier" );
	$vehicle = getBDCScookie($Link, $tran_device, "vehicle" );
	$container = getBDCScookie($Link, $tran_device, "container" );
	$pallet_type = getBDCScookie($Link, $tran_device, "pallet_type" );
	$pallet_qty = getBDCScookie($Link, $tran_device, "pallet_qty" );
	$consignment = getBDCScookie($Link, $tran_device, "consignment" );
	$grn = getBDCScookie($Link, $tran_device, "grn" );

	$product = getBDCScookie($Link, $tran_device, "product" );
	$label_qty1 = getBDCScookie($Link, $tran_device, "label_qty1" );
	$ssn_qty1 = getBDCScookie($Link, $tran_device, "ssn_qty1" );
	$label_qty2 = getBDCScookie($Link, $tran_device, "label_qty2" );
	$ssn_qty2 = getBDCScookie($Link, $tran_device, "ssn_qty2" );
	$label_qty3 = getBDCScookie($Link, $tran_device, "label_qty3" );
	$ssn_qty3 = getBDCScookie($Link, $tran_device, "ssn_qty3" );
	$weight_qty1 = getBDCScookie($Link, $tran_device, "weight_qty1" );
	$printer = getBDCScookie($Link, $tran_device, "printer" );
	$received_ssn_qty = getBDCScookie($Link, $tran_device, "received_ssn_qty" );
	$location = getBDCScookie($Link, $tran_device, "location" );
	$printed_ssn_qty = getBDCScookie($Link, $tran_device, "printed_ssn_qty" );
	$owner = getBDCScookie($Link, $tran_device, "owner" );
	$default_wh_id = getBDCScookie($Link, $tran_device, "WH_ID" );
}
if (isset($_POST['type']))
{
	$type = $_POST['type'];
}
if (isset($_GET['type']))
{
	$type = $_GET['type'];
}
if (isset($_POST['order']))
{
	$order = $_POST['order'];
}
if (isset($_GET['order']))
{
	$order = $_GET['order'];
}
if (isset($_POST['line']))
{
	$line = $_POST['line'];
}
if (isset($_GET['line']))
{
	$line = $_GET['line'];
}
if (isset($_POST['carrier']))
{
	$carrier = $_POST['carrier'];
}
if (isset($_GET['carrier']))
{
	$carrier = $_GET['carrier'];
}
if (isset($_POST['vehicle']))
{
	$vehicle = $_POST['vehicle'];
}
if (isset($_GET['vehicle']))
{
	$vehicle = $_GET['vehicle'];
}
if (isset($_POST['container']))
{
	$container = $_POST['container'];
}
if (isset($_GET['container']))
{
	$container = $_GET['container'];
}
if (isset($_POST['pallet_type']))
{
	$pallet_type = $_POST['pallet_type'];
}
if (isset($_GET['pallet_type']))
{
	$pallet_type = $_GET['pallet_type'];
}
if (isset($_POST['pallet_qty']))
{
	$pallet_qty = $_POST['pallet_qty'];
}
if (isset($_GET['pallet_qty']))
{
	$pallet_qty = $_GET['pallet_qty'];
}
if (isset($_POST['received_qty']))
{
	$received_qty = $_POST['received_qty'];
}
if (isset($_GET['received_qty']))
{
	$received_qty = $_GET['received_qty'];
}
if (isset($_POST['consignment']))
{
	$consignment = $_POST['consignment'];
}
if (isset($_GET['consignment']))
{
	$consignment = $_GET['consignment'];
}
if (isset($_POST['grn']))
{
	$grn = $_POST['grn'];
}
if (isset($_GET['grn']))
{
	$grn = $_GET['grn'];
}
if (isset($_POST['printer']))
{
	$printer = $_POST['printer'];
}
if (isset($_GET['printer']))
{
	$printer = $_GET['printer'];
}
if (isset($_POST['ssn_id'])) 
{
	$ssn_id = $_POST["ssn_id"];
}
if (isset($_GET['ssn_id'])) 
{
	$ssn_id = $_GET["ssn_id"];
}
//$backto = 'getlocn.php';
$backto = 'ssn.php';
// if printer not specified then try one saved in the session table
if (!isset($printer))
{
	$printer = getBDCScookie($Link, $tran_device, "printer" );
	if ($printer == "")
	{
		unset($printer);
	}
}
$wk_current_wh_id  = getBDCScookie($Link, $tran_device, "CURRENT_WH_ID" );
// if none then use PA
if (!isset($printer))
{
	$printer = "";
	// use warehouse.default_receive_printer using the current wh_id
	$Query = "select default_receive_printer  from warehouse where wh_id = '" . $wk_current_wh_id . "' "; 
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Warehouse!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$printer  = $Row[0];
	}
	//release memory
	ibase_free_result($Result);
	// if null use control.default_receive_printer
	if (is_null($printer )) {
		$Query = "select default_receive_printer  from control  "; 
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Control!<BR>\n");
			exit();
		}
		while ( ($Row = ibase_fetch_row($Result)) ) {
			$printer  = $Row[0];
		}
		//release memory
		ibase_free_result($Result);
	}
	// if null use PA
	if (is_null($printer )) {
		$printer = "PA";
	}
}

if (isset($_POST['product']))
{
	$product = $_POST['product'];
}
if (isset($_GET['product']))
{
	$product = $_GET['product'];
}


if (isset($_POST['received_ssn_qty']))
{
	$received_ssn_qty = $_POST['received_ssn_qty'];
}
if (isset($_GET['received_ssn_qty']))
{
	$received_ssn_qty = $_GET['received_ssn_qty'];
}
if (isset($_POST['printed_ssn_qty']))
{
	$printed_ssn_qty = $_POST['printed_ssn_qty'];
}
if (isset($_GET['printed_ssn_qty']))
{
	$printed_ssn_qty = $_GET['printed_ssn_qty'];
}
if (isset($_POST['location']))
{
	$location = $_POST['location'];
}
if (isset($_GET['location']))
{
	$location = $_GET['location'];
}

if (isset($_POST['owner']))
{
	$owner = $_POST['owner'];
}
if (isset($_GET['owner']))
{
	$owner = $_GET['owner'];
}
if (isset($_POST['message']))
{
	$message = $_POST['message'];
}
if (isset($_GET['message']))
{
	$message = $_GET['message'];
}

if (isset($_POST['noprod']))
{
	$wk_NoProd = $_POST['noprod'];
}
if (isset($_GET['noprod']))
{
	$wk_NoProd = $_GET['noprod'];
}

if (isset($ssn_id))
{
	$Query = "select  i1.wh_id, i1.locn_id, i1.prod_id, i1.current_qty, s1.ssn_description from  issn i1 left outer join ssn s1 on i1.original_ssn = s1.ssn_id  where i1.ssn_id = '$ssn_id' "; 
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read GRN!<br>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$default_wh_id = $Row[0];
		$default_locn_id = $Row[1];
		$location = $default_wh_id . $default_locn_id;
		$product = $Row[2];
		$current_qty = $Row[3];
		$ssn_description = $Row[4];
                if ($default_wh_id == "")
		{
			$default_wh_id = $wk_current_wh_id ;
		}


		setBDCScookie($Link, $tran_device, "WH_ID", $default_wh_id);
		if (isset($received_ssn_qty))
		{
			if ((int)$received_ssn_qty > $current_qty)
			{
				// too big
				$received_ssn_qty = $current_qty;
			}
		}

	}
	//release memory
	ibase_free_result($Result);
}

$Query = "select use_sale_channel  from control  "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Control!<BR>\n");
	exit();
}
$wkSaleChannel = "F";
while ( ($Row = ibase_fetch_row($Result)) ) {
	$wkSaleChannel = $Row[0];
}
//release memory
ibase_free_result($Result);
if (is_null($wkSaleChannel )) {
	$wkSaleChannel = "F";
}

$wkOrderSaleChannel = "NONE";
$wkPRRecordId = 0;
$wkPRAvailableQty = 0;
$wkPRReserveQty = 0;
$wkPRRecord = array();
if ($wkSaleChannel == "T" and $wkOrderSaleChannel <> "NONE") {
	$Query = "select record_id,available_qty,reserved_qty  from prod_reservation where prod_id='" . $product . "' and sale_channel_code='" . $wkOrderSaleChannel . "' "; 
	$Query = "select record_id,pr_available_qty,pr_reserved_qty  from prod_reservation where pr_prod_id='" . $product . "' and pr_sale_channel_code='" . $wkOrderSaleChannel . "' and pr_reservation_status = 'OP' "; 
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read PO Order Reservation!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkPRRecordId = $Row[0];
		$wkPRAvailableQty = $Row[1];
		$wkPRReserveQty = $Row[2];
	}
	//release memory
	ibase_free_result($Result);
	if (is_null($wkPRRecordID )) {
		$wkPRRecordID = 0 ;
	}
	if (is_null($wkPRAvailableQty )) {
		$wkPRAvailableQty = 0 ;
	}
	if (is_null($wkPRReserveQty )) {
		$wkPRReserveQty = 0 ;
	}
	$wkIssn = array();
	$wkIssn['PR_RECORD_ID']  = $wkPRRecordId;
	$wkIssn['PR_AVAILABLE_QTY']  = $wkPRAvailableQty;
	$wkIssn['PR_RESERVED_QTY']  = $wkPRReserveQty;
	/* $wkPRRecord[] = array(
		$wkPRRecordId  ,
		$wkPRAvailableQty  ,
		$wkPRReserveQty ); */
	$wkPRRecord[] = array($wkIssn);
} 

/* ===================================================================== */

if (isset($product) and $product != "")
{
	setBDCScookie($Link, $tran_device, "product", $product );
}

/* ====================================================================================================== */

if (isset($product) and $product != "")
{
	// get the product details
	$QueryProd = "select pp.short_desc, pp.long_desc, pp.company_id, pp.net_weight, pp.issue_per_order_unit, pp.issue_per_inner_unit, pp.order_weight, pp.net_weight_uom, pp.order_uom, pp.inner_uom, pp.order_weight_uom, pp.stock, pp.uom, pp.issue, pp.issue_per_pallet, pp.prod_id, pp.issue_per_outer_carton, pp.alternate_id";
	$QueryProd .= " from prod_profile pp";
	$QueryProd .= " where (pp.prod_id = '".$product."' or pp.alternate_id = '".$product."')";
	$QueryProd .= " and   pp.company_id ='".$owner."' ";

	//echo("[$QueryProd]\n");
	if (!($ResultProd = ibase_query($Link, $QueryProd)))
	{
		echo("Unable to query Product!<BR>\n");
		exit();
	}
	$prod_short_desc = "";
	$prod_long_desc = "";
	$prod_company = "";
	$prod_net_weight = "";
	$prod_order_qty = "";
	$prod_per_inner_qty = "";
	$prod_inner_qty = "";
	$prod_order_weight = "";
	$prod_net_weight_uom = "";
	$prod_order_uom = "";
	$prod_inner_uom = "";
	$prod_order_weight_uom = "";
	$prod_stock = "";
	$prod_uom = "";
	$prod_issue = "";
	$prod_prod_id = "";
	$prod_per_pallet_qty = "";
	$prod_per_outer_qty = "";
	$prod_alternate_id = "";
	// Fetch the results from the database.
	while (($Row = ibase_fetch_row($ResultProd))) {
		$prod_short_desc = $Row[0];
		$prod_long_desc = $Row[1];
		$prod_company = $Row[2];
		$prod_per_inner_qty = $Row[5];
		if ($prod_per_inner_qty == "")
			$prod_per_inner_qty = 1;
		//$prod_inner_qty = $prod_order_qty / $prod_per_inner_qty;
		$prod_inner_uom = $Row[9];
		if ($prod_inner_uom == "")
			$prod_inner_uom = "EA"; 
		$prod_stock = $Row[11];
		if ($prod_stock == "")
			$prod_stock = "C"; 
		$prod_uom = $Row[12];
		if ($prod_uom == "")
			$prod_uom = "EA"; 
		$prod_issue = $Row[13];
		if ($prod_issue == "")
			$prod_issue = 1;
		$prod_per_pallet_qty = $Row[14];
		if ($prod_per_pallet_qty == "")
			$prod_per_pallet_qty = 1;
		$prod_prod_id = $Row[15];
		$prod_per_outer_qty = $Row[16];
		if ($prod_per_outer_qty == "")
			$prod_per_outer_qty = 1;
		$prod_alternate_id = $Row[17];
	}

	//release memory
	ibase_free_result($ResultProd);
	if ($prod_prod_id == "") {
		//echo "not found for current company so use ALL";
		// not found for that company so try the ALL company
		$QueryProd = "select pp.short_desc, pp.long_desc, pp.company_id, pp.net_weight, pp.issue_per_order_unit, pp.issue_per_inner_unit, pp.order_weight, pp.net_weight_uom, pp.order_uom, pp.inner_uom, pp.order_weight_uom, pp.stock, pp.uom, pp.issue, pp.issue_per_pallet, pp.prod_id, pp.issue_per_outer_carton, pp.alternate_id";
		$QueryProd .= " from prod_profile pp";
		//$QueryProd .= " where pp.prod_id = '".$product."'";
		$QueryProd .= " where (pp.prod_id = '".$product."' or pp.alternate_id = '".$product."')";
		$QueryProd .= " and   pp.company_id = 'ALL'";

		//echo("[$QueryProd]\n");
		if (!($ResultProd = ibase_query($Link, $QueryProd)))
		{
			echo("Unable to query Product!<BR>\n");
			exit();
		}
		// Fetch the results from the database.
		while (($Row = ibase_fetch_row($ResultProd))) {
			$prod_short_desc = $Row[0];
			$prod_long_desc = $Row[1];
			$prod_company = $Row[2];
			$prod_per_inner_qty = $Row[5];
			if ($prod_per_inner_qty == "")
				$prod_per_inner_qty = 1;
			//$prod_inner_qty = $prod_order_qty / $prod_per_inner_qty;
			$prod_inner_uom = $Row[9];
			if ($prod_inner_uom == "")
				$prod_inner_uom = "EA"; 
			$prod_stock = $Row[11];
			if ($prod_stock == "")
				$prod_stock = "C"; 
			$prod_uom = $Row[12];
			if ($prod_uom == "")
				$prod_uom = "EA"; 
			$prod_issue = $Row[13];
			if ($prod_issue == "")
				$prod_issue = 1;
			$prod_per_pallet_qty = $Row[14];
			if ($prod_per_pallet_qty == "")
				$prod_per_pallet_qty = 1;
			$prod_prod_id = $Row[15];
			$prod_per_outer_qty = $Row[16];
			if ($prod_per_outer_qty == "")
				$prod_per_outer_qty = 1;
			$prod_alternate_id = $Row[17];
		}
		//release memory
		ibase_free_result($ResultProd);
	}
}
if (isset($product) and $product != "")
{
	if ($prod_prod_id == "")
	{
		// product not found for these 2 companys
		$wk_NoProd = "Not Found Here";
	}
	if ($prod_prod_id != $product)
	{
		if ($prod_prod_id != "")
		{
			$product = $prod_prod_id;
		}
	}
}

/* =================================================================== */

	echo("<form action=\"transLPNV.php\"  method=\"post\" name=getcomplete1>\n");
	echo("<input type=\"hidden\" name=\"type\" value=\"$type\" >");
	echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	echo("<input type=\"hidden\" name=\"order\" value=\"$ssn_id\">\n");
	echo("<input type=\"hidden\" name=\"line\" value=\"$line\">\n");
	echo("<input type=\"hidden\" name=\"owner\" >\n");
	echo("<input type=\"hidden\" name=\"received_ssn_qty\" >\n");
	echo("<input type=\"hidden\" name=\"printed_ssn_qty\" >\n");
	echo("<input type=\"hidden\" name=\"location\" >\n");
	echo("<input type=\"hidden\" name=\"printer\" >\n");
	echo("<input type=\"hidden\" name=\"product\" >\n");
	echo("<input type=\"hidden\" name=\"label_qty1\" >\n");
	echo("<input type=\"hidden\" name=\"label_qty2\" >\n");
	echo("<input type=\"hidden\" name=\"label_qty3\" >\n");
	echo("<input type=\"hidden\" name=\"ssn_qty1\" >\n");
	echo("<input type=\"hidden\" name=\"ssn_qty2\" >\n");
	echo("<input type=\"hidden\" name=\"ssn_qty3\" >\n");
	echo("<input type=\"hidden\" name=\"from\" value=\"split1ssn.php\" >\n");
	echo("<input type=\"hidden\" name=\"ssn_id\" value=\"$ssn_id\" >\n");
	echo("</form>");
//echo("</div>\n");

echo("<FONT size=\"2\">\n");
echo("<div id=\"col3\">");
echo("<form method=\"post\" name=getprodqtys onsubmit=\"return processEdit();\">");
echo("<input type=\"hidden\" name=\"complete\" >");

echo("<input type=\"text\" name=\"message\" readonly size=\"30\" class=\"message\"><br>\n");
{
		echo("Barcode:");
}
echo("<input type=\"text\" name=\"order\" maxlength=\"20\" size=\"10\" readonly value=\"$ssn_id\" class=\"noread\">\n");
echo("<input type=\"hidden\" name=\"ssn_id\" value=\"$ssn_id\" >\n");
echo("<br>\n");
// ================================================== do owner
$wk_company_cnt = 0;
$default_comp = "";
// get the default company
$Query = "select company_id from control "; 
$Query = "select company.company_id, company.name from issn join company on issn.company_id = company.company_id where issn.ssn_id = '" . $ssn_id . "'   order by name "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Control!<BR>\n");
	exit();
}
while ( ($Row = ibase_fetch_row($Result)) ) {
	$default_comp = $Row[0];
}
//release memory
ibase_free_result($Result);
$Query = "select count(*)  from company "; 
$Query = "select count(*)  from issn join company on issn.company_id = company.company_id where issn.ssn_id = '" . $ssn_id . "'    "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Control!<BR>\n");
	exit();
}
while ( ($Row = ibase_fetch_row($Result)) ) {
	$wk_company_cnt = $Row[0];
}
//release memory
ibase_free_result($Result);

{
	$owner = $default_comp ;
	echo("<input type=\"hidden\" name=\"owner\"  value=\"$owner\" >\n");
}
// =============================================== end of owner
//echo("Load Sent From:<br>");
//release memory
ibase_free_result($Result);
echo("<table border=\"0\">\n");
echo("<tr><td>");
if (isset($ssn_description)) {
	echo("Description");
	echo("</td><td>");
	echo("<input type=\"text\" name=\"ssndesc\" value=\"" . $ssn_description . "\" readonly class=\"noread\" size=\"30\" ");
	echo("  >\n");
}
echo("</td></tr>");
//echo("</div>");
//echo("<div id=\"col7\">");
echo("<tr><td>");
if (isset($product)) {
	echo("Product");
	echo("</td><td>");
	echo("<input type=\"text\" name=\"productdesc\" value=\"" . $prod_short_desc . "\" readonly class=\"noread\" size=\"30\" ");
	echo("  >\n");
}
echo("</td></tr></table>\n");
echo("<table border=\"0\">\n");
echo("<tr><td>");
//echo("Recd :<input type=\"text\" name=\"received_ssn_qty\" size=\"5\" maxlength=\"5\" class=\"default\" value=\"$received_ssn_qty\">\n");
echo("Split Qty<input type=\"text\" name=\"received_ssn_qty\" size=\"4\" maxlength=\"4\" value=\"$received_ssn_qty\" class=\"nomatch\"");
	echo(" onchange=\"recalcCol()\"");
//echo(" onfocus=\"document.getprodqtys.received_ssn_qty.value=strEmpty\" ");
echo(">\n");
echo("</td><td>");
echo("Qty<input type=\"text\" name=\"current_qty\" size=\"4\" maxlength=\"4\" readonly value=\"$current_qty\" class=\"noread\"");
echo("</td>");
//echo("</tr><tr><table>");
echo("</tr></table>");
echo("</div>");
echo("<div id=\"col8\">");
// ============  grn totals ====================
echo("<div id=\"col7\">");
// qtys of labels
{
	$wk_ToPickQty = 0;
	//echo("Qty Labels X Qty/ISSN Label:<br><input type=\"text\" name=\"label_qty1\" maxlength=\"4\" size=\"3\" class=\"sel5\" onchange=\"recalcCol()\">\n");
	echo("Qty Labels X Qty/Label<br><input type=\"text\" name=\"label_qty1\" maxlength=\"4\" size=\"3\" class=\"sel5\" onchange=\"recalcCol()\">\n");
	echo("X<input type=\"text\" name=\"ssn_qty1\" maxlength=\"5\" size=\"3\" class=\"sel5\" onchange=\"recalcCol()\">\n");
	//echo("+<input type=\"text\" name=\"label_qty2\" maxlength=\"4\" size=\"3\" class=\"sel5\" onchange=\"recalcCol()\">\n");
	echo("<br><input type=\"text\" name=\"label_qty2\" maxlength=\"4\" size=\"3\" class=\"sel5\" onchange=\"recalcCol()\">\n");
	echo("X<input type=\"text\" name=\"ssn_qty2\" maxlength=\"5\" size=\"3\" class=\"sel5\" onchange=\"recalcCol()\"><br>\n");
	echo("<input type=\"hidden\" name=\"label_qty3\" >\n");
	echo("<input type=\"hidden\" name=\"ssn_qty3\" >\n");
}
echo("</div>\n");
echo("<div ID=\"col5\">\n");
echo("Ptr:<select name=\"printer\" class=\"sel4\">\n");
$Query = "select device_id from sys_equip where device_type = 'PR' and wh_id='" . $default_wh_id . "' order by device_id "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Devices!<br>\n");
	exit();
}
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] == $printer)
	{
		echo( "<OPTION value=\"$Row[0]\" selected >$Row[0]\n");
	}
	else
	{
		echo( "<OPTION value=\"$Row[0]\">$Row[0]\n");
	}
}
//release memory
ibase_free_result($Result);
echo("</select><br>\n");
echo("</div>\n");
// ============= end of grn totals ================
echo("<div ID=\"col9\">\n");
echo("<input type=\"hidden\" name=\"location\" value=\"" . $location . "\" >");
echo("<input type=\"hidden\" name=\"product\" value=\"" . $product . "\" >\n");
/*
echo("WH<input name=\"WH_ID\" readonly value=\"" . $default_wh_id . "\" size=\"2\">\n");
echo("Location:<select name=\"location\" class=\"sel8\">\n");
$Query = "select ln.wh_id, ln.locn_id, ln.locn_name from location ln join session on session.device_id = '" . $tran_device . "' and session.code='CURRENT_WH_ID' where ln.wh_id = session.description and ln.store_area = 'RC' order by ln.locn_name "; 
$Query = "select ln.wh_id, ln.locn_id, ln.locn_name from location ln  where ln.wh_id = '" . $default_wh_id . "' and ln.locn_id = '" . $default_locn_id . "' order by ln.locn_id,ln.locn_name " ; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Locations!<br>\n");
	exit();
}
while ( ($Row = ibase_fetch_row($Result)) ) {
	echo( "<OPTION value=\"$Row[0]$Row[1]\">$Row[2]\n");
}
//release memory
ibase_free_result($Result);
echo("</select><br>\n");
*/
{
	// html 4.0 browser
	$alt = "Accept";
	// Create a table.
	echo ("<table BORDER=\"0\" ALIGN=\"LEFT\" id=\"col1\">");
	echo ("<tr>");
	echo ("<td>");
	//echo("<input type=\"IMAGE\" ");  
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
*/
	echo('SRC="/icons/whm/accept.gif" alt="' . $alt . '">');
	echo("</form>");
	echo ("</td>");
	echo ("<td>");
	echo("<form action=\"" . $backto . "\" method=\"post\" name=getssnback>\n");
	echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/Back_50x100.gif"  alt="Back">');
	echo("</form>");
	echo ("</td>");
	echo ("</tr>");
	echo ("</table>");

}

echo("</div>\n");
	echo ("</td>");
	echo ("</tr>");
	echo ("</table>");
echo("<form action=\"transLPNV.php\" method=\"post\" name=goforw >");
echo("<input type=\"hidden\" name=\"type\" value=\"$type\" >");
echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\" >");
echo("<input type=\"hidden\" name=\"complete\" >");
echo("<input type=\"hidden\" name=\"order\" value=\"$ssn_id\">\n");
echo("<input type=\"hidden\" name=\"line\" value=\"$line\">\n");
echo("<input type=\"hidden\" name=\"product\" >\n");
echo("<input type=\"hidden\" name=\"received_ssn_qty\" >\n");
echo("<input type=\"hidden\" name=\"location\" >\n");
echo("<input type=\"hidden\" name=\"printer\" >\n");
echo("<input type=\"hidden\" name=\"label_qty1\" >\n");
echo("<input type=\"hidden\" name=\"label_qty2\" >\n");
echo("<input type=\"hidden\" name=\"ssn_qty1\" >\n");
echo("<input type=\"hidden\" name=\"ssn_qty2\" >\n");
echo("<input type=\"hidden\" name=\"label_qty3\" >\n");
echo("<input type=\"hidden\" name=\"ssn_qty3\" >\n");
echo("<input type=\"hidden\" name=\"owner\" >\n");
//echo("<input type=\"hidden\" name=\"from\" value=\"getlocn.php\" >\n");
echo("<input type=\"hidden\" name=\"from\" value=\"ssn.php\" >\n");
echo("<input type=\"text\" name=\"order\" maxlength=\"20\" size=\"10\" readonly value=\"$ssn_id\" class=\"noread\">\n");
echo("<input type=\"hidden\" name=\"ssn_id\" value=\"$ssn_id\" >\n");
echo("</form>");
echo("<form action=\"split1ssn.php\" method=\"post\" name=\"gorefresh\">");
echo("<input type=\"hidden\" name=\"type\" value=\"$type\" >");
echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\" >");
echo("<input type=\"hidden\" name=\"complete\" >");
echo("<input type=\"hidden\" name=\"order\" value=\"$order\">\n");
echo("<input type=\"hidden\" name=\"line\" value=\"$line\">\n");
echo("<input type=\"hidden\" name=\"product\" >\n");
echo("<input type=\"hidden\" name=\"received_ssn_qty\" >\n");
echo("<input type=\"hidden\" name=\"location\" >\n");
echo("<input type=\"hidden\" name=\"printer\" >\n");
echo("<input type=\"hidden\" name=\"label_qty1\" >\n");
echo("<input type=\"hidden\" name=\"label_qty2\" >\n");
echo("<input type=\"hidden\" name=\"label_qty3\" >\n");
echo("<input type=\"hidden\" name=\"ssn_qty1\" >\n");
echo("<input type=\"hidden\" name=\"ssn_qty2\" >\n");
echo("<input type=\"hidden\" name=\"ssn_qty3\" >\n");
echo("<input type=\"hidden\" name=\"owner\" >\n");
//echo("<input type=\"hidden\" name=\"from\" value=\"getlocnfrom.php\" >\n");
echo("<input type=\"hidden\" name=\"from\" value=\"ssn.php\" >\n");
echo("</form>");
?>
<script type="text/javascript">
<?php
{
	echo("var wk_company_cnt=" . $wk_company_cnt. ";"); 
	echo("var strEmpty = \"\";\n");
	echo("var processDirection = \"\" ;\n");
	if (isset($message))
	{
		if (strlen($message) > 200)
		{
			$message = substr($message,1,200);
		}
		echo("document.getprodqtys.message.value=\"" . $message . "\";\n");
	} else {
		echo("document.getprodqtys.message.value=\"Enter Qtys\";\n");
	}
	echo("recalcCol();");
	
}
?>
</script>
</body>
</html>
