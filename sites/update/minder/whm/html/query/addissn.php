<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
include "../login.inc";
?>
<html>
<head>
<title>Product Profile</title>
<?php
include "viewport.php";
//<meta name="viewport" content="width=device-width">
?>
<link rel=stylesheet type="text/css" href="addissn.css">
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
onerror = errorHandler
function regetproduct() {
	document.reget.product.value=document.alterproduct.product.value;
	dowhat = document.alterproduct.company.selectedIndex ;
        document.reget.company.value = document.alterproduct.company.options[dowhat].value ;
	document.reget.submit();
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
  document.message.message.value="";
  if (( document.alterproduct.shortdesc.value=="") ||
      ( document.alterproduct.shortdesc.value== document.alterproduct.product.value))
  {
  	document.message.message.value="Must Enter a Description";
	document.alterproduct.shortdesc.focus();
  	return false;
  }
  return true;
}
function recalcInner(dataFrom) {
	var dataIn ;
  	var out ;
   if ( chkNumeric(document.alterproduct.order_qty.value)==false)
   {
      document.alterproduct.order_qty.value = "0";
   }
   if (dataFrom == "QTY")
   {
      dataIn = document.alterproduct.inner_qty.value;
      if (chkNumeric(dataIn))
      {
         out = parseInt(document.alterproduct.order_qty.value / dataIn );
         document.alterproduct.per_inner_qty.value = out;
      }
   }
   if (dataFrom == "PER")
   {
      dataIn = document.alterproduct.per_inner_qty.value;
      if (chkNumeric(dataIn))
      {
         out = parseInt(document.alterproduct.order_qty.value / dataIn );
         document.alterproduct.inner_qty.value = out;
      }
   }
  return true;
}
function recalcPallet(dataFrom) {
	var dataIn ;
  	var out ;
   if ( chkNumeric(document.alterproduct.order_qty.value)==false)
   {
      document.alterproduct.order_qty.value = "0";
   }
   if (dataFrom == "QTY")
   {
      dataIn = document.alterproduct.pallet_qty.value;
      if (chkNumeric(dataIn))
      {
         out = parseInt(document.alterproduct.order_qty.value / dataIn );
         document.alterproduct.per_pallet_qty.value = out;
      }
   }
   if (dataFrom == "PER")
   {
      dataIn = document.alterproduct.per_pallet_qty.value;
      if (chkNumeric(dataIn))
      {
         out = parseInt(document.alterproduct.order_qty.value / dataIn );
         document.alterproduct.pallet_qty.value = out;
      }
   }
  return true;
}
function recalcOuter(dataFrom) {
	var dataIn ;
  	var out ;
   if ( chkNumeric(document.alterproduct.order_qty.value)==false)
   {
      document.alterproduct.order_qty.value = "0";
   }
   if (dataFrom == "QTY")
   {
      dataIn = document.alterproduct.outer_qty.value;
      if (chkNumeric(dataIn))
      {
         out = parseInt(document.alterproduct.order_qty.value / dataIn );
         document.alterproduct.per_outer_qty.value = out;
      }
   }
   if (dataFrom == "PER")
   {
      dataIn = document.alterproduct.per_outer_qty.value;
      if (chkNumeric(dataIn))
      {
         out = parseInt(document.alterproduct.order_qty.value / dataIn );
         document.alterproduct.outer_qty.value = out;
      }
   }
  return true;
}
function initDiv()
{
var nextDiv;
var myStartDiv;
var myDummy;
var myDivName;
myDivName = "p" + myDiv;
divstyle = document.getElementById(myDivName).style.visibility;    
if (divstyle.toLowerCase() == "visible" || divstyle == "") {     
myDummy="1";
}else{     
   document.getElementById(myDivName).style.visibility = "visible";    
   /* document.getElementById(myDivName).style.position = "relative"; */   
} 
myStartDiv = parseInt(myDivFirst) + 1;
for (nextDiv = myStartDiv; nextDiv < myDivLimit; nextDiv++) {
myDivName = "p" + nextDiv;
divstyle = document.getElementById(myDivName).style.visibility;    
if (divstyle.toLowerCase() == "visible" || divstyle == "") {     
   document.getElementById(myDivName).style.visibility = "hidden";    
   /* document.getElementById(myDivName).style.position = "absolute";    */         
} 
} 
}

function doNextSrcn()
{
var divstyle = new String();    
var nextDiv;
var myDivName;
nextDiv = parseInt(myDiv) + 1;
if (nextDiv >= myDivLimit) {
nextDiv = myDivFirst;
}
myDivName = "p" + myDiv;
divstyle = document.getElementById(myDivName).style.visibility;    
if (divstyle.toLowerCase() == "visible" || divstyle == "") {     
   document.getElementById(myDivName).style.visibility = "hidden";    
   /* document.getElementById(myDivName).style.position = "absolute"; */            
}else{     
   document.getElementById(myDivName).style.visibility = "visible";    
   /* document.getElementById(myDivName).style.position = "relative"; */   
} 
myDiv = nextDiv;
myDivName = "p" + myDiv;
divstyle = document.getElementById(myDivName).style.visibility;    
if (divstyle.toLowerCase() == "visible" || divstyle == "") {     
   document.getElementById(myDivName).style.visibility = "hidden";    
   /* document.getElementById(myDivName).style.position = "absolute"; */            
}else{     
   document.getElementById(myDivName).style.visibility = "visible";    
   /* document.getElementById(myDivName).style.position = "relative"; */   
} 
}
function saveCompany()
{
	regetproduct() ;
}
var myDiv="1";
var myDivLimit="4";
var myDivFirst="1";
</script>
</head>
<body onload="initDiv()">
<?php
//<body >
require_once 'DB.php';
require 'db_access.php';
include "logme.php";
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);

