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
 </head>
<script type="text/javascript">
function errorHandler(errorMessage,url,line) 
{
	document.write("<p><b>Error in JS:</b> "+errorMessage+"<br>")
	document.write("<b>URL:</b> "+url+"<br>")
	document.write("<b>Line:</b> "+line+"</p>")
	return true
}
function showComplete2() {
	/* document.all.item("col3").style.display="none"; 
	document.all.item("getcomplete2").style.display="block"; 
	document.all.item("col7").style.display="none";  */
	document.getcomplete1.retfrom.value = document.getprodqtys.retfrom.value ;
	document.getcomplete1.received_ssn_qty.value = document.getprodqtys.received_ssn_qty.value ;
	document.getcomplete1.printed_ssn_qty.value = document.getprodqtys.printed_ssn_qty.value ;
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
	document.getcomplete1.weight_qty1.value = document.getprodqtys.weight_qty1.value ;
	document.getcomplete1.weight_uom.value = document.getprodqtys.weight_uom.value ;
	document.getcomplete1.submit();
}
function doproduct() {
// product changed 
	document.getproduct.product.value=document.getprodqtys.product.value;
	processProdGetSupplier();
	return true;
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
function processEdit() {
	var dowhat;
  	var csum;
  /* document.getprodqtys.message.value="in process edit"; */
  if ( document.getprodqtys.product.value=="")
  {
  	document.getprodqtys.message.value="Must Enter the Product";
	document.getprodqtys.product.focus();
  	return false;
  }
  if ( document.getprodqtys.uom.value=="")
  {
  	document.getprodqtys.message.value="Must Select the UoM";
	document.getprodqtys.uom.focus();
  	return false;
  }
  if ( document.getprodqtys.retfrom.value=="")
  {
  	document.getprodqtys.message.value="Must Select the Sent From";
	document.getprodqtys.retfrom.focus();
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
  if ( document.getprodqtys.weight_qty1.value=="")
  {
	document.getprodqtys.weight_qty1.value = "0";
	/* no weight */
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
  if ( chkNumeric(document.getprodqtys.weight_qty1.value)==false)
  {
  	document.getprodqtys.message.value="Weight Not Numeric";
	document.getprodqtys.weight_qty1.focus();
  	return false;
  }
  if ( document.getprodqtys.weight_uom.value=="")
  {
  	document.getprodqtys.message.value="Must Select the UoM";
	document.getprodqtys.weight_uom.focus();
  	return false;
  }
	csum = (document.getprodqtys.label_qty1.value * document.getprodqtys.ssn_qty1.value) + 
	(document.getprodqtys.printed_ssn_qty.value * 1);
	if (csum > document.getprodqtys.received_ssn_qty.value)
	{
  		document.getprodqtys.message.value="Qty dosn't Add Up";
		document.getprodqtys.label_qty1.focus();
  		return false;
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
	document.goforw.printed_ssn_qty.value = document.getprodqtys.printed_ssn_qty.value ;
	document.goforw.location.value = document.getprodqtys.location.value ;
	document.goforw.printer.value = document.getprodqtys.printer.value ;
	document.goforw.label_qty1.value = document.getprodqtys.label_qty1.value ;
	document.goforw.ssn_qty1.value = document.getprodqtys.ssn_qty1.value ;
	if (document.getprodqtys.problem.checked == true)
	{
		document.goforw.problem.value = "T" ;
	}
	else
	{
		document.goforw.problem.value = "" ;
	}
	document.goforw.weight_qty1.value = document.getprodqtys.weight_qty1.value ;
	document.goforw.weight_uom.value = document.getprodqtys.weight_uom.value ;
	document.goforw.submit();
  }
  return false;
}
function processComment() {
	var dowhat;
	document.getcomment.retfrom.value = document.getprodqtys.retfrom.value ;
	document.getcomment.received_ssn_qty.value = document.getprodqtys.received_ssn_qty.value ;
	document.getcomment.printed_ssn_qty.value = document.getprodqtys.printed_ssn_qty.value ;
	document.getcomment.location.value = document.getprodqtys.location.value ;
	document.getcomment.printer.value = document.getprodqtys.printer.value ;
	if (document.getprodqtys.problem.checked == true)
	{
		document.getcomment.problem.value = "T" ;
	}
	else
	{
		document.getcomment.problem.value = "" ;
	}
	document.getcomment.uom.value = document.getprodqtys.uom.value ;
	document.getcomment.product.value = document.getprodqtys.product.value ;
	document.getcomment.label_qty1.value = document.getprodqtys.label_qty1.value ;
	document.getcomment.ssn_qty1.value = document.getprodqtys.ssn_qty1.value ;
	document.getcomment.weight_qty1.value = document.getprodqtys.weight_qty1.value ;
	document.getcomment.weight_uom.value = document.getprodqtys.weight_uom.value ;
	document.getcomment.submit();
  return true;
}
function processProdSearch() {
	document.getprodsrch.retfrom.value = document.getprodqtys.retfrom.value ;
	document.getprodsrch.received_ssn_qty.value = document.getprodqtys.received_ssn_qty.value ;
	document.getprodsrch.printed_ssn_qty.value = document.getprodqtys.printed_ssn_qty.value ;
	document.getprodsrch.location.value = document.getprodqtys.location.value ;
	document.getprodsrch.printer.value = document.getprodqtys.printer.value ;
	if (document.getprodqtys.problem.checked == true)
	{
		document.getprodsrch.problem.value = "T" ;
	}
	else
	{
		document.getprodsrch.problem.value = "" ;
	}
	document.getprodsrch.uom.value = document.getprodqtys.uom.value ;
	document.getprodsrch.product.value = document.getprodqtys.product.value ;
	document.getprodsrch.label_qty1.value = document.getprodqtys.label_qty1.value ;
	document.getprodsrch.ssn_qty1.value = document.getprodqtys.ssn_qty1.value ;
	document.getprodsrch.weight_qty1.value = document.getprodqtys.weight_qty1.value ;
	document.getprodsrch.weight_uom.value = document.getprodqtys.weight_uom.value ;
	document.getprodsrch.submit();
}
function processProd() {
	var wk1;
	wk1 = document.getproduct.grn.value ;
	if (document.getprodqtys.problem.checked == true)
	{
		wk1 += "|T" ;
	}
	document.getproduct.grn.value = wk1 ;
	document.getproduct.submit();
  return true;
}
function processProdSupplier() {
	var dowhat;
	/* clicked add supplier */
	if (document.getprodqtys.product.value=="") 
	{
		document.getprodqtys.message.value = "Must enter Product First";
		/* alert( "Must enter Product First"); */
		return false;
	}
	/* populate getprodsupplier and submit form to add supplier 
	dowhat = document.getprodqtys.retfrom.selectedIndex ;
	document.getprodsupplier.retfrom.value = document.getprodqtys.retfrom.options[dowhat].value ;
	dowhat = document.getprodqtys.uom.selectedIndex ;
	document.getprodsupplier.uom.value = document.getprodqtys.uom.options[dowhat].value ;
	dowhat = document.getprodqtys.printer.selectedIndex ;
	document.getprodsupplier.printer.value = document.getprodqtys.printer.options[dowhat].value ;
	dowhat = document.getprodqtys.location.selectedIndex ;
	document.getprodsupplier.location.value = document.getprodqtys.location.options[dowhat].value ;
	dowhat = document.getprodqtys.weight_uom.selectedIndex ;
	document.getprodsupplier.weight_uom.value = document.getprodqtys.weight_uom.options[dowhat].value ; */

	document.getprodsupplier.retfrom.value = document.getprodqtys.retfrom.value ;
	document.getprodsupplier.printer.value = document.getprodqtys.printer.value ;
	document.getprodsupplier.uom.value = document.getprodqtys.uom.value ;
	document.getprodsupplier.location.value = document.getprodqtys.location.value ;
	document.getprodsupplier.weight_uom.value = document.getprodqtys.weight_uom.value ; 

	document.getprodsupplier.received_ssn_qty.value = document.getprodqtys.received_ssn_qty.value ;
	document.getprodsupplier.printed_ssn_qty.value = document.getprodqtys.printed_ssn_qty.value ;
	if (document.getprodqtys.problem.checked == true)
	{
		document.getprodsupplier.problem.value = "T" ;
	}
	else
	{
		document.getprodsupplier.problem.value = "" ;
	}
	document.getprodsupplier.product.value = document.getprodqtys.product.value ;
	document.getprodsupplier.label_qty1.value = document.getprodqtys.label_qty1.value ;
	document.getprodsupplier.ssn_qty1.value = document.getprodqtys.ssn_qty1.value ;
	document.getprodsupplier.weight_qty1.value = document.getprodqtys.weight_qty1.value ;
	document.getprodsupplier.submit();
}
function processProdGetSupplier() {
	var dowhat;
	/* save all the fields then refresh the screen to populate the prod_supplier for this prod 
	dowhat = document.getprodqtys.retfrom.selectedIndex ;
	document.getproduct2.retfrom.value = document.getprodqtys.retfrom.options[dowhat].value ;
	dowhat = document.getprodqtys.uom.selectedIndex ;
	document.getproduct2.uom.value = document.getprodqtys.uom.options[dowhat].value ;
	dowhat = document.getprodqtys.printer.selectedIndex ;
	document.getproduct2.printer.value = document.getprodqtys.printer.options[dowhat].value ;
	dowhat = document.getprodqtys.location.selectedIndex ;
	document.getproduct2.location.value = document.getprodqtys.location.options[dowhat].value ;
	dowhat = document.getprodqtys.weight_uom.selectedIndex ;
	document.getproduct2.weight_uom.value = document.getprodqtys.weight_uom.options[dowhat].value ; */
	document.getproduct2.received_ssn_qty.value = document.getprodqtys.received_ssn_qty.value ;
	document.getproduct2.printed_ssn_qty.value = document.getprodqtys.printed_ssn_qty.value ;
	document.getproduct2.retfrom.value = document.getprodqtys.retfrom.value ;
	document.getproduct2.location.value = document.getprodqtys.location.value ;
	document.getproduct2.printer.value = document.getprodqtys.printer.value ; 
	document.getproduct2.uom.value = document.getprodqtys.uom.value ; 
	document.getproduct2.weight_uom.value = document.getprodqtys.weight_uom.value ; 
	if (document.getprodqtys.problem.checked == true)
	{
		document.getproduct2.problem.value = "T" ;
	}
	else
	{
		document.getproduct2.problem.value = "" ;
	}
	document.getproduct2.product.value = document.getprodqtys.product.value ;
	document.getproduct2.label_qty1.value = document.getprodqtys.label_qty1.value ;
	document.getproduct2.ssn_qty1.value = document.getprodqtys.ssn_qty1.value ;
	document.getproduct2.weight_qty1.value = document.getprodqtys.weight_qty1.value ;
	document.getproduct2.submit();
}
/* 
once the product is changed 
then the supplier list must be recalulated using the entered product
*/
onerror = errorHandler
</script>
<?php
require_once 'DB.php';
require 'db_access.php';
include 'logme.php';

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	
$received_ssn_qty = 0;
$printed_ssn_qty = 0;
$retfrom = "";
$uom = "EA";
$weight_uom = "KG";
$location = "";
$product = "";
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<br>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
if (isset($_COOKIE['BDCSData']))
{
	list($received_qty, $type, $order, $line, $carrier, $vehicle, $container, $pallet_type, $pallet_qty, $consignment, $grn, $problem) = explode("|", $_COOKIE["BDCSData"]);
}
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

	$retfrom = getBDCScookie($Link, $tran_device, "retfrom" );
	$product = getBDCScookie($Link, $tran_device, "product" );
	$label_qty1 = getBDCScookie($Link, $tran_device, "label_qty1" );
	$ssn_qty1 = getBDCScookie($Link, $tran_device, "ssn_qty1" );
	$weight_qty1 = getBDCScookie($Link, $tran_device, "weight_qty1" );
	$weight_uom = getBDCScookie($Link, $tran_device, "weight_uom" );
	$printer = getBDCScookie($Link, $tran_device, "printer" );
	$received_ssn_qty = getBDCScookie($Link, $tran_device, "received_ssn_qty" );
	$location = getBDCScookie($Link, $tran_device, "location" );
	$uom = getBDCScookie($Link, $tran_device, "uom" );
	$printed_ssn_qty = getBDCScookie($Link, $tran_device, "printed_ssn_qty" );
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
	if (strpos($_POST['grn'], '|') === false)
	{
		$grn = $_POST['grn'];
	}
	else
	{
		list($grn, $problem) = explode('|', $_POST['grn']);
	}
}
if (isset($_GET['grn']))
{
	if (strpos($_GET['grn'], '|') === false)
	{
		$grn = $_GET['grn'];
	}
	else
	{
		list($grn, $problem) = explode('|', $_GET['grn']);
	}
}
if (isset($_POST['printer']))
{
	$printer = $_POST['printer'];
}
if (isset($_GET['printer']))
{
	$printer = $_GET['printer'];
}
if (!isset($printer))
{
	$printer = "";
	$wk_current_wh_id  = getBDCScookie($Link, $tran_device, "CURRENT_WH_ID" );
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
if (isset($_POST['problem']))
{
	$problem = $_POST['problem'];
}
if (isset($_GET['problem']))
{
	$problem = $_GET['problem'];
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
if (isset($_POST['retfrom']))
{
	$retfrom = $_POST['retfrom'];
}
if (isset($_GET['retfrom']))
{
	$retfrom = $_GET['retfrom'];
}
if (isset($_POST['uom']))
{
	$uom = $_POST['uom'];
}
if (isset($_GET['uom']))
{
	$uom = $_GET['uom'];
}
if (isset($_POST['location']))
{
	$location = $_POST['location'];
}
if (isset($_GET['location']))
{
	$location = $_GET['location'];
}

echo("<FONT size=\"2\">\n");

//echo("<div id=\"goforward\">");
	echo("<form action=\"transLPNVQ.php\" method=\"post\" name=goforw >");
	echo("<INPUT type=\"hidden\" name=\"type\" value=\"$type\" >");
	echo("<INPUT type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	echo("<INPUT type=\"hidden\" name=\"complete\" >");
	echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\">\n");
	echo("<INPUT type=\"hidden\" name=\"line\" value=\"$line\">\n");
	echo("<INPUT type=\"hidden\" name=\"retfrom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"uom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"product\" >\n");
	echo("<INPUT type=\"hidden\" name=\"received_ssn_qty\" >\n");
	echo("<INPUT type=\"hidden\" name=\"printed_ssn_qty\" >\n");
	echo("<INPUT type=\"hidden\" name=\"location\" >\n");
	echo("<INPUT type=\"hidden\" name=\"printer\" >\n");
	echo("<INPUT type=\"hidden\" name=\"label_qty1\" >\n");
	echo("<INPUT type=\"hidden\" name=\"label_qty2\" >\n");
	echo("<INPUT type=\"hidden\" name=\"ssn_qty1\" >\n");
	echo("<INPUT type=\"hidden\" name=\"ssn_qty2\" >\n");
	echo("<INPUT type=\"hidden\" name=\"problem\" >\n");
	echo("<INPUT type=\"hidden\" name=\"weight_uom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"weight_qty1\" >\n");
	echo("</form>");
//echo("</div>\n");
//echo("<div id=\"search1\">");
//	prod search
	$alt = "Search";
	echo("<form action=\"prodsrch.php\"  method=\"post\" name=getprodsrch>\n");
//	echo("<INPUT type=\"IMAGE\" ");  
//	echo('SRC="/icons/unknown.gif"');
//	echo(' alt="' . $alt . '">');
	echo("<INPUT type=\"hidden\" name=\"type\" value=\"$type\" >");
	echo("<INPUT type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\">\n");
	echo("<INPUT type=\"hidden\" name=\"line\" value=\"$line\">\n");
	echo("<INPUT type=\"hidden\" name=\"retfrom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"owner\" >\n");
	echo("<INPUT type=\"hidden\" name=\"received_ssn_qty\" >\n");
	echo("<INPUT type=\"hidden\" name=\"printed_ssn_qty\" >\n");
	echo("<INPUT type=\"hidden\" name=\"location\" >\n");
	echo("<INPUT type=\"hidden\" name=\"printer\" >\n");
	echo("<INPUT type=\"hidden\" name=\"problem\" >\n");
	echo("<INPUT type=\"hidden\" name=\"uom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"product\" >\n");
	echo("<INPUT type=\"hidden\" name=\"label_qty1\" >\n");
	echo("<INPUT type=\"hidden\" name=\"label_qty2\" >\n");
	echo("<INPUT type=\"hidden\" name=\"ssn_qty1\" >\n");
	echo("<INPUT type=\"hidden\" name=\"ssn_qty2\" >\n");
	echo("<INPUT type=\"hidden\" name=\"from\" value=\"verifyLPweight.php\" >\n");
	echo("<INPUT type=\"hidden\" name=\"weight_uom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"weight_qty1\" >\n");
	echo("</form>");
//echo("</div>\n");
//echo("<div id=\"addsupplier1\">");
//	add a prod supplier
	$alt = "Add Supplier";
	echo("<form action=\"prodsupplier.php\"  method=\"post\" name=getprodsupplier>\n");
//	echo("<INPUT type=\"IMAGE\" ");  
//	echo('SRC="/icons/unknown.gif"');
//	echo(' alt="' . $alt . '">');
	echo("<INPUT type=\"hidden\" name=\"type\" value=\"$type\" >");
	echo("<INPUT type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\">\n");
	echo("<INPUT type=\"hidden\" name=\"line\" value=\"$line\">\n");
	echo("<INPUT type=\"hidden\" name=\"retfrom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"owner\" >\n");
	echo("<INPUT type=\"hidden\" name=\"received_ssn_qty\" >\n");
	echo("<INPUT type=\"hidden\" name=\"printed_ssn_qty\" >\n");
	echo("<INPUT type=\"hidden\" name=\"location\" >\n");
	echo("<INPUT type=\"hidden\" name=\"printer\" >\n");
	echo("<INPUT type=\"hidden\" name=\"problem\" >\n");
	echo("<INPUT type=\"hidden\" name=\"uom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"product\" >\n");
	echo("<INPUT type=\"hidden\" name=\"label_qty1\" >\n");
	echo("<INPUT type=\"hidden\" name=\"label_qty2\" >\n");
	echo("<INPUT type=\"hidden\" name=\"ssn_qty1\" >\n");
	echo("<INPUT type=\"hidden\" name=\"ssn_qty2\" >\n");
	echo("<INPUT type=\"hidden\" name=\"from\" value=\"verifyLPweight.php\" >\n");
	echo("<INPUT type=\"hidden\" name=\"weight_uom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"weight_qty1\" >\n");
	echo("</form>");
//echo("</div>\n");
//echo("<div id=\"getsupplier2\">");
//	changed product so resubmit screen so that supplier list works
	$alt = "Reget Product";
	echo("<form action=\"verifyLPweight.php\"  method=\"post\" name=getproduct2>\n");
//	echo("<INPUT type=\"IMAGE\" ");  
//	echo('SRC="/icons/unknown.gif"');
//	echo(' alt="' . $alt . '">');
	echo("<INPUT type=\"hidden\" name=\"type\" value=\"$type\" >");
	echo("<INPUT type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\">\n");
	echo("<INPUT type=\"hidden\" name=\"line\" value=\"$line\">\n");
	echo("<INPUT type=\"hidden\" name=\"retfrom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"owner\" >\n");
	echo("<INPUT type=\"hidden\" name=\"received_ssn_qty\" >\n");
	echo("<INPUT type=\"hidden\" name=\"printed_ssn_qty\" >\n");
	echo("<INPUT type=\"hidden\" name=\"location\" >\n");
	echo("<INPUT type=\"hidden\" name=\"printer\" >\n");
	echo("<INPUT type=\"hidden\" name=\"problem\" >\n");
	echo("<INPUT type=\"hidden\" name=\"uom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"product\" >\n");
	echo("<INPUT type=\"hidden\" name=\"label_qty1\" >\n");
	echo("<INPUT type=\"hidden\" name=\"label_qty2\" >\n");
	echo("<INPUT type=\"hidden\" name=\"ssn_qty1\" >\n");
	echo("<INPUT type=\"hidden\" name=\"ssn_qty2\" >\n");
	echo("<INPUT type=\"hidden\" name=\"from\" value=\"verifyLPweight.php\" >\n");
	echo("<INPUT type=\"hidden\" name=\"weight_uom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"weight_qty1\" >\n");
	echo("</form>");
//echo("</div>\n");
//echo("<div id=\"getcomplete2\">");
//	confirm order complete
	//echo("<form action=\"prodcomplete.php\"  method=\"get\" name=getcomplete1>\n");
	echo("<form action=\"prodcomplete.php\"  method=\"post\" name=getcomplete1>\n");
	echo("<INPUT type=\"hidden\" name=\"type\" value=\"$type\" >");
	echo("<INPUT type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\">\n");
	echo("<INPUT type=\"hidden\" name=\"line\" value=\"$line\">\n");
	echo("<INPUT type=\"hidden\" name=\"retfrom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"owner\" >\n");
	echo("<INPUT type=\"hidden\" name=\"received_ssn_qty\" >\n");
	echo("<INPUT type=\"hidden\" name=\"printed_ssn_qty\" >\n");
	echo("<INPUT type=\"hidden\" name=\"location\" >\n");
	echo("<INPUT type=\"hidden\" name=\"printer\" >\n");
	echo("<INPUT type=\"hidden\" name=\"problem\" >\n");
	echo("<INPUT type=\"hidden\" name=\"uom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"product\" >\n");
	echo("<INPUT type=\"hidden\" name=\"label_qty1\" >\n");
	echo("<INPUT type=\"hidden\" name=\"label_qty2\" >\n");
	echo("<INPUT type=\"hidden\" name=\"ssn_qty1\" >\n");
	echo("<INPUT type=\"hidden\" name=\"ssn_qty2\" >\n");
	echo("<INPUT type=\"hidden\" name=\"from\" value=\"verifyLPweight.php\" >\n");
	echo("<INPUT type=\"hidden\" name=\"weight_uom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"weight_qty1\" >\n");
	echo("</form>");
//echo("</div>\n");
echo("<div id=\"col3\">");
echo("<form method=\"post\" name=getprodqtys ONSUBMIT=\"return processEdit();\">");
if (isset($type))
{
	echo("<INPUT type=\"hidden\" name=\"type\" value=\"$type\" >");
}
echo("<INPUT type=\"hidden\" name=\"complete\" >");

echo("<INPUT type=\"text\" name=\"message\" readonly size=\"30\" class=\"message\"><br>\n");
switch ($type )
{
	case "PO":
		echo($type . ".No.:");
		break;
	case "RA":
		echo("Return No.:");
		break;
	case "TR":
		echo("TR No.:");
		break;
	case "WO":
		echo($type . ".No.:");
		break;
	case "LD":
		echo("Non-Product:");
		break;
	case "LP":
		echo("Load No.:");
		break;
}
echo("<INPUT type=\"text\" name=\"order\" maxlength=\"10\" size=\"10\" readonly value=\"$order\">\n");
//echo("<INPUT type=\"text\" name=\"line\" maxlength=\"4\" size=\"4\" readonly value=\"$line\"><br>\n");
echo("<INPUT type=\"hidden\" name=\"line\" value=\"$line\"><br>\n");
/* ===============================================================
echo("<div id=\"col7\">"); */
if (isset($product))
	echo("Product ID:<br><INPUT type=\"text\" name=\"product\" value=\"" . $product . "\" maxlength=\"30\" size=\"20\" onchange=\"doproduct();");
else
	echo("Product ID:<br><INPUT type=\"text\" name=\"product\" maxlength=\"30\" size=\"20\" onchange=\"doproduct();");
echo("processEdit();\" class=\"product\" >\n");
echo("<INPUT type=\"button\" name=\"search\" value=\"Search\" ");  
echo(' alt="Search" onclick="processProdSearch();" >');
echo("<br>\n"); 
/* =============================================================== */
/* echo("Load Sent From:<br>");
echo("<select name=\"retfrom\" class=\"sel3\">\n");
$Query = "select person_id, first_name, last_name from person where person_type in ('RP') or person_type starting 'C' order by first_name, last_name "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Returner!<br>\n");
	exit();
}
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($retfrom == $Row[0])
	{
		echo( "<OPTION value=\"$Row[0]\" selected>$Row[1] $Row[2]\n");
	}
	else
	{
		echo( "<OPTION value=\"$Row[0]\">$Row[1] $Row[2]\n");
	}
}
//release memory
ibase_free_result($Result);
//echo("</select><br>\n");
echo("</select>\n");
echo("<INPUT type=\"button\" name=\"addsupplier\" value=\"A\" ");  
echo(' alt="Add Supplier" onclick="processProdSupplier();" >');
echo("<br>\n"); */
/* ============================================ */
echo("Problem:<INPUT type=\"checkbox\" name=\"problem\" ");
echo(" onchange=\"processEdit();\"");
if ($problem > "")
{
	echo(" checked ");
}
echo(" ><br>");
echo("</div>");
echo("<div id=\"col6\">");
echo("UOM:<select name=\"uom\" class=\"sel6\">\n");
$Query = "select code, description from uom where uom_type='UT' order by description "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Uom!<br>\n");
	exit();
}
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] == $uom)
	{
		echo( "<OPTION value=\"$Row[0]\" selected >$Row[1]\n");
	}
	else
	{
		echo( "<OPTION value=\"$Row[0]\">$Row[1]\n");
	}
}
//release memory
ibase_free_result($Result);
echo("</select><br>\n");
echo("</div>");
/* =================================================================================================== */
echo("<div id=\"col7\">");
echo("Load Sent From:<br>");
echo("<select name=\"retfrom\" class=\"sel3\">\n");
//$Query = "select person.person_id, person.first_name, person.last_name from prod_profile join person on person.person_id in (prod_profile.supplier_no1,prod_profile.supplier_no2,prod_profile.supplier_no3) where prod_profile.prod_id = '" . $product . "'  "; 
$Query = "select person.person_id, person.first_name, person.last_name from prod_supplier join person on person.person_id = prod_supplier.supplier_id where prod_supplier.prod_id = '" . $product . "'  "; 
//$Query = "select person.person_id, person.first_name, person.last_name from person where (person.person_type in ('RP') or person.person_type starting 'C') "; 
$Query .= " order by first_name, last_name "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Returner!<br>\n");
	exit();
}
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($retfrom == $Row[0])
	{
		echo( "<OPTION value=\"$Row[0]\" selected>$Row[1] $Row[2]\n");
	}
	else
	{
		echo( "<OPTION value=\"$Row[0]\">$Row[1] $Row[2]\n");
	}
}
//release memory
ibase_free_result($Result);
//echo("</select><br>\n");
echo("</select>\n");
echo("<INPUT type=\"button\" name=\"addsupplier\" value=\"A\" ");  
echo(' alt="Add Supplier" onclick="processProdSupplier();" >');
echo("<br>\n"); 
/* ================================================================== 
echo("<div id=\"col7\">");
if (isset($product))
	echo("Product ID:<br><INPUT type=\"text\" name=\"product\" value=\"" . $product . "\" maxlength=\"30\" size=\"30\" onchange=\"doproduct();");
else
	echo("Product ID:<br><INPUT type=\"text\" name=\"product\" maxlength=\"30\" size=\"30\" onchange=\"doproduct();");
echo("processEdit();\" >\n");
echo("<INPUT type=\"button\" name=\"search\" value=\"Search\" ");  
echo(' alt="Search" onclick="processProdSearch();" >');
echo("<br>\n");
 ================================================================== */
