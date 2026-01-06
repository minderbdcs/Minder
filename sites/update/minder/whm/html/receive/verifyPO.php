<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
include "../login.inc";
?>
<html>
 <head>
  <title>Receive Verify Product Load</title>
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
function errorHandler(errorMessage, url, line) 
{
	document.write("<p><b>Error in JS:</b> "+errorMessage+"<br>");
	document.write("<b>URL:</b> "+url+"<br>");
	document.write("<b>Line:</b> "+line+"</p>");
	return true;
}
function showComplete2() {
/* alert("in showcom2 "); */
	document.getcomplete1.retfrom.value = document.getprodqtys.retfrom.value ;
	document.getcomplete1.received_ssn_qty.value = document.getprodqtys.received_ssn_qty.value ;
	document.getcomplete1.location.value = document.getprodqtys.location.value ;
	document.getcomplete1.printer.value = document.getprodqtys.printer.value ;
	if (document.getprodqtys.problem.checked == true)
	{
		document.getcomplete1.problem.value = "T" ;
	}
	else
	{
		document.getcomplete1.problem.value = "" ;
	}
	document.getcomplete1.uom.value = document.getprodqtys.uom.value ;
	document.getcomplete1.product.value = document.getprodqtys.product.value ;
	document.getcomplete1.label_qty1.value = document.getprodqtys.label_qty1.value ;
	document.getcomplete1.ssn_qty1.value = document.getprodqtys.ssn_qty1.value ;
	document.getcomplete1.label_qty2.value = document.getprodqtys.label_qty2.value ;
	document.getcomplete1.ssn_qty2.value = document.getprodqtys.ssn_qty2.value ;
	document.getcomplete1.label_qty3.value = document.getprodqtys.label_qty3.value ;
	document.getcomplete1.ssn_qty3.value = document.getprodqtys.ssn_qty3.value ;
	document.getcomplete1.submit();
}
function chkNumeric(strString)
{
//check for valid numerics
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
function changeQty() {
	var dowhat;
  	var csum;
  if ( document.getprodqtys.received_ssn_qty.value=="")
  {
  	document.getprodqtys.message.value="Must Enter Received Qty";
	document.getprodqtys.received_ssn_qty.focus();
  	return false;
  }
  if ( chkNumeric(document.getprodqtys.received_ssn_qty.value)==false)
  {
  	document.getprodqtys.message.value="Received Qty Not Numeric";
	document.getprodqtys.received_ssn_qty.focus();
  	return false;
  }
  if (doDirect == "T")
  {
	/* have toPickQty and  document.getprodqtys.received_ssn_qty.value */
/*
  	document.getprodqtys.label_qty1.value = 1;
  	document.getprodqtys.ssn_qty1.value = Math.min(toPickQty, document.getprodqtys.received_ssn_qty.value); -* qty required for picks *- 
  	document.getprodqtys.label_qty2.value =  ( document.getprodqtys.received_ssn_qty.value - document.getprodqtys.ssn_qty1.value ) / issnDirectDefaultQty;  -* qty remaining for issn labels *-
  	document.getprodqtys.ssn_qty2.value = issnDirectDefaultQty ;
  	document.getprodqtys.dlabel_qty1.value = 1;
  	document.getprodqtys.dssn_qty1.value = Math.min(toPickQty, document.getprodqtys.received_ssn_qty.value); -* qty required for picks *- 
  	document.getprodqtys.dlabel_qty2.value =  ( document.getprodqtys.received_ssn_qty.value - document.getprodqtys.ssn_qty1.value ) / issnDirectDefaultQty;  -* qty remaining for issn labels *-
  	document.getprodqtys.dssn_qty2.value = issnDirectDefaultQty ;
*/
  	document.getprodqtys.label_qty3.value = 1;
  	document.getprodqtys.ssn_qty3.value = Math.min(toPickQty, document.getprodqtys.received_ssn_qty.value); /* qty required for picks */ 
  	document.getprodqtys.label_qty1.value =  Math.floor( ( document.getprodqtys.received_ssn_qty.value - document.getprodqtys.ssn_qty3.value ) / issnDirectDefaultQty );  /* qty remaining for issn labels */
  	document.getprodqtys.ssn_qty1.value = issnDirectDefaultQty ;
  	document.getprodqtys.label_qty2.value = 1;
  	document.getprodqtys.ssn_qty2.value = ( document.getprodqtys.received_ssn_qty.value - document.getprodqtys.ssn_qty3.value - (document.getprodqtys.ssn_qty1.value * document.getprodqtys.label_qty1.value ) ); /* qty required for issn part of issn qty */ 
  	document.getprodqtys.dlabel_qty3.value = 1;
  	document.getprodqtys.dssn_qty3.value = Math.min(toPickQty, document.getprodqtys.received_ssn_qty.value); /* qty required for picks */ 
  	document.getprodqtys.dlabel_qty1.value =  Math.floor ( ( document.getprodqtys.received_ssn_qty.value - document.getprodqtys.ssn_qty3.value ) / issnDirectDefaultQty );  /* qty remaining for issn labels */
  	document.getprodqtys.dssn_qty1.value = issnDirectDefaultQty ;
  	document.getprodqtys.dlabel_qty2.value = 1;
  	document.getprodqtys.dssn_qty2.value = ( document.getprodqtys.received_ssn_qty.value - document.getprodqtys.ssn_qty3.value - (document.getprodqtys.ssn_qty1.value * document.getprodqtys.label_qty1.value ) ); /* qty required for picks */ 
  }
  	return true;
}
function processEdit() {
	var dowhat;
  	var csum;
  /* document.getprodqtys.message.value="in process edit"; */
  if ( document.getprodqtys.uom.value=="")
  {
  	document.getprodqtys.message.value="Must Select the UoM";
	document.getprodqtys.uom.focus();
  	return false;
  }
  if ( document.getprodqtys.received_ssn_qty.value=="")
  {
  	document.getprodqtys.message.value="Must Enter Received Qty";
	document.getprodqtys.received_ssn_qty.focus();
  	return false;
  }
  if ( document.getprodqtys.received_ssn_qty.value=="0")
  {
  	document.getprodqtys.message.value="Must Enter Received Qty";
	document.getprodqtys.received_ssn_qty.focus();
  	return false;
  }
  if ( chkNumeric(document.getprodqtys.received_ssn_qty.value)==false)
  {
  	document.getprodqtys.message.value="Received Qty Not Numeric";
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

  if (doDirect == "T")
  {
  	/* doing direct delivery so calc the sum  */
/*
  	document.getprodqtys.label_qty1.value = 1;
  	document.getprodqtys.ssn_qty1.value = 0 -* qty required for picks *- ;
  	document.getprodqtys.label_qty2.value =  0 -* qty remaining for issn labels *-;
  	document.getprodqtys.ssn_qty2.value = issnDirectDefaultQty ;
*/
/* this overwrites the entered qty */
  	document.getprodqtys.label_qty3.value = 1;
  	document.getprodqtys.ssn_qty3.value = Math.min(toPickQty, document.getprodqtys.received_ssn_qty.value); /* qty required for picks */ 
/*
  	document.getprodqtys.label_qty1.value =  Math.floor( ( document.getprodqtys.received_ssn_qty.value - document.getprodqtys.ssn_qty3.value ) / issnDirectDefaultQty );  -* qty remaining for issn labels *-
  	document.getprodqtys.ssn_qty1.value = issnDirectDefaultQty ;
  	document.getprodqtys.label_qty2.value = 1;
  	document.getprodqtys.ssn_qty2.value = ( document.getprodqtys.received_ssn_qty.value - document.getprodqtys.ssn_qty3.value - (document.getprodqtys.ssn_qty1.value * document.getprodqtys.label_qty1.value ) ); -* qty required for picks *- 
        csum=document.getprodqtys.received_ssn_qty.value;
*/
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
	(document.getprodqtys.label_qty2.value * document.getprodqtys.ssn_qty2.value) +
	(document.getprodqtys.label_qty3.value * document.getprodqtys.ssn_qty3.value);

	if (csum != document.getprodqtys.received_ssn_qty.value)
	{
  		document.getprodqtys.message.value="Qty dosn't Add Up";
		document.getprodqtys.label_qty1.focus();
  		return false;
	}
  }
  else
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
  if ( document.getprodqtys.location.value=="")
  {
 	document.getprodqtys.message.value="Must Select the Location";
	document.getprodqtys.location.focus();
  	return false;
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
	document.goforw.retfrom.value = document.getprodqtys.retfrom.value ;
	document.goforw.uom.value = document.getprodqtys.uom.value ;
	document.goforw.product.value = document.getprodqtys.product.value ;
	document.goforw.received_ssn_qty.value = document.getprodqtys.received_ssn_qty.value ;
	document.goforw.location.value = document.getprodqtys.location.value ;
	document.goforw.printer.value = document.getprodqtys.printer.value ;
	document.goforw.label_qty1.value = document.getprodqtys.label_qty1.value ;
	document.goforw.ssn_qty1.value = document.getprodqtys.ssn_qty1.value ;
	document.goforw.label_qty2.value = document.getprodqtys.label_qty2.value ;
	document.goforw.ssn_qty2.value = document.getprodqtys.ssn_qty2.value ;
	document.goforw.label_qty3.value = document.getprodqtys.label_qty3.value ;
	document.goforw.ssn_qty3.value = document.getprodqtys.ssn_qty3.value ;
	if (document.getprodqtys.problem.checked == true)
	{
		document.goforw.problem.value = "T" ;
	}
	else
	{
		document.goforw.problem.value = "" ;
	}
	document.goforw.submit();
  }
  return false;
}
function processComment() {
	var dowhat;
	document.getcomment.retfrom.value = document.getprodqtys.retfrom.value ;
	document.getcomment.received_ssn_qty.value = document.getprodqtys.received_ssn_qty.value ;
	document.getcomment.location.value = document.getprodqtys.location.value ;
	document.getcomment.printer.value = document.getprodqtys.printer.value ;
	document.getcomment.problem.value = document.getprodqtys.problem.value ;
	document.getcomment.uom.value = document.getprodqtys.uom.value ;
	document.getcomment.product.value = document.getprodqtys.product.value ;
	document.getcomment.label_qty1.value = document.getprodqtys.label_qty1.value ;
	document.getcomment.label_qty2.value = document.getprodqtys.label_qty2.value ;
	document.getcomment.label_qty3.value = document.getprodqtys.label_qty3.value ;
	document.getcomment.ssn_qty1.value = document.getprodqtys.ssn_qty1.value ;
	document.getcomment.ssn_qty2.value = document.getprodqtys.ssn_qty2.value ;
	document.getcomment.ssn_qty3.value = document.getprodqtys.ssn_qty3.value ;
	document.getcomment.submit();
  return true;
}
function processBack() {
	var dowhat;
        processDirection="BACK";
	document.getssnback.submit();
  return true;
}
onerror = errorHandler
</script>
<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include "logme.php";

echo("</head>\n");
echo("<!-- Background white, links blue (unvisited), navy (visited), red (active) -->\n");
echo("<body>\n");

$received_ssn_qty = 0;
if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	
if (isset($_COOKIE['BDCSData']))
{
	list($received_qty, $type, $order, $line, $carrier, $vehicle, $container, $pallet_type, $pallet_qty, $consignment, $grn, $problem) = explode("|", $_COOKIE["BDCSData"] . "||||||||||||");
}

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<br>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

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
	$problem = getBDCScookie($Link, $tran_device, "problem" );
	$default_wh_id = getBDCScookie($Link, $tran_device, "WH_ID" );
}

// ==============================================================================================================

/**
 * get the qty of Pick Lines ready to be picked for this product
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkProduct the Product to use
 * @param string $wkCompany the Company to use
 * @param string $wkSaleChannel whether or not to use sale channels T or F
 * @param string $wkOrderSaleChannel SaleChannel for the Purchase Order
 * @param string $wkPRRecord Array of RECORD_ID of the PROD_RESERVATION record involved 
 * @return array

 * @oldparam string $wkPRRecordId old parameter RECORD_ID of the PROD_RESERVATION record involved 
 */
//function getLines4Picks ($Link, $wkProduct, $wkCompany )
function getLines4Picks ($Link, 
                         $wkProduct, 
                         $wkCompany ,
                         $wkSaleChannel, 
                         $wkOrderSaleChannel,
                         $wkPRRecordId)
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	// want to limit by wh of receive 
	$toPickWhId = "";
	$Query = "select  session.description from session where session.device_id = '" . $tran_device . "' and session.code='CURRENT_WH_ID' " ; 
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Session!<br>\n");
	}
	while (($Row5 = ibase_fetch_row($Result))) {
		$toPickWhId = $Row5[0];
	}		

	//release memory
	ibase_free_result($Result);
	if (sizeof($wkPRRecordId) > 0) {
		$wk1stPRRecordId = $wkPRRecordId[0]['PR_RECORD_ID'];
	} else {
		$wk1stPRRecordId = 0;
	} 

		$toPickQty = 0;
		$Query = "select sum(p1.pick_order_qty - coalesce(p1.picked_qty,0) )  "; 
		$Query .= "from pick_item p1 ";
		$Query .= "join pick_order p2 on p1.pick_order = p2.pick_order ";
		//$Query .= "where p1.pick_line_status in ('OP','UP') ";
		$Query .= "where p1.pick_line_status in ('OP','UP','AS') ";
		$Query .= "and   p1.prod_id = '" . $wkProduct . "' ";
		$Query .= "and   p2.pick_status in ('OP','DA') ";
		$Query .= "and   p2.wh_id = '" . $toPickWhId . "' ";
		$Query .= "and   p2.company_id = '" . $wkCompany . "' ";
		//if ($wkPRRecordId <> 0 and $wkOrderSaleChannel <> "NONE" and $wkSaleChannel == "T" ) {
		if ($wk1stPRRecordId <> 0 and $wkOrderSaleChannel <> "NONE" and $wkSaleChannel == "T" ) {
			/* have a prod_reservation so limit the orders to those that use it */
			//$Query .= "and   p2.other2 = '" . $wkOrderSaleChannel . "' ";
			/* have a Sales Channel so can use any order channel or none - but 1st use the orders channel */
			$wkDummy = 1;
		}
		if ( $wkOrderSaleChannel == "NONE" and $wkSaleChannel == "T" ) {
			/* have a  NONE Sales Channel so limit the orders to those that use it */
			//$Query .= "and   coalesce(p2.other2,'') = '' ";
			/* have a  NONE Sales Channel so can use any order channel or none */
			$wkDummy = 1;
		}
