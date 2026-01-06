<?php
include "../login.inc";
?>
<html>
 <head>
  <title>Find Product</title>
<?php
include "viewport.php";
if (strpos($_SERVER['HTTP_USER_AGENT'] , "NetFront") === false)
{
	echo('<link rel=stylesheet type="text/css" href="prodsrch.css">');
}
else
{
	echo('<link rel=stylesheet type="text/css" href="prodsrch.css">');
}
?>
<script type="text/javascript">
function errorHandler(errorMessage,url,line) 
{
	document.write("<p><b>Error in JS:</b> "+errorMessage+"<br>")
	document.write("<b>URL:</b> "+url+"<br>")
	document.write("<b>Line:</b> "+line+"</p>")
	return true
}
function doProduct() {
/*
	document.getprodback.product.value=document.getprodname.product.options[document.getprodname.product.selectedIndex].value;
*/
	return true;
}

function saveMe(myorder) {

/* # save my order */
  var mytype;
	/* alert("in saveMe"); */
	/* alert(myorder); */
  	document.getprodname.product.value = myorder; 
  	document.getprodback.product.value = myorder; 
  	document.getprodback.submit(); 
	return true;
}

/* onerror = errorHandler */
 </script>
<style type="text/css">
body {
     font-family: sans-serif;
     font-size: 1em;
     border: 0; padding: 0; margin: 0;
}
form, div {
    border: 0; padding:0 ; margin: 0;
}
table {
    border: 0; padding: 0; margin: 0;
}
</style>
 </head>
<body>
<?php
require_once 'DB.php';
require 'db_access.php';
include 'transaction.php';
include 'logme.php';
include "repage.php";

function errorHandler2( $errno, $errstr, $errfile, $errline, $errcontext)
{

	//$log = fopen('/tmp/logme.log' , 'a');
	$log = fopen('/data/tmp/prodsrch.logme.log' , 'a');
	$datetime  = strftime("%D %T "); 
	if (isset($_COOKIE['LoginUser']))
	{
		list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
		$userline = sprintf("%s  by %s on %s\n", $datetime, $tran_user, $tran_device);
	} else {
		$userline = sprintf("%s  \n", $datetime );
	}

	fwrite($log, $userline);
	fwrite($log, $errno);
	fwrite($log,"  ");
	fwrite($log, $errstr);
	fwrite($log,"  \n");
	fwrite($log, $errfile);
	fwrite($log," line ");
	fwrite($log, $errline);
	fwrite($log,"  \n");
	fwrite($log, print_r($errcontext, true));
	fwrite($log,"\n");
	fclose($log);
}

$received_ssn_qty = 0;
if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	
if (isset($_COOKIE['BDCSData']))
{
	list($received_qty, $type, $order, $line, $carrier, $vehicle, $container, $pallet_type, $pallet_qty, $consignment, $grn, $problem) = explode("|", $_COOKIE["BDCSData"]);
}

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

set_error_handler('errorHandler2');
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
	$from = getBDCScookie($Link, $tran_device, "prodsrchfrom" );
	$from2 = getBDCScookie($Link, $tran_device, "prodsrchfrom2" );
	$prodsrchtext = getBDCScookie($Link, $tran_device, "prodsrchtext" );
	$owner = getBDCScookie($Link, $tran_device, "owner" );
}