function getstockdesc($stock)
{
	global $Link;
	$stockdesc = "";
	$Query = " select description  from  options o1 where  o1.group_code = 'PROD_STOCK' and o1.code = '" . $stock . "'";
	if (!($Result6 = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Options!<BR>\n");
		exit;
	}
	while ( ($Row6 = ibase_fetch_row($Result6)) ) {
		$stock = $Row6[0];
	}
	//release memory
	ibase_free_result($Result6);
	if (($stock == "") or ($stock == " "))
	{
		$stockdesc = "Unknown";
	}
	else
	{
		$stockdesc = $stock;
	}
	return $stockdesc;
}
$product = "";
$wk_add_product = "F";

	$received_qty = getBDCScookie($Link, $tran_device, "received_qty" );
	//commit
	//$Link->commit();
	//ibase_commit($dbTran);
	//$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
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
	$label_qty2 = getBDCScookie($Link, $tran_device, "label_qty2" );
	$ssn_qty2 = getBDCScookie($Link, $tran_device, "ssn_qty2" );
	$weight_qty1 = getBDCScookie($Link, $tran_device, "weight_qty1" );
	$weight_uom = getBDCScookie($Link, $tran_device, "weight_uom" );
	$printer = getBDCScookie($Link, $tran_device, "printer" );
	$received_ssn_qty = getBDCScookie($Link, $tran_device, "received_ssn_qty" );
	$location = getBDCScookie($Link, $tran_device, "location" );
	$uom = getBDCScookie($Link, $tran_device, "uom" );
	$printed_ssn_qty = getBDCScookie($Link, $tran_device, "printed_ssn_qty" );
	$owner = getBDCScookie($Link, $tran_device, "owner" );
	$other_qty1 = getBDCScookie($Link, $tran_device, "other_qty1" );
	$other_qty3 = getBDCScookie($Link, $tran_device, "other_qty3" );
	$other_qty2 = getBDCScookie($Link, $tran_device, "other_qty2" );
	$other_qty4 = getBDCScookie($Link, $tran_device, "other_qty4" );
	$qty = getBDCScookie($Link, $tran_device, "createqty" );
	$wh_id = getBDCScookie($Link, $tran_device, "wh_id" );
	$locn_id = getBDCScookie($Link, $tran_device, "locn_id" );

if (isset($_POST['product'])) 
{
	$product = $_POST["product"];
}
if (isset($_GET['product'])) 
{
	$product = $_GET["product"];
}
if (isset($_POST['from'])) 
{
	$from = $_POST["from"];
}
if (isset($_GET['from'])) 
{
	$from = $_GET["from"];
}
if (isset($_POST['grn'])) 
{
	$grn = $_POST["grn"];
}
if (isset($_GET['grn'])) 
{
	$grn = $_GET["grn"];
}
if (isset($_GET['addproduct'])) 
{
	$wk_add_product = $_GET["addproduct"];
}
if (isset($_POST['addproduct'])) 
{
	$wk_add_product = $_POST["addproduct"];
}


if (isset($_POST['printer']))
{
	$printer = $_POST['printer'];
	setBDCScookie($Link, $tran_device, "printer", $printer);
}
if (isset($_GET['printer']))
{
	$printer = $_GET['printer'];
	setBDCScookie($Link, $tran_device, "printer", $printer);
}
if (isset($_POST['retfrom']))
{
	$retfrom = $_POST['retfrom'];
	setBDCScookie($Link, $tran_device, "retfrom", $retfrom);
}
if (isset($_GET['retfrom']))
{
	$retfrom = $_GET['retfrom'];
	setBDCScookie($Link, $tran_device, "retfrom", $retfrom);
}
if (isset($_POST['owner']))
{
	$owner = $_POST['owner'];
	setBDCScookie($Link, $tran_device, "owner", $owner );
}
if (isset($_GET['owner']))
{
	$owner = $_GET['owner'];
	setBDCScookie($Link, $tran_device, "owner", $owner );
}
if (isset($_POST['received_ssn_qty']))
{
	$received_ssn_qty = $_POST['received_ssn_qty'];
	setBDCScookie($Link, $tran_device, "received_ssn_qty", $received_ssn_qty);
}
if (isset($_GET['received_ssn_qty']))
{
	$received_ssn_qty = $_GET['received_ssn_qty'];
	setBDCScookie($Link, $tran_device, "received_ssn_qty", $received_ssn_qty);
}
if (isset($_POST['location']))
{
	$location = $_POST['location'];
	setBDCScookie($Link, $tran_device, "location", $location);
}
if (isset($_GET['location']))
{
	$location = $_GET['location'];
	setBDCScookie($Link, $tran_device, "location", $location);
}
if (isset($_POST['problem']))
{
	$problem = $_POST['problem'];
	setBDCScookie($Link, $tran_device, "problem", $problem);
}
if (isset($_GET['problem']))
{
	$problem = $_GET['problem'];
	setBDCScookie($Link, $tran_device, "problem", $problem);
}
if (isset($_POST['uom']))
{
	$uom = $_POST['uom'];
	setBDCScookie($Link, $tran_device, "uom", $uom);
}
if (isset($_GET['uom']))
{
	$uom = $_GET['uom'];
	setBDCScookie($Link, $tran_device, "uom", $uom);
}
if (isset($_POST['label_qty1']))
{
	$label_qty1 = $_POST['label_qty1'];
	setBDCScookie($Link, $tran_device, "label_qty1", $label_qty1);
}
if (isset($_GET['label_qty1']))
{
	$label_qty1 = $_GET['label_qty1'];
	setBDCScookie($Link, $tran_device, "label_qty1", $label_qty1);
}
if (isset($_POST['label_qty2']))
{
	$label_qty2 = $_POST['label_qty2'];
	setBDCScookie($Link, $tran_device, "label_qty2", $label_qty2);
}
if (isset($_GET['label_qty2']))
{
	$label_qty2 = $_GET['label_qty2'];
	setBDCScookie($Link, $tran_device, "label_qty2", $label_qty2);
}
if (isset($_POST['ssn_qty1']))
{
	$ssn_qty1 = $_POST['ssn_qty1'];
	setBDCScookie($Link, $tran_device, "ssn_qty1", $ssn_qty1);
}
if (isset($_GET['ssn_qty1']))
{
	$ssn_qty1 = $_GET['ssn_qty1'];
	setBDCScookie($Link, $tran_device, "ssn_qty1", $ssn_qty1);
}
if (isset($_POST['ssn_qty2']))
{
	$ssn_qty2 = $_POST['ssn_qty2'];
	setBDCScookie($Link, $tran_device, "ssn_qty2", $ssn_qty2 );
}
if (isset($_GET['ssn_qty2']))
{
	$ssn_qty2 = $_GET['ssn_qty2'];
	setBDCScookie($Link, $tran_device, "ssn_qty2", $ssn_qty2 );
}
if (isset($_POST['other_qty1']))
{
	$other_qty1 = $_POST['other_qty1'];
	setBDCScookie($Link, $tran_device, "other_qty1", $other_qty1 );
}
if (isset($_GET['other_qty1']))
{
	$other_qty1 = $_GET['other_qty1'];
	setBDCScookie($Link, $tran_device, "other_qty1", $other_qty1 );
}
if (isset($_POST['other_qty2']))
{
	$other_qty2 = $_POST['other_qty2'];
	setBDCScookie($Link, $tran_device, "other_qty2", $other_qty2 );
}
if (isset($_GET['other_qty2']))
{
	$other_qty2 = $_GET['other_qty2'];
	setBDCScookie($Link, $tran_device, "other_qty2", $other_qty2 );
}
if (isset($_POST['other_qty3']))
{
	$other_qty3 = $_POST['other_qty3'];
	setBDCScookie($Link, $tran_device, "other_qty3", $other_qty3 );
}
if (isset($_GET['other_qty3']))
{
	$other_qty3 = $_GET['other_qty3'];
	setBDCScookie($Link, $tran_device, "other_qty3", $other_qty3 );
}
if (isset($_POST['other_qty4']))
{
	$other_qty4 = $_POST['other_qty4'];
	setBDCScookie($Link, $tran_device, "other_qty4", $other_qty4 );
}
if (isset($_GET['other_qty4']))
{
	$other_qty4 = $_GET['other_qty4'];
	setBDCScookie($Link, $tran_device, "other_qty4", $other_qty4 );
}
if (isset($_POST['company']))
{
	$company = $_POST['company'];
	setBDCScookie($Link, $tran_device, "company", $company );
}
if (isset($_GET['company']))
{
	$company = $_GET['company'];
	setBDCScookie($Link, $tran_device, "company", $company );
}
if (isset($_POST['qty']))
{
	$qty = $_POST['qty'];
	setBDCScookie($Link, $tran_device, "createqty", $qty );
}
if (isset($_GET['qty']))
{
	$qty = $_GET['qty'];
	setBDCScookie($Link, $tran_device, "createqty", $qty );
}
if (isset($_POST['wh_id']))
{
	$wh_id = $_POST['wh_id'];
	setBDCScookie($Link, $tran_device, "wh_id", $wh_id );
}
if (isset($_GET['wh_id']))
{
	$wh_id = $_GET['wh_id'];
	setBDCScookie($Link, $tran_device, "wh_id", $wh_id );
}
if (isset($_POST['locn_id']))
{
	$locn_id = $_POST['locn_id'];
	setBDCScookie($Link, $tran_device, "locn_id", $locn_id );
}
if (isset($_GET['wh_id']))
{
	$wh_id = $_GET['wh_id'];
	setBDCScookie($Link, $tran_device, "wh_id", $wh_id );
}

$wk_comany_cnt = 0;
$Query = "select count(*)  from company "; 
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

if ($wk_add_product == "T") 
{
	if (isset($_POST['company'])) 
	{
		$wk_add_company = $_POST["company"];
	}
	if (isset($_GET['company'])) 
	{
		$wk_add_company = $_GET["company"];
	}
	if (isset($_POST['owner'])) 
	{
		$wk_add_owner = $_POST["owner"];
	}
	if (isset($_GET['owner'])) 
	{
		$wk_add_owner = $_GET["owner"];
	}
//var_dump($wk_add_product);
//var_dump($wk_add_company);
//var_dump($wk_add_owner);
}
/*
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
// Set the variables for the database access:
*/
$rcount = 0;
//echo ("<div id=\"col2\">\n");
//echo("<form action=\"transAP.php\" method=\"post\" name=\"alterproduct\" onsubmit=\"return processEdit();\">\n");
echo("<form action=\"transAP.php\" method=\"post\" name=\"alterproduct\" >\n");

// check whether we can use the next_prod_id procedure for new prod id
$Query6 = "select description from options where group_code = 'REP_CODE' and code = 'PROD_PROFILE.LONG_DESC'  "; 
//echo($Query);
$wk_use_long_desc    = "T";
if (!($Result6 = ibase_query($Link, $Query6)))
{
	echo("Unable to Read Options!<BR>\n");
	exit;
}
while ( ($Row6 = ibase_fetch_row($Result6)) ) {
	$wk_use_long_desc  = $Row6[0];
}
//release memory
ibase_free_result($Result6);

// get the default stock -- for when the stock is null 
$Query3 = "select default_prod_stock from control  "; 
//echo($Query);
$wk_default_stock = "U"; /* undefined */
if (!($Result3 = ibase_query($Link, $Query3)))
{
	echo("Unable to Read Control!<BR>\n");
	exit;
}
while ( ($Row3 = ibase_fetch_row($Result3)) ) {
	$wk_default_stock = $Row3[0];
}
//release memory
ibase_free_result($Result3);

if ($wk_add_product == "T")
{
	if (!isset($wk_add_company))
	{
		// check whether we can use the next_prod_id procedure for new prod id
		$Query3 = "select description from options where group_code = 'REP_CODE' and code = 'PROD_PROFILE.P_COMPANY_FROM_ISSN'  "; 
		$wk_use_issn_company_id = "F";
		//echo($Query);
		if (!($Result3 = ibase_query($Link, $Query3)))
		{
			echo("Unable to Read Options!<BR>\n");
			exit;
		}
		while ( ($Row3 = ibase_fetch_row($Result3)) ) {
			$wk_use_issn_company_id = $Row3[0];
		}
		//release memory
		ibase_free_result($Result3);
		if ($wk_use_issn_company_id == "T")
		{
			if (isset($wk_add_owner))
			{
				$wk_add_company = $wk_add_owner;
			}
		}
	}
	else
	{
		// check whether we can use the next_prod_id procedure for new prod id
		$Query3 = "select description from options where group_code = 'REP_CODE' and code = 'PROD_PROFILE.P_NEXT_PROD_ID'  "; 
		//echo($Query);
		$wk_use_next_prod_id = "F";
		if (!($Result3 = ibase_query($Link, $Query3)))
		{
			echo("Unable to Read Options!<BR>\n");
			exit;
		}
		while ( ($Row3 = ibase_fetch_row($Result3)) ) {
			$wk_use_next_prod_id = $Row3[0];
		}
		//release memory
		ibase_free_result($Result3);
		if ($wk_use_next_prod_id == "T")
		{
			if ($wk_add_company != "")
			{
				// want to get the next prod id and put into the product field
				$Query5 = "select prod_id, error_text from next_prod_id ('" . $wk_add_company . "')"; 
				//echo("[$Query]\n");
				if (!($Result5 = ibase_query($Link, $Query5)))
				{
					echo("Unable to query next product!<BR>\n");
					exit();
				}
				while (($Row5 = ibase_fetch_row($Result5))) {
					$product = $Row5[0];
					$wk_error_text = $Row5[1];
				}
				//release memory
				ibase_free_result($Result5);
			}
			else
			{
				// must use controls company
				$wk_cntrl_company = "";
				$Query4 = "select company_id from control "; 
				//echo("[$Query]\n");
				if (!($Result4 = ibase_query($Link, $Query4)))
				{
					echo("Unable to query control!<BR>\n");
					exit();
				}
				while (($Row4 = ibase_fetch_row($Result4))) {
					$wk_cntrl_company = $Row4[0];
				}
				//release memory
				ibase_free_result($Result4);
				$Query5 = "select prod_id, error_text from next_prod_id ('" . $wk_cntrl_company . "')"; 
				//echo("[$Query]\n");
				if (!($Result5 = ibase_query($Link, $Query5)))
				{
					echo("Unable to query next product!<BR>\n");
					exit();
				}
				while (($Row5 = ibase_fetch_row($Result5))) {
					$product = $Row5[0];
					$wk_error_text = $Row5[1];
				}
				//release memory
				ibase_free_result($Result5);
			}
		}
	}
}
// get companys that the product is for
$wk_prod_company = array();
$Query = "select pp.company_id, cy.name ";
$Query .= " from prod_profile pp";
$Query .= " left outer join company cy on cy.company_id = pp.company_id";
$Query .= " where pp.prod_id = '".$product."'";
$Query .= " or    pp.alternate_id = '".$product."'";
//echo("[$Query]\n");
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to query Produ Profile!<BR>\n");
	exit();
}
// Fetch the results from the database.
while (($Row = ibase_fetch_row($Result))) {
	$wkCompany = $Row[0];
	$name = $Row[1];
	$wk_prod_company [$wkCompany] = $name;
}