/*
		if ( $wkSaleChannel == "F" ) {
			-* have No prod_reservation so no limits  *-
		}
*/
/* that will give 
lines for the product that are unpicked
1 now must ensure that this line or product is the only line or product waiting  for this order */
		$Query .= "and   not exists ( select p3.pick_label_no 
                           from pick_item p3 
                           where p3.pick_order = p1.pick_order 
                           and   p3.pick_line_status in ('OP','UP')
		           and   ( p3.prod_id <> p1.prod_id ) ) ";
/* 2 if this product is a component line for a kit
   then must ensure that there are no AS status lines in the order for the kit
*/
		$Query .= "and   not exists ( select p4.pick_label_no 
                           from pick_item p4 
                           where p4.pick_order = p1.pick_order 
                           and   p4.pick_line_status in ('AS')
		           and   p4.parent_label_no = p1.parent_label_no   
		           and   (p4.prod_id <> p1.prod_id)    
		           and   ( p4.parent_label_no is not null ) ) ";

		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Orders Count!<br>\n");
			exit();
		}
		while (($Row5 = ibase_fetch_row($Result))) {
			if (!is_null($Row5[0])) {
				$toPickQty = $Row5[0];
			}
		}		

		//release memory
		ibase_free_result($Result);

	setBDCScookie($Link, $tran_device, "pick_qty", $toPickQty );
	//echo('to pick qty:' . print_r($toPickQty,true));
	return $toPickQty;
}