function dopager( $Query)
{
	global $Host, $User, $Password, $DBName2;
	//load the adodb code
	require 'adodb/adodb.inc.php';
	//require 'adodb/adodb-pager.inc.php';
	require 'adodb/bdcs_pager.php';

	//connect to the database
	//$conn = &ADONewConnection('ibase');
	$conn = &ADONewConnection('firebird');
	list($myhost,$mydb) = explode(":", $DBName2,2);

	$conn->connect($Host,$User,$Password,$mydb);

	//send a select
	$pager = new ADODB_Pager($conn,$Query,'Products',true);
	$pager->Render(2);
	$conn->Close();

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
if (!isset($printer))
{
	$printer = "PA";
}
if (isset($_POST['retfrom']))
{
	$retfrom = $_POST['retfrom'];
}
if (isset($_GET['retfrom']))
{
	$retfrom = $_GET['retfrom'];
}
if (isset($_POST['owner']))
{
	$owner = $_POST['owner'];
}
if (isset($_GET['owner']))
{
	$owner = $_GET['owner'];
}
if (isset($_POST['received_ssn_qty']))
{
	$received_ssn_qty = $_POST['received_ssn_qty'];
}
if (isset($_GET['received_ssn_qty']))
{
	$received_ssn_qty = $_GET['received_ssn_qty'];
}
if (isset($_POST['location']))
{
	$location = $_POST['location'];
}
if (isset($_GET['location']))
{
	$location = $_GET['location'];
}
if (isset($_POST['from']))
{
	$from = $_POST['from'];
}
if (isset($_GET['from']))
{
	$from = $_GET['from'];
}
if (isset($_POST['from2']))
{
	$from2 = $_POST['from2'];
}
if (isset($_GET['from2']))
{
	$from2 = $_GET['from2'];
}
if (isset($_POST['problem']))
{
	$problem = $_POST['problem'];
}
if (isset($_GET['problem']))
{
	$problem = $_GET['problem'];
}
if (isset($_POST['uom']))
{
	$uom = $_POST['uom'];
}
if (isset($_GET['uom']))
{
	$uom = $_GET['uom'];
}
if (isset($_POST['product']))
{
	$product = $_POST['product'];
}
if (isset($_GET['product']))
{
	$product = $_GET['product'];
}
if (isset($_POST['label_qty1']))
{
	$label_qty1 = $_POST['label_qty1'];
}
if (isset($_GET['label_qty1']))
{
	$label_qty1 = $_GET['label_qty1'];
}
if (isset($_POST['label_qty2']))
{
	$label_qty2 = $_POST['label_qty2'];
}
if (isset($_GET['label_qty2']))
{
	$label_qty2 = $_GET['label_qty2'];
}
if (isset($_POST['ssn_qty1']))
{
	$ssn_qty1 = $_POST['ssn_qty1'];
}
if (isset($_GET['ssn_qty1']))
{
	$ssn_qty1 = $_GET['ssn_qty1'];
}
if (isset($_POST['ssn_qty2']))
{
	$ssn_qty2 = $_POST['ssn_qty2'];
}
if (isset($_GET['ssn_qty2']))
{
	$ssn_qty2 = $_GET['ssn_qty2'];
}
if (isset($_POST['printed_ssn_qty']))
{
	$printed_ssn_qty = $_POST['printed_ssn_qty'];
}
if (isset($_GET['printed_ssn_qty']))
{
	$printed_ssn_qty = $_GET['printed_ssn_qty'];
}
if (isset($_POST['x']))
{
	$image_x = $_POST['x'];
}
if (isset($_GET['x']))
{
	$image_x = $_GET['x'];
}
if (isset($_POST['y']))
{
	$image_y = $_POST['y'];
}
if (isset($_GET['y']))
{
	$image_y = $_GET['y'];
}
if (isset($_POST['prodsrchtext']))
{
	$prodsrchtext = $_POST['prodsrchtext'];
}
if (isset($_GET['prodsrchtext']))
{
	$prodsrchtext = $_GET['prodsrchtext'];
}
if (isset($_POST['other_qty1']))
{
	$other_qty1 = $_POST['other_qty1'];
}
if (isset($_GET['other_qty1']))
{
	$other_qty1 = $_GET['other_qty1'];
}
if (isset($_POST['other_qty2']))
{
	$other_qty2 = $_POST['other_qty2'];
}
if (isset($_GET['other_qty2']))
{
	$other_qty2 = $_GET['other_qty2'];
}
if (isset($_POST['other_qty3']))
{
	$other_qty3 = $_POST['other_qty3'];
}
if (isset($_GET['other_qty3']))
{
	$other_qty3 = $_GET['other_qty3'];
}
if (isset($_POST['other_qty4']))
{
	$other_qty4 = $_POST['other_qty4'];
}
if (isset($_GET['other_qty4']))
{
	$other_qty4 = $_GET['other_qty4'];
}
{
/*
	setBDCScookie($Link, $tran_device, "grn", $grn);
	setBDCScookie($Link, $tran_device, "type", $type);
	setBDCScookie($Link, $tran_device, "order", $order);
	setBDCScookie($Link, $tran_device, "line", $line);
*/

	setBDCScookie($Link, $tran_device, "retfrom", $retfrom);
	setBDCScookie($Link, $tran_device, "product", $product);
	setBDCScookie($Link, $tran_device, "label_qty1", $label_qty1);
	setBDCScookie($Link, $tran_device, "ssn_qty1", $ssn_qty1);
	setBDCScookie($Link, $tran_device, "weight_qty1", $weight_qty1);
	setBDCScookie($Link, $tran_device, "weight_uom", $weight_uom);
	setBDCScookie($Link, $tran_device, "printer", $printer);
	setBDCScookie($Link, $tran_device, "received_ssn_qty", $received_ssn_qty);
	setBDCScookie($Link, $tran_device, "location", $location);
	setBDCScookie($Link, $tran_device, "uom", $uom);
	setBDCScookie($Link, $tran_device, "printed_ssn_qty", $printed_ssn_qty);
	if (isset($from)) {
		setBDCScookie($Link, $tran_device, "prodsrchfrom", $from);
	}
	if (isset($from2)) {
		setBDCScookie($Link, $tran_device, "prodsrchfrom2", $from2);
	}
	if (isset($prodsrchtext)) {
		setBDCScookie($Link, $tran_device, "prodsrchtext", $prodsrchtext);
	}
	if (isset($owner)) {
		setBDCScookie($Link, $tran_device, "owner", $owner);
	}
}

//echo("<FONT size=\"2\">\n");
echo("<div id=\"col3\">");
echo("<form action=\"prodsrch.php\" method=\"post\" name=\"getprodname\">");
if (isset($type))
{
	echo("<input type=\"hidden\" name=\"type\" value=\"$type\" >");
}
if (isset($grn))
{
	echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\" >");
}
if (isset($retfrom))
{
	echo("<input type=\"hidden\" name=\"retfrom\" value=\"$retfrom\" >");
}
if (isset($owner))
{
	echo("<input type=\"hidden\" name=\"owner\" value=\"$owner\" >");
}
if (isset($received_ssn_qty))
{
	echo("<input type=\"hidden\" name=\"received_ssn_qty\" value=\"$received_ssn_qty\" >");
}
if (isset($printed_ssn_qty))
{
	echo("<input type=\"hidden\" name=\"printed_ssn_qty\" value=\"$printed_ssn_qty\" >");
}
if (isset($location))
{
	echo("<input type=\"hidden\" name=\"location\" value=\"$location\" >");
}
if (isset($printer))
{
	echo("<input type=\"hidden\" name=\"printer\" value=\"$printer\" >");
}
if (isset($problem))
{
	echo("<input type=\"hidden\" name=\"problem\" value=\"$problem\" >");
}
if (isset($from))
{
	echo("<input type=\"hidden\" name=\"from\" value=\"$from\" >");
}
if (isset($uom))
{
	echo("<input type=\"hidden\" name=\"uom\" value=\"$uom\" >");
}
if (isset($label_qty1))
{
	echo("<input type=\"hidden\" name=\"label_qty1\" value=\"$label_qty1\" >");
}
if (isset($label_qty2))
{
	echo("<input type=\"hidden\" name=\"label_qty2\" value=\"$label_qty2\" >");
}
if (isset($ssn_qty1))
{
	echo("<input type=\"hidden\" name=\"ssn_qty1\" value=\"$ssn_qty1\" >");
}
if (isset($ssn_qty2))
{
	echo("<input type=\"hidden\" name=\"ssn_qty2\" value=\"$ssn_qty2\" >");
}
if (isset($other_qty1))
{
	echo("<input type=\"hidden\" name=\"other_qty1\" value=\"$other_qty1\" >");
}
if (isset($other_qty2))
{
	echo("<input type=\"hidden\" name=\"other_qty2\" value=\"$other_qty2\" >");
}
if (isset($other_qty3))
{
	echo("<input type=\"hidden\" name=\"other_qty3\" value=\"$other_qty3\" >");
}
if (isset($other_qty4))
{
	echo("<input type=\"hidden\" name=\"other_qty4\" value=\"$other_qty4\" >");
}