//release memory
ibase_free_result($Result);


$Query = "select pp.short_desc, pp.long_desc, pp.company_id, pp.net_weight, pp.issue_per_order_unit, pp.issue_per_inner_unit, pp.order_weight, pp.net_weight_uom, pp.order_uom, pp.inner_uom, pp.order_weight_uom, cy.name, pp.stock, o1.description ,k1.kit_id, pp.uom, pp.issue, pp.issue_per_pallet, pp.issue_per_outer_carton, pp.prod_id, pp.alternate_id";
$Query .= ", pp.dimension_x, pp.dimension_y, pp.dimension_z, pp.dimension_x_uom, pp.dimension_y_uom, pp.dimension_z_uom ";
$Query .= " from prod_profile pp";
$Query .= " left outer join company cy on cy.company_id = pp.company_id";
$Query .= " left outer join options o1 on o1.group_code = 'PROD_STOCK' and o1.code = pp.stock";
$Query .= " left outer join kit k1 on k1.kit_id = pp.prod_id";
$Query .= " where pp.prod_id = '".$product."'";
$Query .= " or    pp.alternate_id = '".$product."'";
/* if something then get the product for the company   
   if company is Unknown then use company_id 'ALL'
*/
if (isset($wk_add_company ))
{
	if ($wk_add_company <> "")
	{
		$Query .= " and pp.company_id  in ('ALL', '".$wk_add_company."')";
	} else {
		$Query .= " and pp.company_id  = 'ALL'";
	}
} else {
	if ($owner <> "")
	{
		$Query .= " and pp.company_id in ('ALL', '".$owner."')";
	} else {
		$Query .= " and pp.company_id  = 'ALL'";
	}
}