// ============================================================================================================
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
if (isset($_POST['selorder']))
{
	$order = $_POST['selorder'];
}
if (isset($_GET['selorder']))
{
	$order = $_GET['selorder'];
}
if (isset($_POST['line']))
{
	$line = $_POST['line'];
}
if (isset($_GET['line']))
{
	$line = $_GET['line'];
}
if (isset($_POST['selline']))
{
	$line = $_POST['selline'];
}
if (isset($_GET['selline']))
{
	$line = $_GET['selline'];
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
// if printer not specified then try one saved in the session table
if (!isset($printer))
{
	$printer = getBDCScookie($Link, $tran_device, "printer" );
	if ($printer == "")
	{
		unset($printer);
	}
}
// if none then use PA
if (!isset($printer))
{
	$printer = "";
	$wk_current_wh_id  = getBDCScookie($Link, $tran_device, "CURRENT_WH_ID" );
	// use warehouse.default_receive_printer using the current wh_id
	$Query = "select default_receive_printer  from warehouse where wh_id = '" . $wk_current_wh_id . "' "; 
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Warehouse!<br>\n");
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
			echo("Unable to Read Control!<br>\n");
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
if (isset($_POST['problem']))
{
	$problem = $_POST['problem'];
}
if (isset($_GET['problem']))
{
	$problem = $_GET['problem'];
}

if (isset($grn))
{
	$Query = "select  g1.wh_id from  grn g1  where g1.grn = '$grn' "; 
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read GRN!<br>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		//$default_comp = $Row[2];
		//$default_supplier = $Row[3];
		$default_wh_id = $Row[0];
                if ($default_wh_id == "")
		{
			$default_wh_id = $wk_current_wh_id ;
		}

		//$received_qty = getBDCScookie($Link, $tran_device, "received_qty" );

		setBDCScookie($Link, $tran_device, "WH_ID", $default_wh_id);

	}
	//release memory
	ibase_free_result($Result);
}

$Query = "select receive_direct_delivery, receive_issn_original_qty, use_sale_channel  from control  "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Control!<br>\n");
	exit();
}
$wkDirectDelivery = "F";
$wkDirectDeliveryDefaultIssnQty = null;
$wkSaleChannel = "F";
while ( ($Row = ibase_fetch_row($Result)) ) {
	$wkDirectDelivery = $Row[0];
	$wkDirectDeliveryDefIssnQty = $Row[1];
	$wkSaleChannel = $Row[2];
}
//release memory
ibase_free_result($Result);
if (is_null($wkDirectDelivery )) {
	$wkDirectDelivery = "F";
}
if (is_null($wkSaleChannel )) {
	$wkSaleChannel = "F";
}