echo("<table border=\"0\">\n");
echo("<tr><td>");
if ($received_ssn_qty == 0)
{
	echo("Recd :<INPUT type=\"text\" name=\"received_ssn_qty\" size=\"5\" maxlength=\"10\" value=\"\">\n");
}
else
{
	echo("Recd :<INPUT type=\"text\" name=\"received_ssn_qty\" size=\"5\" maxlength=\"10\" value=\"$received_ssn_qty\">\n");
}
echo("<div ID=\"col5\">\n");
echo("</td><td>");
echo("Done:<INPUT type=\"text\" name=\"printed_ssn_qty\" size=\"5\" maxlength=\"10\" value=\"$printed_ssn_qty\" readonly>\n");
echo("<div ID=\"col5\">\n");
echo("</td><td>");
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
echo("</td></tr><table>");
echo("</div>\n");
echo("<div id=\"col8\">");
echo("Qty Labels X Qty/SSN Label:<br>\n");
echo("<table border=\"0\">\n");
echo("<tr><td>");
echo("<INPUT type=\"text\" name=\"label_qty1\" maxlength=\"4\" size=\"4\" class=\"sel5\">\n");
echo("</td><td>X");
echo("</td><td>");
echo("<INPUT type=\"text\" name=\"ssn_qty1\" maxlength=\"6\" size=\"6\" class=\"sel5\">\n");
echo("</td><td>Weight");
echo("</td><td>");
echo("<INPUT type=\"text\" name=\"weight_qty1\" maxlength=\"5\" size=\"5\" class=\"sel5\">\n");
echo("</td><td>UOM");
echo("</td><td>");
//echo("<select name=\"weight_uom\" class=\"sel5\">\n");
echo("<select name=\"weight_uom\" >\n");
$Query = "select code, description from uom where uom_type='WT' order by description "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Uom!<br>\n");
	exit();
}
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] == "KG")
	{
		echo( "<OPTION value=\"$Row[0]\" selected >$Row[1]\n");
	}
	else
	{
		echo( "<OPTION value=\"$Row[0]\">$Row[1]\n");
	}
}
//release memory
ibase_free_result($Result);
echo("</select>\n");
echo("</td></tr></table>");
echo("Location:<select name=\"location\" class=\"sel8\">\n");
$Query = "select ln.wh_id, ln.locn_id, ln.locn_name from location ln join session on session.device_id = '" . $tran_device . "' and session.code='CURRENT_WH_ID' where ln.wh_id = session.description and ln.store_area = 'RC' order by ln.locn_name "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Locations!<br>\n");
	exit();
}
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($location == ($Row[0] . $Row[1]))
	{
		echo( "<OPTION value=\"$Row[0]$Row[1]\" selected>$Row[2]\n");
	}
	else
	{
		echo( "<OPTION value=\"$Row[0]$Row[1]\">$Row[2]\n");
	}
}
//release memory
ibase_free_result($Result);
echo("</select><br>\n");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	echo("<INPUT type=\"submit\" name=\"accept\" value=\"Accept\">\n");
}
else
{
	echo("<BUTTON name=\"accept\" value=\"Accept\" type=\"submit\" id=\"col9\" >\n");
	echo("Accept<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
}
*/
{
	// html 4.0 browser
	$alt = "Accept";
	// Create a table.
	echo ("<table border=\"0\" align=\"LEFT\" id=\"col9\">");
	echo ("<TR>");
	echo ("<TD>");
	echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
*/
	echo('SRC="/icons/whm/accept.gif" alt="' . $alt . '">');
	echo("</form>");
	echo ("</TD>");
	echo ("<TD>");
	echo("<form action=\"receive_menu.php\"  method=\"post\"  name=getssnback>\n");
	echo("<INPUT type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/Back_50x100.gif"  alt="Back">');
	echo("</form>");
	echo ("</TD>");
	//echo ("</TR>");
	$alt = "Product Profile";
	//echo ("<TR>");
	echo ("<TD>");
	echo("<form action=\"../query/product.php\"  method=\"post\" name=getproduct onsubmit=\"return processProd();\" >\n");
	echo("<INPUT type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	if (isset($product))
		echo("<INPUT type=\"hidden\" name=\"product\" value=\"" . $product . "\" >");
	else
		echo("<INPUT type=\"hidden\" name=\"product\" >");

	echo("<INPUT type=\"hidden\" name=\"from\" value=\"".$_SERVER['PHP_SELF']."\" >");
	echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=' . urlencode($alt) . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
*/
	echo('SRC="/icons/whm/prodprofile.gif" alt="' . $alt . '">');
	echo("</form>");
	echo ("</TD>");
	echo ("<TD>");
	$alt = "Comments";
	echo("<form action=\"comments.php\"  method=\"post\" onsubmit=\"return processComment();\" name=getcomment>\n");
	echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
*/
	echo('SRC="/icons/whm/comment.gif" alt="' . $alt . '">');
	echo("<INPUT type=\"hidden\" name=\"type\" value=\"$type\" >");
	echo("<INPUT type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\">\n");
	echo("<INPUT type=\"hidden\" name=\"line\" value=\"$line\">\n");
	echo("<INPUT type=\"hidden\" name=\"retfrom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"owner\" >\n");
	echo("<INPUT type=\"hidden\" name=\"received_ssn_qty\" >\n");
	echo("<INPUT type=\"hidden\" name=\"printed_ssn_qty\" >\n");
	echo("<INPUT type=\"hidden\" name=\"location\" >\n");
	echo("<INPUT type=\"hidden\" name=\"printer\" >\n");
	echo("<INPUT type=\"hidden\" name=\"problem\" >\n");
	echo("<INPUT type=\"hidden\" name=\"uom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"product\" >\n");
	echo("<INPUT type=\"hidden\" name=\"label_qty1\" >\n");
	echo("<INPUT type=\"hidden\" name=\"label_qty2\" >\n");
	echo("<INPUT type=\"hidden\" name=\"ssn_qty1\" >\n");
	echo("<INPUT type=\"hidden\" name=\"ssn_qty2\" >\n");
	echo("<INPUT type=\"hidden\" name=\"from\" value=\"verifyLPweight.php\" >\n");
	echo("<INPUT type=\"hidden\" name=\"weight_uom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"weight_qty1\" >\n");
	echo("</form>");
	echo ("</TD>");
	echo ("</TR>");
/*
	echo ("<TR>");
	echo ("<TD COLSPAN=\"2\">");
	$alt = "Receive without Weights";
	echo("<form action=\"verifyLP.php\"  method=\"post\" name=getnoweight>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/buttonflat.php?text=' . urlencode($alt) );
	echo('" alt="' . $alt . '">');
	echo('</form>');
	echo ("</TD>");
	echo ("</TR>");
*/
	echo ("</TABLE>");

}
echo("</div>\n");
?>
<script type="text/javascript">
{
	document.getprodqtys.message.value="Enter Product";
	document.getprodqtys.product.focus();
}
</script>
</html>