//echo("[$Query]\n");
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to query issn!<BR>\n");
	exit();
}
$short_desc = "";
$long_desc = "";
$wk_prod_company2 = "";
$net_weight = "";
$order_qty = "";
$inner_qty = "";
$order_weight = "";
$net_weight_uom = "";
$order_uom = "";
$inner_uom = "";
$order_weight_uom = "";
$company_name = "";
$prod_stock = "";
$stock_desc = "";
$kit_id = "";
$uom = "";
$issue = "";
$pallet_qty = "";
$pallet_uom = "";
$per_pallet_qty = "";
$per_inner_qty = "";
$per_outer_qty = "";
$outer_qty = "";
$pp_alternate = "";
$pp_product   = "";
$dimension_x = "";
$dimension_y = "";
$dimension_z = "";
$dimension_x_uom = "";
$dimension_y_uom = "";
$dimension_z_uom = "";
// Fetch the results from the database.
while (($Row = ibase_fetch_row($Result))) {
	//var_dump ($Row);
	if ($Row[2] <> 'ALL' )
	{
	$short_desc = $Row[0];
	$long_desc = $Row[1];
	$wk_prod_company2 = $Row[2];
	$net_weight = $Row[3];
	$order_qty = $Row[4];
	$per_inner_qty = $Row[5];
	if ($per_inner_qty == "")
		$per_inner_qty = 1;
	$inner_qty = $order_qty / $per_inner_qty;
	$order_weight = $Row[6];
	if ($order_weight == "")
		$order_weight = 1;
	$net_weight_uom = $Row[7];
	$order_uom = $Row[8];
	$inner_uom = $Row[9];
	$order_weight_uom = $Row[10];
	$company_name = $Row[11];
	$prod_stock = $Row[12];
	$stock_desc = $Row[13];
	$kit_id = $Row[14];
	$uom = $Row[15];
	$issue = $Row[16];
	$qty = 1;
	$per_pallet_qty = $Row[17];
	if ($per_pallet_qty == "")
		$per_pallet_qty = 1;
	$pallet_qty = $order_qty / $per_pallet_qty;
	$per_outer_qty = $Row[18];
	if ($per_outer_qty == "")
		$per_outer_qty = 1;
	$outer_qty = $order_qty / $per_outer_qty;
	$pp_product   = $Row[19];
	$pp_alternate = $Row[20];
	$dimension_x = $Row[21];
	$dimension_y = $Row[22];
	$dimension_z = $Row[23];
	$dimension_x_uom = $Row[24];
	$dimension_y_uom = $Row[25];
	$dimension_z_uom = $Row[26];
	}
}