echo("<input type=\"text\" name=\"message\" readonly size=\"30\" class=\"message\"><br>\n");
/*
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
		//echo("Load No.:");
		echo("Non-Product:");
		break;
	case "LP":
		echo("Load No:");
		break;
}
echo("<input type=\"text\" name=\"order\" maxlength=\"10\" size=\"10\" readonly value=\"$order\">\n");
echo("<input type=\"text\" name=\"line\" maxlength=\"4\" size=\"4\" readonly value=\"$line\">\n");
//echo("<input type=\"text\" name=\"displaygrn\" maxlength=\"4\" size=\"4\" readonly value=\"$grn\"><BR>\n");
*/
echo("Search For:<input type=\"text\" name=\"prodsrchtext\" ");
if (isset($prodsrchtext))
{
	echo(" value=\"$prodsrchtext\"");
}
echo(" size=\"15\" maxlength=\"128\" onchange=\"document.getprodname.submit()\" ><br>\n");
if (isset($prodsrchtext))
{
//=====================================================================================================

$got_ssn = 0;

// for pagination
// want to know # lines in total
$wkNumRows = 0;
{
	
	$Query2 = "SELECT COUNT(*)  FROM  ";
	$Query2 .= "(SELECT  ";
	//$Query2 .= " prod_id, long_desc, short_desc from prod_profile where upper(long_desc) like '%" . strtoupper($prodsrchtext) . "%' ";
	$Query2 .= " prod_id, long_desc, short_desc, alternate_id from prod_profile ";
	if (isset($owner)) {
		$Query2 .= " where company_id in ('ALL','" . $owner . "') AND ( ";
	} else {
		$Query2 .= " where company_id in ('ALL','') AND ( ";
	}
	$Query2 .= " upper(long_desc) like '%" . strtoupper($prodsrchtext) . "%' ";
	$Query2 .= "or  upper(short_desc) like '%" . strtoupper($prodsrchtext) . "%' ";
	//$Query2 .= "or  upper(prod_id) like '%" . strtoupper($prodsrchtext) . "%' order by long_desc, short_desc, prod_id  ";
	$Query2 .= "or  upper(prod_id) like '%" . strtoupper($prodsrchtext) . "%' ";
	$Query2 .= "or  upper(alternate_id) like '%" . strtoupper($prodsrchtext) . "%' ) ";
	$Query2 .= " order by long_desc, short_desc, prod_id  ";
	$Query2 .= "  ) ";
	//echo($Query2);
	if (!($Result2 = ibase_query($Link, $Query2)))
	{
		echo("Unable to Read Orders!<BR>\n");
		exit();
	}
	while (($Row2 = ibase_fetch_row($Result2)) )  {
		$wkNumRows = $Row2[0];
	}
	//release memory
	ibase_free_result($Result2);
	//echo "PS NumRows:" . $wkNumRows;
}
	//release memory
	//ibase_free_result($Result);
// and lines per page
$wkLinesPerPage = 2;
	//$wkQueryList = " prod_id,long_desc, short_desc from prod_profile where upper(long_desc) like '%" . strtoupper($prodsrchtext) . "%' ";
	//$wkQueryList = " prod_id,case when long_desc is null then short_desc else long_desc end as prod_desc  from prod_profile where upper(long_desc) like '%" . strtoupper($prodsrchtext) . "%' ";
	//$wkQueryList = " prod_id as Product,v4substring(case when long_desc is null then short_desc else long_desc end,1,30) as Description  from prod_profile ";
	$wkQueryList = " prod_id as Product,case when long_desc is null and strlen(short_desc) <= 30 then short_desc ";
	$wkQueryList .= " when long_desc is null and strlen(short_desc) > 30 then v4substring(short_desc,1,30) ";
	$wkQueryList .= " when strlen(long_desc) > 30 then v4substring(long_desc,1,30) ";
        $wkQueryList .= " else long_desc end as Description  from prod_profile ";
	if (isset($owner)) {
		$wkQueryList .= " where company_id in ('ALL','" . $owner . "') AND ( ";
	} else {
		$wkQueryList .= " where company_id in ('ALL','') AND ( ";
	}
	$wkQueryList .= " upper(long_desc) like '%" . strtoupper($prodsrchtext) . "%' ";
	$wkQueryList .= "or  upper(short_desc) like '%" . strtoupper($prodsrchtext) . "%' ";
	//$wkQueryList .= "or  upper(prod_id) like '%" . strtoupper($prodsrchtext) . "%' order by long_desc, short_desc, prod_id";
	$wkQueryList .= "or  upper(prod_id) like '%" . strtoupper($prodsrchtext) . "%' ";
	//$wkQueryList .= "or  upper(alternate_id) like '%" . strtoupper($prodsrchtext) . "%' order by long_desc, short_desc, prod_id";
	$wkQueryList .= "or  upper(alternate_id) like '%" . strtoupper($prodsrchtext) . "%' )";
	$wkQueryList .= " order by long_desc, short_desc, prod_id";
	//echo $wkQueryList;


// echo headers
$wkHeaders = "<table BORDER=\"1\" class=\"pg\">\n";
$wkHeaders .= "<tr>";
$wkHeaders .= "<th></th>\n";
$wkHeaders .= "<th>Product</th>\n";
$wkHeaders .= "<th>Description</th>\n";
$wkHeaders .= "</tr>";

bdcsRepage($Link, $wkNumRows, $wkLinesPerPage, $wkQueryList, $wkHeaders, 'T');

// ===================================================================================

$wkQueryList2 = "select " . $wkQueryList;
//dopager($wkQueryList2);

// ==================================================================================

//====================================================================================================
/*
	echo("Product:<SELECT name=\"product\" class=\"sel7\"");
	echo(" onclick=\"return doProduct();\" ");
	echo(" onchange=\"return doProduct();\" >");
	//$Query = "select prod_id, long_desc from prod_profile where upper(long_desc) like '%" . strtoupper($prodsrchtext) . "%' order by long_desc";
	$Query = "select prod_id, long_desc, short_desc from prod_profile where upper(long_desc) like '%" . strtoupper($prodsrchtext) . "%' ";
	$Query .= "or  upper(short_desc) like '%" . strtoupper($prodsrchtext) . "%' ";
	$Query .= "or  upper(prod_id) like '%" . strtoupper($prodsrchtext) . "%' order by long_desc, short_desc, prod_id";
	//$Query .= "union select prod_id, short_desc from prod_profile where long_desc is null and upper(short_desc) like '%" . strtoupper($prodsrchtext) . "%' order by short_desc";
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Devices!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		if ($Row[0] == $product)
		{
			//echo( "<OPTION value=\"$Row[0]\" selected >$Row[1]\n");
			if ($Row[1] <> '') {
				echo( "<OPTION value=\"$Row[0]\" selected >$Row[0] $Row[1]\n");
			} else {
				echo( "<OPTION value=\"$Row[0]\" selected >$Row[0] $Row[2]\n");
			}
		}
		else
		{
			//echo( "<OPTION value=\"$Row[0]\">$Row[1]\n");
			if ($Row[1] <> '') {
				echo( "<OPTION value=\"$Row[0]\">$Row[0] $Row[1]\n");
			} else {
				echo( "<OPTION value=\"$Row[0]\">$Row[0] $Row[2]\n");
			}
		}
	}
	//release memory
	ibase_free_result($Result);
	echo("</SELECT><BR>\n");
*/
		echo("<input type=\"hidden\" name=\"product\" >");
}
else
{
	if (isset($product))
	{
		echo("<input type=\"hidden\" name=\"product\" value=\"$product\" >");
	}
}
echo("</div>\n");
echo("<div id=\"col6\">");
{
	// html 4.0 browser
	$alt = "Search";
	// Create a table.
	echo ("<table BORDER=\"0\" ALIGN=\"LEFT\" id=\"col1\">");
	echo ("<tr>");
	echo ("<td>");
	//echo("<input type=\"IMAGE\" ");  
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
/*
	echo('SRC="/icons/whm/accept.gif" alt="' . $alt . '">');
*/
	echo("</form>");
	echo ("</td>");
	echo ("<td>");
	echo("<form action=\"" . $from . "\"  method=\"post\"  name=getprodback onsubmit=\"return doProduct();\">\n");
	//echo("<input type=\"IMAGE\" ");  
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/Back_50x100.gif" alt="Back">');
	echo("<input type=\"hidden\" name=\"type\" value=\"$type\" >");
	echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	echo("<input type=\"hidden\" name=\"order\" value=\"$order\">\n");
	echo("<input type=\"hidden\" name=\"line\" value=\"$line\">\n");
	if (isset($retfrom))
	{
		echo("<input type=\"hidden\" name=\"retfrom\" value=\"$retfrom\" >");
	}
	if (isset($owner))
	{
		echo("<input type=\"hidden\" name=\"owner\" value=\"$owner\" >");
	}
	if (isset($received_ssn_qty))
	{
		echo("<input type=\"hidden\" name=\"received_ssn_qty\" value=\"$received_ssn_qty\" >");
	}
	if (isset($printed_ssn_qty))
	{
		echo("<input type=\"hidden\" name=\"printed_ssn_qty\" value=\"$printed_ssn_qty\" >");
	}
	if (isset($location))
	{
		echo("<input type=\"hidden\" name=\"location\" value=\"$location\" >");
	}
	if (isset($printer))
	{
		echo("<input type=\"hidden\" name=\"printer\" value=\"$printer\" >");
	}
	if (isset($problem))
	{
		echo("<input type=\"hidden\" name=\"problem\" value=\"$problem\" >");
	}
	if (isset($uom))
	{
		echo("<input type=\"hidden\" name=\"uom\" value=\"$uom\" >");
	}
	if (isset($product))
	{
		echo("<input type=\"hidden\" name=\"product\" value=\"$product\" >");
	} else {
		echo("<input type=\"hidden\" name=\"product\" value=\"\" >");
	}
	if (isset($label_qty1))
	{
		echo("<input type=\"hidden\" name=\"label_qty1\" value=\"$label_qty1\" >");
	}
	if (isset($label_qty2))
	{
		echo("<input type=\"hidden\" name=\"label_qty2\" value=\"$label_qty2\" >");
	}
	if (isset($ssn_qty1))
	{
		echo("<input type=\"hidden\" name=\"ssn_qty1\" value=\"$ssn_qty1\" >");
	}
	if (isset($ssn_qty2))
	{
		echo("<input type=\"hidden\" name=\"ssn_qty2\" value=\"$ssn_qty2\" >");
	}
	if (isset($other_qty1))
	{
		echo("<input type=\"hidden\" name=\"other_qty1\" value=\"$other_qty1\" >");
	}
	if (isset($other_qty2))
	{
		echo("<input type=\"hidden\" name=\"other_qty2\" value=\"$other_qty2\" >");
	}
	if (isset($other_qty3))
	{
		echo("<input type=\"hidden\" name=\"other_qty3\" value=\"$other_qty3\" >");
	}
	if (isset($other_qty4))
	{
		echo("<input type=\"hidden\" name=\"other_qty4\" value=\"$other_qty4\" >");
	}
	echo("</form>");
	echo ("</td>");
	echo ("<td>");
	$alt = "Add Product";
	echo("<form action=\"../query/product.php\"  method=\"post\" name=getproduct>\n");
	//echo("<input type=\"IMAGE\" ");  
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
/*
	echo('SRC="/icons/whm/accept.gif" alt="' . $alt . '">');
*/

	echo("<input type=\"hidden\" name=\"type\" value=\"$type\" >");
	echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	echo("<input type=\"hidden\" name=\"order\" value=\"$order\">\n");
	echo("<input type=\"hidden\" name=\"line\" value=\"$line\">\n");
	if (isset($retfrom))
	{
		echo("<input type=\"hidden\" name=\"retfrom\" value=\"$retfrom\" >");
	}
	if (isset($owner))
	{
		echo("<input type=\"hidden\" name=\"owner\" value=\"$owner\" >");
	}
	if (isset($received_ssn_qty))
	{
		echo("<input type=\"hidden\" name=\"received_ssn_qty\" value=\"$received_ssn_qty\" >");
	}
	if (isset($printed_ssn_qty))
	{
		echo("<input type=\"hidden\" name=\"printed_ssn_qty\" value=\"$printed_ssn_qty\" >");
	}
	if (isset($location))
	{
		echo("<input type=\"hidden\" name=\"location\" value=\"$location\" >");
	}
	if (isset($printer))
	{
		echo("<input type=\"hidden\" name=\"printer\" value=\"$printer\" >");
	}
	if (isset($problem))
	{
		echo("<input type=\"hidden\" name=\"problem\" value=\"$problem\" >");
	}
	if (isset($uom))
	{
		echo("<input type=\"hidden\" name=\"uom\" value=\"$uom\" >");
	}
	if (isset($product))
	{
		echo("<input type=\"hidden\" name=\"product\" value=\"$product\" >");
	} else {
		echo("<input type=\"hidden\" name=\"product\" value=\"\" >");
	}
	if (isset($label_qty1))
	{
		echo("<input type=\"hidden\" name=\"label_qty1\" value=\"$label_qty1\" >");
	}
	if (isset($label_qty2))
	{
		echo("<input type=\"hidden\" name=\"label_qty2\" value=\"$label_qty2\" >");
	}
	if (isset($ssn_qty1))
	{
		echo("<input type=\"hidden\" name=\"ssn_qty1\" value=\"$ssn_qty1\" >");
	}
	if (isset($ssn_qty2))
	{
		echo("<input type=\"hidden\" name=\"ssn_qty2\" value=\"$ssn_qty2\" >");
	}
	if (isset($other_qty1))
	{
		echo("<input type=\"hidden\" name=\"other_qty1\" value=\"$other_qty1\" >");
	}
	if (isset($other_qty2))
	{
		echo("<input type=\"hidden\" name=\"other_qty2\" value=\"$other_qty2\" >");
	}
	if (isset($other_qty3))
	{
		echo("<input type=\"hidden\" name=\"other_qty3\" value=\"$other_qty3\" >");
	}
	if (isset($other_qty4))
	{
		echo("<input type=\"hidden\" name=\"other_qty4\" value=\"$other_qty4\" >");
	}
	echo("<INPUT type=\"hidden\" name=\"from\" value=\"$from2\" >");
	echo("<INPUT type=\"hidden\" name=\"addproduct\" value=\"T\" >");
	echo("</form>");
	echo ("</tr>");
	echo ("</table>");

}
?>
<script type="text/javascript">
<?php
{
	if (isset($prodsrchtext))
	{
		echo("document.getprodname.message.value=\"Choose Product to Use\";\n");
		//echo("document.getprodname.product.focus();\n");
	}
	else
	{
		echo("document.getprodname.message.value=\"Enter Title to Search for\";\n");
		echo("document.getprodname.prodsrchtext.focus();\n");
	}
}
	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
	}
		
	//commit
	ibase_commit($dbTran);
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
?>
</script>
</div>
</body>
</html>