$wkOrderSaleChannel = "NONE";
if ($wkSaleChannel == "T") {
	$Query = "select purchase_order.po_sale_channel_code  from purchase_order  where purchase_order.purchase_order = '" . $order . "' "; 
	$Query = "select purchase_order_line.po_sale_channel_code  from purchase_order_line  where purchase_order_line.purchase_order = '" . $order . "' and purchase_order_line.po_line = '" . $line . "' "; 
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read PO Order Channel!<br>\n");
		echo("in get sale channel !<br>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkOrderSaleChannel = $Row[0];
	}
	//release memory
	ibase_free_result($Result);
	if (is_null($wkOrderSaleChannel )) {
		$wkOrderSaleChannel = "NONE";
	}
	if ($wkOrderSaleChannel == "") {
		$wkOrderSaleChannel = "NONE";
	}
} 

$Query = "select purchase_order_line.prod_id, purchase_order_line.po_line_qty, prod_profile.short_desc, prod_profile.uom , purchase_order.company_id  from purchase_order join purchase_order_line on purchase_order.purchase_order = purchase_order_line.purchase_order   left outer join prod_profile on purchase_order_line.prod_id = prod_profile.prod_id where purchase_order.purchase_order = '" . $order . "' and purchase_order_line.po_line = '" . $line . "' "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read PO Line!<br>\n");
	exit();
}
$product_uom = "EA";
while ( ($Row = ibase_fetch_row($Result)) ) {
	$product = $Row[0];
	$product_qty = $Row[1];
	$product_desc = $Row[2];
	$product_uom = $Row[3];
	$product_company = $Row[4];
}
//release memory
ibase_free_result($Result);
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
		echo("Unable to Read PO Order Reservation!<br>\n");
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
/*
	$Query = "select record_id,pr_available_qty,pr_reserved_qty  from prod_reservation where pr_prod_id='" . $product . "' and pr_sale_channel_code<>'" . $wkOrderSaleChannel . "' and pr_reservation_status = 'OP' "; 
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read PO Order Reservation Others!<br>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkPRRecordId2 = 0;
		$wkPRAvailableQty2 = 0;
		$wkPRReserveQty2 = 0;
		$wkPRRecordId2 = $Row[0];
		$wkPRAvailableQty2 = $Row[1];
		$wkPRReserveQty2 = $Row[2];
		if (is_null($wkPRRecordID2 )) {
			$wkPRRecordID2 = 0 ;
		}
		if (is_null($wkPRAvailableQty2 )) {
			$wkPRAvailableQty2 = 0 ;
		}
		if (is_null($wkPRReserveQty2 )) {
			$wkPRReserveQty2 = 0 ;
		}
		$wkIssn = array();
		$wkIssn['PR_RECORD_ID']  = $wkPRRecordId2;
		$wkIssn['PR_AVAILABLE_QTY']  = $wkPRAvailableQty2;
		$wkIssn['PR_RESERVED_QTY']  = $wkPRReserveQty2;
		-* $wkPRRecord[] = array(
			$wkPRRecordID2  ,
			$wkPRAvailableQty2  ,
			$wkPRReserveQty2 ); *-
		$wkPRRecord[] = array($wkIssn);
	}
	//release memory
	ibase_free_result($Result);
*/
} 
//echo("<FONT size=\"2\">\n");
echo("<div id=\"col3\">");
echo("<form method=\"post\" name=getprodqtys id=\"getprodqtys\" onsubmit=\"return processEdit();\" >");
if (isset($type))
{
	echo("<input type=\"hidden\" name=\"type\" value=\"$type\" >");
}
echo("<input type=\"hidden\" name=\"complete\" >");