$short_desc = htmlspecialchars($short_desc);
$long_desc = htmlspecialchars($long_desc);
//var_dump($pp_alternate);
//var_dump($pp_product  );
//release memory
ibase_free_result($Result);

if ($pp_product != "")
{
	// ie product exists
	if ($product != $pp_product)
	{
		$product = $pp_product;
		// product was alternate id
	}
}
if ($wk_add_product == "T")
{
	if (isset($wk_add_company))
	{
		$company = $wk_add_company;
	}
}
if ($prod_stock == "")
{
	$prod_stock = $wk_default_stock;
}
if ($prod_stock == " ")
{
	$prod_stock = $wk_default_stock;
}
echo("<input type=\"hidden\" name=\"stock\" value=\"$prod_stock\" readonly >");
//echo("<input type=\"hidden\" name=\"company\" value=\"$company\" readonly >");
echo("<table border=\"0\" align=\"left\">");
echo("<tr><td>");
/*
if ($wk_add_product != "T")
{
	//var_dump($prod_stock);
	echo("<label for=\"stock_desc\">Stock</label>");
	//echo("<input type=\"text\" id=\"stock_desc\" name=\"stock_desc\" value=\"" . getstockdesc($stock_desc) . "\" readonly size=\"11\">");
	echo("<input type=\"text\" id=\"stock_desc\" name=\"stock_desc\" value=\"" . getstockdesc($prod_stock) . "\" readonly size=\"11\" class=\"noread\" >");
	//echo("<br>\n");
	echo("</td></tr>\n");
} 
*/
{
	echo("<tr><td>");
	echo("<label for=\"company\">Owner</label>");
	echo("<select name=\"company\" id=\"company\" onchange=\"saveCompany();\" class=\"sel3\" >\n");
	echo( "<OPTION value=\"\" >Unknown\n");
	$Query = "select company_id , name  from company  order by name  "; 
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Company!<BR>\n");
		exit;
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		if (isset($company))
		{
			if ($Row[0] == $company)
			{
				echo( "<OPTION value=\"$Row[0]\" selected >$Row[1]\n");
			} else {
				echo( "<OPTION value=\"$Row[0]\">$Row[1]\n");
			}
		} else {
			if (isset($owner))
			{
				if ($Row[0] == $owner)
				{
					echo( "<OPTION value=\"$Row[0]\" selected >$Row[1]\n");
				} else {
					echo( "<OPTION value=\"$Row[0]\">$Row[1]\n");
				}
			} else {
				echo( "<OPTION value=\"$Row[0]\">$Row[1]\n");
			}
		}
	}
	//release memory
	ibase_free_result($Result);
	//echo("</select><BR>\n");
	echo("</select>\n");
	echo("</td><td colspan=\"2\">");
	//echo("<label for=\"prodcompany\">Exists in</label>");
	echo("Exists in");
	echo("<select name=\"prodcompany\" id=\"prodcompany\" class=\"sel3\" >\n");
	foreach ($wk_prod_company as $Key_results => $Value_results) {
		if ($Value_results == "")
		{
			if ($Key_results == "ALL") {
				echo( "<OPTION value=\"$Key_results\" >ALL\n");
			} else {
				echo( "<OPTION value=\"$Key_results\" >$Key_results Unknown\n");
			}
		}
		else
		{
			echo( "<OPTION value=\"$Key_results\">$Value_results\n");
		}
	}
	echo("</select>\n");
	echo("</td><td>");
}
if ($kit_id <> "")
{
	echo("<p class=\"kit\">");
	//echo("Kitted<br>");
	echo("Kitted");
	echo("</p>");
	echo("<input type=\"hidden\" name=\"kitid\" value=\"" . $kit_id . "\" >");
}
else
{
	echo("<p class=\"kit\">");
	//echo("Not Kitted<br>");
	echo("Not Kitted");
	echo("</p>");
}
echo("</td></tr>");
echo("<tr><td>");
//echo("<br>\n");
//echo("Product ID:<input type=\"text\" name=\"product\" value=\"$product\" size=\"30\" onchange=\"return regetproduct()\">");
echo("<label for=\"product\">ID</label>");
//echo("ID");
//echo("</td></tr>");
//echo("</td>");
//echo("<tr><td>");
//echo("<td>");
echo("<input type=\"text\" id=\"product\" name=\"product\" value=\"$product\" size=\"30\" onchange=\"return regetproduct()\" class=\"product\" >");
echo("</td></tr>");
echo("</table>");
echo("<br>");
//echo("<br>");
//echo("<br>");
//echo("<input type=\"text\" name=\"shortdesc\" readonly size=\"50\" value=\"$short_desc\" >");
//echo("<input type=\"text\" name=\"shortdesc\" id=\"shortdesc\"  size=\"50\" value=\"$short_desc\" class=\"shortdesc\" >");
echo("<input type=\"text\" name=\"shortdesc\" id=\"shortdesc\"  size=\"50\" value=\"$short_desc\" class=\"shortdesc\" >");
if ($wk_use_long_desc == "T")
{
	echo("<table border=\"0\" align=\"left\">");
	echo("<tr><td>");
	echo("<textarea name=\"longdesc\" readonly rows=\"2\" cols=\"50\">$long_desc</textarea>");
	echo("</td></tr>");
	echo ("</table>\n");
	echo("<br><br><br>");
}
echo("<br>");
echo("<table border=\"0\" align=\"left\">");
echo("<tr><td>");
echo("Alternate:");
echo("</td><td colspan=\"3\">");
echo("<input type=\"text\" name=\"alternate\" value=\"$pp_alternate\" size=\"14\" maxlength=\"30\" class=\"alternate\" >");
echo("</td>");
echo("</tr>");
echo ("</table>\n");
echo("<div id=\"p1\" name=\"1\">");
echo("<table border=\"0\" align=\"left\" id=\"qtys\" name=\"qtys\" class=\"qtys\">");
//echo("<tr><th></th><th>Qty</th><th>UoM</th></tr>");
//echo("<tr><th></th><th>Qty</th><th>UoM</th><th>Per</th></tr>");
echo("<tr><td>");
echo("Qty:");
echo("</td><td>");
echo("<input type=\"text\" name=\"qty\" value=\"$qty\" size=\"4\" maxlength=\"6\">");
echo("</td><td>");
//echo("<select name=\"uom\"  >\n");
echo("<select name=\"uom\" class=\"sel3\" >\n");
// get default uom types
$wk_uomtypes = array();
$Query = "select code, description, standard_uom from uom_type  order by code "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read UOM TYPE!<BR>\n");
	exit;
}
while ( ($Row = ibase_fetch_row($Result)) ) {
	{
		$wk_uomtypes [$Row[0]] = $Row[2];
	}
}
if ($uom == "")
{
	$uom = $wk_uomtypes['UT'];
}
if ($inner_uom == "")
{
	$inner_uom = $wk_uomtypes['UT'];
}
if ($order_uom == "")
{
	$order_uom = $wk_uomtypes['UT'];
}
if ($pallet_uom == "")
{
	$pallet_uom = $wk_uomtypes['UT'];
}
if ($net_weight_uom == "")
{
	$net_weight_uom = $wk_uomtypes['WT'];
}
if ($order_weight_uom == "")
{
	$order_weight_uom = $wk_uomtypes['WT'];
}
//release memory
ibase_free_result($Result);
$Query = "select code, description from uom where uom_type = 'UT' order by description "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read UOM!<BR>\n");
	exit;
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
//echo("</select><BR>\n");
echo("</select>\n");
echo("</td></tr>");