echo("<input type=\"text\" name=\"message\" readonly size=\"30\" class=\"message\"><br>\n");
switch ($type )
{
	case "PO":
		echo($type . ".No.");
		break;
	case "RA":
		echo("Return No.");
		break;
	case "TR":
		echo("TR No.");
		break;
	case "WO":
		echo($type . ".No.");
		break;
	case "LD":
		echo("Load No.");
		break;
	case "LP":
		echo("Load No.");
		break;
}
//echo("<input type=\"text\" name=\"order\" maxlength=\"10\" size=\"10\" readonly value=\"$order\" class=\"noread\">\n");
echo("<input type=\"text\" name=\"order\" maxlength=\"20\" size=\"17\" readonly value=\"$order\" class=\"noread\">\n");
echo("<input type=\"text\" name=\"line\" maxlength=\"4\" size=\"4\" readonly value=\"$line\" class=\"noread\"><br>\n");
if (isset ($product)) {
	echo("Prod No<input type=\"text\" name=\"product\" readonly value=\"$product\" class=\"noread\" >\n");
} else {
	echo("Prod No<input type=\"text\" name=\"product\" readonly value=\"\" class=\"noread\" >\n");
}
if (isset ($product_desc)) {
	//echo("<input type=\"text\" name=\"product_desc\" size=\"40\" readonly value=\"$product_desc\" class=\"noread\" >\n");
	echo("<input type=\"text\" name=\"product_desc\" size=\"40\" readonly value=\"" . htmlspecialchars($product_desc) . "\" class=\"noread\" >\n");
} else {
	echo("<input type=\"text\" name=\"product_desc\" size=\"40\" readonly value=\"\" class=\"noread\" >\n");
}
if (isset ($product_qty)) {
	echo("<br>Due:<input type=\"text\" name=\"product_qty\" size=\"4\" readonly value=\"$product_qty\" class=\"noread\" >\n");
} else {
	echo("<br>Due:<input type=\"text\" name=\"product_qty\" size=\"4\" readonly value=\"0\" class=\"noread\" >\n");
}
echo("UOM:<select name=\"uom\" class=\"sel6\">\n");
$Query = "select code, description from uom order by description "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Uom!<br>\n");
	exit();
}
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] == $product_uom)
	{
		echo( "<option value=\"$Row[0]\" selected >$Row[1]\n");
	}
	else
	{
		echo( "<option value=\"$Row[0]\">$Row[1]\n");
	}
}
//release memory
ibase_free_result($Result);
echo("</select><br>\n");
//echo("</div >\n");
echo("<div ID=\"col14\">\n");
echo("Recd :<input type=\"text\" name=\"received_ssn_qty\" size=\"4\" maxlength=\"4\" value=\"$received_ssn_qty\" class=\"default\"");
echo(" onfocus=\"document.getprodqtys.received_ssn_qty.value=strEmpty\" ");
if ($wkDirectDelivery == "T") 
{
	echo(" onchange=\"changeQty()\" ");
}
echo(">\n");
echo("</div >\n");
echo("<div ID=\"col13\">\n");
echo("Ptr:<select name=\"printer\" class=\"sel4\">\n");
$Query = "select device_id from sys_equip where device_type = 'PR' order by device_id "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Devices!<br>\n");
	exit();
}
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] == $printer)
	{
		echo( "<option value=\"$Row[0]\" selected >$Row[0]\n");
	}
	else
	{
		echo( "<option value=\"$Row[0]\">$Row[0]\n");
	}
}
//release memory
ibase_free_result($Result);
echo("</select><br>\n");
echo("</div>\n");
echo("<div id=\"col15\">");
if ($wkDirectDelivery == "T") 
{
	if (isset($product))
	{
		/*
		$wk_ToPickQty = getLines4Picks ($Link, $product, $product_company );
		$wk_ToPickQty = getLines4Picks ($Link, 
                                                $product, 
                                                $product_company ,
                                                $wkSaleChannel, 
                                                $wkOrderSaleChannel,
                                                $wkPRRecordId);
		*/
		$wk_ToPickQty = getLines4Picks ($Link, 
                                                $product, 
                                                $product_company ,
                                                $wkSaleChannel, 
                                                $wkOrderSaleChannel,
                                                $wkPRRecord);
	} else {
		$wk_ToPickQty = 0;
	}
/*
	echo("Qty Labels X Qty/ISSN Label:<br><input type=\"text\" name=\"dlabel_qty1\" maxlength=\"4\" size=\"3\" class=\"sel15\" readonly value=\"1\">\n");
	echo("X<input type=\"text\" name=\"dssn_qty1\" maxlength=\"4\" size=\"3\" class=\"sel15\" readonly>\n");
	echo("+<input type=\"text\" name=\"dlabel_qty2\" maxlength=\"4\" size=\"3\" class=\"sel15\" readonly>\n");
	echo("X<input type=\"text\" name=\"dssn_qty2\" maxlength=\"4\" size=\"3\" class=\"sel15\" readonly value=\"$wkDirectDeliveryDefIssnQty\"><br>\n");
*/
	//echo("Qty Labels X Qty/ISSN Label:<br><input type=\"text\" name=\"dlabel_qty1\" maxlength=\"4\" size=\"3\" class=\"sel15\" readonly >\n");
	echo("Qty Labels X Qty/ISSN Label:<br><input type=\"text\" name=\"label_qty1\" maxlength=\"4\" size=\"3\" class=\"sel5\"  >\n");
	//echo("X<input type=\"text\" name=\"dssn_qty1\" maxlength=\"4\" size=\"3\" class=\"sel15\" readonly value=\"$wkDirectDeliveryDefIssnQty\">\n");
	echo("X<input type=\"text\" name=\"ssn_qty1\" maxlength=\"4\" size=\"3\" class=\"sel5\" >\n");
/*
	echo("+<input type=\"text\" name=\"dlabel_qty2\" maxlength=\"4\" size=\"3\" class=\"sel15\" readonly >\n");
	echo("X<input type=\"text\" name=\"dssn_qty2\" maxlength=\"4\" size=\"3\" class=\"sel15\" readonly >\n");
*/
	echo("<input type=\"hidden\" name=\"dssn_qty2\" >\n");
	echo("<input type=\"hidden\" name=\"dlabel_qty2\" >\n");
	echo("<input type=\"hidden\" name=\"dssn_qty1\" >\n");
	echo("<input type=\"hidden\" name=\"dlabel_qty1\" >\n");
	echo("+PK<input type=\"text\" name=\"dlabel_qty3\" maxlength=\"4\" size=\"3\" class=\"sel15\" readonly value=\"1\">\n");
	echo("X<input type=\"text\" name=\"dssn_qty3\" maxlength=\"4\" size=\"3\" class=\"sel15\" readonly ><br>\n");

	//echo("<input type=\"hidden\" name=\"label_qty1\" value=\"1\">\n");
	//echo("<input type=\"hidden\" name=\"ssn_qty1\" >\n");
	echo("<input type=\"hidden\" name=\"label_qty2\" >\n");
	//echo("<input type=\"hidden\" name=\"ssn_qty2\"  value=\"$wkDirectDeliveryDefIssnQty\">\n");
	echo("<input type=\"hidden\" name=\"ssn_qty2\"  >\n");
	echo("<input type=\"hidden\" name=\"label_qty3\" value=\"1\">\n");
	echo("<input type=\"hidden\" name=\"ssn_qty3\" >\n");
} else {
	$wk_ToPickQty = 0;
	echo("Qty Labels X Qty/ISSN Label:<br><input type=\"text\" name=\"label_qty1\" maxlength=\"4\" size=\"3\" class=\"sel5\">\n");
	echo("X<input type=\"text\" name=\"ssn_qty1\" maxlength=\"4\" size=\"3\" class=\"sel5\">\n");
	echo("+<input type=\"text\" name=\"label_qty2\" maxlength=\"4\" size=\"3\" class=\"sel5\">\n");
	echo("X<input type=\"text\" name=\"ssn_qty2\" maxlength=\"4\" size=\"3\" class=\"sel5\"><br>\n");
	echo("<input type=\"hidden\" name=\"label_qty3\" >\n");
	echo("<input type=\"hidden\" name=\"ssn_qty3\" >\n");
}
echo("Location:<select name=\"location\" class=\"sel8\">\n");
//$Query = "select wh_id, locn_id, locn_name from location where store_area = 'RC' order by locn_name "; 
$Query = "select ln.wh_id, ln.locn_id, ln.locn_name from location ln join session on session.device_id = '" . $tran_device . "' and session.code='CURRENT_WH_ID' where ln.wh_id = session.description and ln.store_area = 'RC' order by ln.locn_name " ; 
$Query = "select ln.wh_id, ln.locn_id, ln.locn_name from location ln  where ln.wh_id = '" . $default_wh_id . "' and ln.store_area = 'RC' order by ln.locn_id,ln.locn_name " ; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Locations!<br>\n");
	exit();
}
while ( ($Row = ibase_fetch_row($Result)) ) {
	echo( "<option value=\"$Row[0]$Row[1]\">$Row[2]\n");
}
//release memory
ibase_free_result($Result);
//echo("</select><br>\n");
echo("</select>\n");
echo("WH<input name=\"WH_ID\" readonly value=\"" . $default_wh_id . "\" size=\"2\"><br>\n");
echo("Problem:<input type=\"checkbox\" name=\"problem\" value=\"T\"");
echo(" onchange=\"processEdit();\"");
if ($problem > "")
{
	echo(" checked ");
}
//echo(" ><br>");
echo(" >");
echo("<input name=\"retfrom\" type=\"hidden\" value=\"\">\n");
echo("</div>\n");
echo("<div>\n");
	$wk_label_posn = 0;
	$wk_menu_output = "IMAGE";