echo("<tr><td>");
echo("WH:");
echo("</td><td>");
echo("<input type=\"text\" name=\"wh_id\" value=\"$wh_id\" size=\"2\" maxlength=\"2\">");
echo("</td><td>");
echo("<input type=\"text\" name=\"locn_id\" value=\"$locn_id\" size=\"10\" maxlength=\"12\">");
$Query = "select code, description from uom where uom_type = 'WT' order by description "; 
//echo($Query);
echo("</td></tr>");
//=========================
echo ("</table>\n");
echo("</div>");

echo("<div id=\"p2\" name=\"p2\">");
echo("<table border=\"0\" align=\"left\" id=\"qtys2\" name=\"qtys2\" class=\"qtys\">");
//echo("<tr><th></th><th>Qty</th><th>UoM</th></tr>");
echo("<tr><th></th><th>Qty</th><th>UoM</th><th>Per</th></tr>");
echo("<tr><td>");
echo("Outer:");
echo("</td><td>");
echo("<input type=\"text\" name=\"outer_qty\" value=\"$outer_qty\" size=\"4\" maxlength=\"6\" onchange=\"recalcOuter('QTY');\">");
echo("</td>");
echo("<td>");
echo("</td>");
echo("<td>");
echo("<input type=\"text\" name=\"per_outer_qty\" value=\"$per_outer_qty\" size=\"4\" maxlength=\"6\" onchange=\"recalcOuter('PER');\">");
echo("</td>");
echo("</tr>");
echo("<tr><td>");
echo("Pallet:");
echo("</td><td>");
echo("<input type=\"text\" name=\"pallet_qty\" value=\"$pallet_qty\" size=\"4\" maxlength=\"6\" onchange=\"recalcPallet('QTY');\">");
echo("</td>");
echo("<td>");
echo("<select name=\"pallet_uom\" class=\"sel3\" >\n");
$Query = "select code, description from uom where uom_type = 'UT' order by description "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Order_UOM!<BR>\n");
	exit;
}
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] == $pallet_uom)
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
echo("</td><td>");
echo("<input type=\"text\" name=\"per_pallet_qty\" value=\"$per_pallet_qty\" size=\"4\" maxlength=\"6\" onchange=\"recalcPallet('PER');\">");
echo("</td>");
echo("</tr>");