//echo("</form>");
{
	// html 4.0 browser
 	//echo("<table border=\"0\" align=\"LEFT\" id=\"col11\">\n");
	//whm2buttons('Accept', './getPOLine.php', "N","Back_50x100.gif","Back","accept.gif","N" );
	$alt = "Accept";
	// Create a table.
	echo ("<table border=\"0\" align=\"LEFT\" id=\"col11\">");
	echo ("<tbody>");
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
	echo("<form action=\"getPOLine.php\"  method=\"post\"  name=\"getssnback\" onsubmit=\"return processBack();\" id=\"getssnback\" >\n");
	echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/Back_50x100.gif"  alt="Back">');
	echo("</form>");
	echo ("</td>");
/*
14/07/2013
accept does the processedit javascript then seems to refresh
currently back button only works if the qty is entered - this runs the processedit javascript first
however comments works always
*/
/*
	//echo("<BUTTON name=\"back\" type=\"button\" id=\"col2\" onfocus=\"location.href='./receive_menu.php';\">\n");
	echo("<BUTTON name=\"back\" type=\"button\" id=\"col12\" onfocus=\"location.href='./getPO.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
	//echo ("<tr>");
	echo ("<td>");
	$alt = "Comments";
	echo("<form action=\"comments.php\"  method=\"post\" onsubmit=\"return processComment();\" name=getcomment id=\"getcomment\">\n");
/*
	echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
*/
	//echo("<input type=\"IMAGE\" ");  
	//echo('SRC="/icons/whm/comment.gif" alt="' . $alt . '">');
	addScreenButton("getcomment","/icons/whm/comment.gif",$alt);
	echo("<input type=\"hidden\" name=\"type\" value=\"$type\" >");
	echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	echo("<input type=\"hidden\" name=\"order\" value=\"$order\">\n");
	echo("<input type=\"hidden\" name=\"line\" value=\"$line\">\n");
	echo("<input type=\"hidden\" name=\"retfrom\" >\n");
	echo("<input type=\"hidden\" name=\"owner\" >\n");
	echo("<input type=\"hidden\" name=\"received_ssn_qty\" >\n");
	echo("<input type=\"hidden\" name=\"location\" >\n");
	echo("<input type=\"hidden\" name=\"printer\" >\n");
	echo("<input type=\"hidden\" name=\"problem\" >\n");
	echo("<input type=\"hidden\" name=\"uom\" >\n");
	echo("<input type=\"hidden\" name=\"product\" >\n");
	echo("<input type=\"hidden\" name=\"label_qty1\" >\n");
	echo("<input type=\"hidden\" name=\"label_qty2\" >\n");
	echo("<input type=\"hidden\" name=\"ssn_qty1\" >\n");
	echo("<input type=\"hidden\" name=\"ssn_qty2\" >\n");
	echo("<input type=\"hidden\" name=\"label_qty3\" >\n");
	echo("<input type=\"hidden\" name=\"ssn_qty3\" >\n");
	echo("<input type=\"hidden\" name=\"from\" value=\"verifyPO.php\" >\n");
	echo("</form>");
	echo ("</td>");
	echo ("</tr>");
	echo ("</tbody>");
	echo ("</table>");
}
echo("</div>\n");
echo("<form action=\"transPONV.php\" method=\"post\" name=goforw id=\"goforw\" >");
echo("<input type=\"hidden\" name=\"type\" value=\"$type\" >");
echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\" >");
echo("<input type=\"hidden\" name=\"complete\" >");
echo("<input type=\"hidden\" name=\"order\" value=\"$order\">\n");
echo("<input type=\"hidden\" name=\"line\" value=\"$line\">\n");
echo("<input type=\"hidden\" name=\"uom\" >\n");
echo("<input type=\"hidden\" name=\"retfrom\" >\n");
echo("<input type=\"hidden\" name=\"received_ssn_qty\" >\n");
echo("<input type=\"hidden\" name=\"location\" >\n");
echo("<input type=\"hidden\" name=\"product\" >\n");
echo("<input type=\"hidden\" name=\"printer\" >\n");
echo("<input type=\"hidden\" name=\"label_qty1\" >\n");
echo("<input type=\"hidden\" name=\"label_qty2\" >\n");
echo("<input type=\"hidden\" name=\"ssn_qty1\" >\n");
echo("<input type=\"hidden\" name=\"ssn_qty2\" >\n");
echo("<input type=\"hidden\" name=\"ssn_qty3\" >\n");
echo("<input type=\"hidden\" name=\"label_qty3\" >\n");
echo("<input type=\"hidden\" name=\"problem\" >\n");
echo("</form>");
//echo("</div>\n");
//echo("<div id=\"getcomplete2\">");
//	confirm order complete
	//echo("<form action=\"pocomplete.php\"  method=\"get\" name=getcomplete1>\n");
	echo("<form action=\"pocomplete.php\"  method=\"post\" name=getcomplete1 id=\"getcomplete1\">\n");
	echo("<input type=\"hidden\" name=\"type\" value=\"$type\" >");
	echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	echo("<input type=\"hidden\" name=\"order\" value=\"$order\">\n");
	echo("<input type=\"hidden\" name=\"line\" value=\"$line\">\n");
	echo("<input type=\"hidden\" name=\"retfrom\" >\n");
	echo("<input type=\"hidden\" name=\"owner\" >\n");
	echo("<input type=\"hidden\" name=\"received_ssn_qty\" >\n");
	echo("<input type=\"hidden\" name=\"location\" >\n");
	echo("<input type=\"hidden\" name=\"printer\" >\n");
	echo("<input type=\"hidden\" name=\"problem\" >\n");
	echo("<input type=\"hidden\" name=\"uom\" >\n");
	echo("<input type=\"hidden\" name=\"product\" >\n");
	echo("<input type=\"hidden\" name=\"label_qty1\" >\n");
	echo("<input type=\"hidden\" name=\"label_qty2\" >\n");
	echo("<input type=\"hidden\" name=\"label_qty3\" >\n");
	echo("<input type=\"hidden\" name=\"ssn_qty1\" >\n");
	echo("<input type=\"hidden\" name=\"ssn_qty2\" >\n");
	echo("<input type=\"hidden\" name=\"ssn_qty3\" >\n");
	echo("<input type=\"hidden\" name=\"from\" value=\"verifyPO.php\" >\n");
	//echo("<input type=\"submit\" name=\"completesubmit\" id=\"completesubmit\" style=\"visibility:hidden\">\n");
	echo("</form>");
//echo("</div>\n");
?>
<script type="text/javascript">
<?php
{
	echo("var strEmpty = \"\";\n");
	echo("var doDirect = " . json_encode($wkDirectDelivery) . ";\n");
	echo("var issnDirectDefaultQty = " . json_encode($wkDirectDeliveryDefIssnQty) . ";\n");
	echo("var toPickQty = " . json_encode($wk_ToPickQty) . ";\n");
	echo("var processDirection = \"\" ;\n");
	if ($wkDirectDelivery == "T") 
	{
		echo("changeQty();\n");
	}
	echo("document.getprodqtys.message.value=\"Enter the Received Qty\";\n");
	echo("document.getprodqtys.received_ssn_qty.focus();\n");
}
?>
</script>
</body>
</html>