echo("<tr><td>");
echo("X:");
echo("</td><td>");
echo("<input type=\"text\" name=\"dimension_x\" value=\"$dimension_x\" size=\"4\" maxlength=\"6\" >");
echo("</td>");
echo("<td>");
echo("<select name=\"dimension_x_uom\" class=\"sel3\" >\n");
$Query = "select code, description from uom where uom_type = 'DT' order by description "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Dim X UOM!<BR>\n");
	exit;
}
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] == $dimension_x_uom)
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
echo("</td><td>");
echo("</td>");
echo("</tr>");

echo("<tr><td>");
echo("Y:");
echo("</td><td>");
echo("<input type=\"text\" name=\"dimension_y\" value=\"$dimension_y\" size=\"4\" maxlength=\"6\" >");
echo("</td>");
echo("<td>");
echo("<select name=\"dimension_y_uom\" class=\"sel3\" >\n");
$Query = "select code, description from uom where uom_type = 'DT' order by description "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Dim X UOM!<BR>\n");
	exit;
}
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] == $dimension_y_uom)
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
echo("</td><td>");
echo("</td>");
echo("</tr>");

echo ("</table>\n");
echo("</div>");

echo("<div id=\"p3\" name=\"p3\">");
echo("<table border=\"0\" align=\"left\" id=\"qtys3\" name=\"qtys3\" class=\"qtys\">");
//echo("<tr><th></th><th>Qty</th><th>UoM</th></tr>");
echo("<tr><th></th><th>Qty</th><th>UoM</th></tr>");
echo("<tr><td>");
echo("Z:");
echo("</td><td>");
echo("<input type=\"text\" name=\"dimension_z\" value=\"$dimension_z\" size=\"4\" maxlength=\"6\" >");
echo("</td>");
echo("<td>");
echo("<select name=\"dimension_z_uom\" class=\"sel3\" >\n");
$Query = "select code, description from uom where uom_type = 'DT' order by description "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Dim X UOM!<BR>\n");
	exit;
}
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] == $dimension_z_uom)
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
echo("</td><td>");
echo("</td>");
echo("</tr>");

echo ("</table>\n");
echo("</div>");
//release memory
//$Result->free();
//ibase_free_result($Result);

//commit
//$Link->commit();
ibase_commit($dbTran);

//close
//$Link->disconnect();
//ibase_close($Link);

/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<form action=\"getlocn.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</form>\n");
}
else
{
	// html 4.0 browser
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='getlocn.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
}
*/
if (isset($from))
{
	$backto = $from;
}
else
{
	$backto = 'getlocn.php';
}
$endtable = "Y";
echo("<div class=\"buttonsgroup\">");
{
	//echo("<br><br><br><br><br><br><br><br><br>");
	// Create a table.
	echo ("<table border=\"0\" align=\"bottom\">");
	echo ("<tr>");
	echo ("<td>");
	if (isset($grn))
	{
		echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\">");
	}
	if (isset($from))
	{
		echo("<input type=\"hidden\" name=\"from\" value=\"$from\">");
	}

	//echo("<INPUT type=\"IMAGE\" ");  
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
	if (isset($from))
	{
		$alt = "Accept";
		echo('SRC="/icons/whm/accept.gif" alt="' . $alt . '">');
	}
	else
	{
		$alt = "View Locations";
		echo('SRC="/icons/whm/locations.gif" alt="' . $alt . '">');
	}
/*
	echo('SRC="/icons/whm/button.php?text=' . urlencode($alt) . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
*/
	echo("</form>");
	echo ("</td>");
	echo ("<td>");
	echo("<form action=\"" . $backto . "\" method=\"post\" name=back>\n");
	if (isset($grn))
	{
		echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\">");
	}
	if (isset($product))
	{
		echo("<input type=\"hidden\" name=\"product\" value=\"$product\">");
	}

	//echo("<INPUT type=\"IMAGE\" ");  
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/Back_50x100.gif" alt="Back">');
	echo("<input type=\"hidden\" name=\"cancelproduct\" value=\"T\">");
	echo("</form>");
	echo ("</td>");
	echo ("<td >");
		echo("<div class=\"nextButton\"><input type=\"button\" id=\"nextScrn\" onclick=\"doNextSrcn();\"></div>");
		//echo("<input type=\"button\" name=\"nextScrn\" value=\"Next\" id=\"nextScrn\" title=\"Next\" onclick=\doNext()\" class=\"buttons\"");
		//echo("<button name=\"nextSrcn\" type=\"button\" onclick=\"doNextSrcn();\"  >\n");
		//echo("Next Screen");
		//echo("<img src=\"/icons/whm/more.gif\" alt=\"Next Screen\" width=\"$rimg_width\"  ></button>\n");
		//echo("</button>\n");
	echo ("</td>");
	echo ("</tr>");
	if ($endtable == "Y")
	{
		echo ("</table>");
	}
	echo("<form action=\"" . $_SERVER["PHP_SELF"] . "\" method=\"post\" name=reget>\n");
	if (isset($grn))
	{
		echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\">");
	}
	if (isset($product))
	{
		echo("<input type=\"hidden\" name=\"product\" value=\"$product\">");
	}
	if (isset($from))
	{
		echo("<input type=\"hidden\" name=\"from\" value=\"$from\">");
	}
	if (isset($from2))
	{
		echo("<input type=\"hidden\" name=\"from2\" value=\"$from2\">");
	}
	if (isset($wk_add_product)) 
	{
		echo("<input type=\"hidden\" name=\"addproduct\" value=\"$wk_add_product\">");
	}
	{
		echo("<input type=\"hidden\" name=\"company\" >");
	}

	echo("</form>");
	echo("<form action=\"" . $_SERVER["PHP_SELF"] . "\" method=\"post\" name=\"message\">\n");
	echo("<input type=\"text\" name=\"message\" readonly size=\"30\" class=\"message\">\n");
	echo("</form>");
}

echo ("</div>\n");
?>

</body>
</html>
