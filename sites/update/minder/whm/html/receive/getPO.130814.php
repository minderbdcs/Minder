<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
include "../login.inc";
?>
<html>
 <head>
  <title>Receive Select Order to Receive</title>
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
function processBack() {
	var dowhat;
/*
	dowhat=confirm("Cancel Delivery?");
	if(dowhat)
	{
		document.forms.getssnback.submit();
		return true;
	}
	else
	{
  		document.getpo.message.value="Select the Purchase Order";
		return false;
	}
*/
	/* treat as if yes is wanted in cancel */
	document.forms.getssnback.submit();
	return true;
}

function saveMe(myorder) {

/* # save my order */
  var mytype;
	/* alert("in saveMe"); */
	/* alert(myorder); */
  	document.getperson.selorder.value = myorder; 
  	document.getperson.submit(); 
	return true;
}
/* onerror = errorHandler */
</script>

<?php
require_once 'DB.php';
require 'db_access.php';
include "logme.php";


function dopager( $Query)
{
	global $Host, $User, $Password, $DBName2;
	//load the adodb code
	require 'adodb/adodb.inc.php';
	//require 'adodb/adodb-pager.inc.php';
	require 'adodb/bdcs_pager.php';

	//connect to the database
	$conn = &ADONewConnection('ibase');
	list($myhost,$mydb) = explode(":", $DBName2,2);
	//$conn->connect($Host,'sysdba','masterkey','d:/asset.rf/database/wh.v39.gdb');
	//$conn->connect($Host,$User,$Password,'/data/asset.rf/wh.v39.gdb');
	//echo "Host:" . $Host;
	//echo "User:" . $User;
	//echo "Password:" . $Password;
	//echo "DB:" . $mydb;

	$conn->connect($Host,$User,$Password,$mydb);

	//send a select
	$pager = new ADODB_Pager($conn,$Query,'PurchaseOrders',true);
	$pager->Render(4);

}

function errorHandler2( $errno, $errstr, $errfile, $errline, $errcontext)
{

	$log = fopen('/tmp/logme.log' , 'a');
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

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	
/* 
if (isset($_COOKIE['BDCSData']))
{
	list($received_qty, $type, $order, $line, $carrier, $vehicle, $container, $pallet_type, $pallet_qty, $consignment, $grn) = explode("|", $_COOKIE["BDCSData"] . "|||||||||||");
}
*/

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

set_error_handler('errorHandler2');

{
	$type = getBDCScookie($Link, $tran_device, "type" );
	$order = getBDCScookie($Link, $tran_device, "order" );
	$line = getBDCScookie($Link, $tran_device, "line" );
	$carrier = getBDCScookie($Link, $tran_device, "carrier" );
	$vehicle = getBDCScookie($Link, $tran_device, "vehicle" );
	$container = getBDCScookie($Link, $tran_device, "container" );
	$pallet_type = getBDCScookie($Link, $tran_device, "pallet_type" );
	$pallet_qty = getBDCScookie($Link, $tran_device, "pallet_qty" );
	$received_qty = getBDCScookie($Link, $tran_device, "received_qty" );
	$consignment = getBDCScookie($Link, $tran_device, "consignment" );
	$grn = getBDCScookie($Link, $tran_device, "grn" );
	$problem = getBDCScookie($Link, $tran_device, "problem" );
	$other1 = getBDCScookie($Link, $tran_device, "other1" );
}
if (isset($_POST['type']))
{
	$type = $_POST['type'];
	setBDCScookie($Link, $tran_device, "type", $type);
}
if (isset($_GET['type']))
{
	$type = $_GET['type'];
	setBDCScookie($Link, $tran_device, "type", $type);
}
if (isset($_POST['order']))
{
	$order = $_POST['order'];
	$new_order = $order;
	setBDCScookie($Link, $tran_device, "order", $order);
}
if (isset($_GET['order']))
{
	$order = $_GET['order'];
	$new_order = $order;
	setBDCScookie($Link, $tran_device, "order", $order);
}
if (isset($_POST['line']))
{
	$line = $_POST['line'];
	$new_line = $line;
	setBDCScookie($Link, $tran_device, "line", $line);
}
if (isset($_GET['line']))
{
	$line = $_GET['line'];
	$new_line = $line;
	setBDCScookie($Link, $tran_device, "line", $line);
}
if (isset($_POST['carrier']))
{
	$carrier = $_POST['carrier'];
	setBDCScookie($Link, $tran_device, "carrier", $carrier);
}
if (isset($_GET['carrier']))
{
	$carrier = $_GET['carrier'];
	setBDCScookie($Link, $tran_device, "carrier", $carrier);
}
if (isset($_POST['vehicle']))
{
	$vehicle = $_POST['vehicle'];
	setBDCScookie($Link, $tran_device, "vehicle", $vehicle);
}
if (isset($_GET['vehicle']))
{
	$vehicle = $_GET['vehicle'];
	setBDCScookie($Link, $tran_device, "vehicle", $vehicle);
}
if (isset($_POST['container']))
{
	$container = $_POST['container'];
	setBDCScookie($Link, $tran_device, "container", $container);
}
if (isset($_GET['container']))
{
	$container = $_GET['container'];
	setBDCScookie($Link, $tran_device, "container", $container);
}
if (isset($_POST['pallet_type']))
{
	$pallet_type = $_POST['pallet_type'];
	setBDCScookie($Link, $tran_device, "pallet_type", $pallet_type);
}
if (isset($_GET['pallet_type']))
{
	$pallet_type = $_GET['pallet_type'];
	setBDCScookie($Link, $tran_device, "pallet_type", $pallet_type);
}
if (isset($_POST['pallet_qty']))
{
	$pallet_qty = $_POST['pallet_qty'];
	setBDCScookie($Link, $tran_device, "pallet_qty", $pallet_qty);
}
if (isset($_GET['pallet_qty']))
{
	$pallet_qty = $_GET['pallet_qty'];
	setBDCScookie($Link, $tran_device, "pallet_qty", $pallet_qty);
}
if (isset($_POST['received_qty']))
{
	$received_qty = $_POST['received_qty'];
	setBDCScookie($Link, $tran_device, "received_qty", $received_qty);
}
if (isset($_GET['received_qty']))
{
	$received_qty = $_GET['received_qty'];
	setBDCScookie($Link, $tran_device, "received_qty", $received_qty);
}
if (isset($_POST['consignment']))
{
	$consignment = $_POST['consignment'];
	setBDCScookie($Link, $tran_device, "consignment", $consignment);
}
if (isset($_GET['consignment']))
{
	$consignment = $_GET['consignment'];
	setBDCScookie($Link, $tran_device, "consignment", $consignment);
}
if (isset($_POST['grn']))
{
	$grn = $_POST['grn'];
	setBDCScookie($Link, $tran_device, "grn", $grn);
}
if (isset($_GET['grn']))
{
	$grn = $_GET['grn'];
	setBDCScookie($Link, $tran_device, "grn", $grn);
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
if (!isset($printer))
{
	$printer = "PA";
}
if (isset($_POST['other1']))
{
	$other1 = $_POST['other1'];
	setBDCScookie($Link, $tran_device, "other1", $other1);
}
if (isset($_GET['other1']))
{
	$other1 = $_GET['other1'];
	setBDCScookie($Link, $tran_device, "other1", $other1);
}


echo("<div id=\"col3\">");
//echo("<form action=\"getPO.php\"  method=\"post\" name=getpo>\n");
echo("<form action=\"getPO.php\"  method=\"post\" name=\"getpo\" id=\"getpo\">\n");
if (isset($type))
{
	echo("<input type=\"hidden\" name=\"type\" value=\"$type\" >\n");
}
echo("<input type=\"hidden\" name=\"complete\" >\n");

echo("<input type=\"text\" name=\"message\" readonly size=\"30\" class=\"message\"><br>\n");
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
		echo("Load No.:");
		break;
	case "LP":
		echo("Load No.:");
		break;
}
{
	echo("<input type=\"text\" name=\"order\" maxlength=\"10\" size=\"10\" value=\"$order\" onchange=\"document.forms.getpo.submit();\" class=\"default\">\n");
	//echo("<input type=\"text\" name=\"order\" maxlength=\"10\" size=\"10\" value=\"$order\" onchange=\"document.getpo.submit();\">\n");
	//echo("<input type=\"text\" name=\"order\" maxlength=\"10\" size=\"10\" value=\"$order\" onchange=\"document.forms['getpo'].submit();\">\n");

	if (isset($line))
	{
		echo("<input type=\"hidden\" name=\"line\" value=\"" . $line . "\">");
	}
	if (isset($carrier))
	{
		echo("<input type=\"hidden\" name=\"carrier\" value=\"" . $carrier . "\">");
	}
	if (isset($vehicle))
	{
		echo("<input type=\"hidden\" name=\"vehicle\" value=\"" . $vehicle . "\">");
	}
	if (isset($container))
	{
		echo("<input type=\"hidden\" name=\"container\" value=\"" . $container . "\">");
	}
	if (isset($pallet_type))
	{
		echo("<input type=\"hidden\" name=\"pallet_type\" value=\"" . $pallet_type . "\">");
	}
	if (isset($pallet_qty))
	{
		echo("<input type=\"hidden\" name=\"pallet_qty\" value=\"" . $pallet_qty . "\">");
	}
	if (isset($received_qty))
	{
		echo("<input type=\"hidden\" name=\"received_qty\" value=\"" . $received_qty . "\">");
	}
	if (isset($consignment))
	{
		echo("<input type=\"hidden\" name=\"consignment\" value=\"" . $consignment . "\">");
	}
	if (isset($grn))
	{
		echo("<input type=\"hidden\" name=\"grn\" value=\"" . $grn . "\">");
	}
	if (isset($other1))
	{
		echo("<input type=\"hidden\" name=\"other1\" value=\"" . $other1 . "\">");
	}

	echo("<input type=\"submit\" name=\"submit\" value=\"Get Order\">\n");
}

echo("</form>\n");

$Query = "SELECT first $rxml_limit  po.purchase_order, po.po_date, po.company_id, po.person_id, pp.first_name from purchase_order_line pl  join purchase_order po on pl.purchase_order = po.purchase_order left outer join person pp on po.person_id = pp.person_id where ";

$Query2 = "SELECT  po2.purchase_order as po, cast(cast(po2.po_date as date) as char(12))  as due , po2.company_id as company, coalesce(po2.person_id,'') || coalesce( pp.first_name,'') as whom from purchase_order_line pl  join purchase_order po2 on pl.purchase_order = po2.purchase_order left outer join person pp on po2.person_id = pp.person_id where ";
if ($order <> "")
{
	if (strpos($order, "%") === false)
	{
		$Query .= "pl.purchase_order like '%" . strtoupper( $order ) . "%' and ";
		$Query2 .= "pl.purchase_order like '%" . strtoupper( $order ) . "%' and ";
	} else {
		$Query .= "pl.purchase_order like '" . strtoupper( $order ) . "' and ";
		$Query2 .= "pl.purchase_order like '" . strtoupper( $order ) . "' and ";
	}
}
$Query .= " pl.po_line_status in ('OP', 'CF') and po.order_type = '$type' ";
$Query .= " and po.po_status in ('OP', 'CF')  ";
$Query .= "group by po.purchase_order, po.po_date, po.company_id, po.person_id, pp.first_name ";

$Query2 .= " pl.po_line_status in ('OP', 'CF') and po2.order_type = '$type' ";
$Query2 .= " and po2.po_status in ('OP', 'CF')  ";
$Query2 .= "group by po2.purchase_order, po2.po_date, po2.company_id, po2.person_id, pp.first_name ";

//echo($Query);
// Create a table.
/*
echo("<BR>\n");
echo ("<table BORDER=\"1\" >\n");
*/

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to query Order Lines!<BR>\n");
	exit();
}
/*
// echo headers
echo ("<TR>\n");
{
	echo("<TH>#</TH>\n");
	echo("<TH>Due Date</TH>\n");
	echo("<TH>Company</TH>\n");
	echo("<TH>Whom</TH>\n");
}
echo ("</TR>\n");

// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) 
{
 	echo ("<TR>\n");
	//for ($i=0; $i<$Result->numCols(); $i++)
	//for ($i=0; $i<ibase_num_fields($Result); $i++)
	{
		{

			echo("<TD>");
			if (isset($grn))
			{
				echo("<form action=\"getPOLine.php\" method=\"post\"> \n");
			}
			else
			{
				echo("<form action=\"transND.php\" method=\"post\"> \n");
			}
			echo("<input type=\"hidden\" name=\"selorder\" value=\"$Row[0]\">");
			if (isset($type))
			{
				echo("<input type=\"hidden\" name=\"type\" value=\"" . $type . "\">");
			}
			if (isset($order))
			{
				echo("<input type=\"hidden\" name=\"order\" value=\"" . $order . "\">");
			}
			if (isset($line))
			{
				echo("<input type=\"hidden\" name=\"line\" value=\"" . $line . "\">");
			}
			if (isset($carrier))
			{
				echo("<input type=\"hidden\" name=\"carrier\" value=\"" . $carrier . "\">");
			}
			if (isset($vehicle))
			{
				echo("<input type=\"hidden\" name=\"vehicle\" value=\"" . $vehicle . "\">");
			}
			if (isset($container))
			{
				echo("<input type=\"hidden\" name=\"container\" value=\"" . $container . "\">");
			}
			if (isset($pallet_type))
			{
				echo("<input type=\"hidden\" name=\"pallet_type\" value=\"" . $pallet_type . "\">");
			}
			if (isset($pallet_qty))
			{
				echo("<input type=\"hidden\" name=\"pallet_qty\" value=\"" . $pallet_qty . "\">");
			}
			if (isset($received_qty))
			{
				echo("<input type=\"hidden\" name=\"received_qty\" value=\"" . $received_qty . "\">");
			}
			if (isset($consignment))
			{
				echo("<input type=\"hidden\" name=\"consignment\" value=\"" . $consignment . "\">");
			}

			echo("<input type=\"submit\" name=\"selmit\" value=\"$Row[0]\">");
			echo("</form>\n");
			echo("</TD>");
		}
		{
			{
				// the date
				list($wk_due_date,$wk_due_time) = explode(" ", $Row[1] . "  1");
 				//echo ("<TD>$Row[1]</TD>\n");
 				echo ("<TD>$wk_due_date</TD>\n");
			}
			{
				// the company
 				echo ("<TD>$Row[2]</TD>\n");
			}
			{
 				echo ("<TD>$Row[3]$Row[4]</TD>\n");
			}
		}
	}
 	echo ("</TR>\n");
}
ibase_free_result($Result);

echo("</table>");
*/
// ===================================================================================
//echo $Query2;

dopager($Query2);

// ==================================================================================

// echo("</form>");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<form action=\"./receive_menu.php\" method=\"post\" name=goback>\n");
	echo("<input type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</form>\n");
}
else
*/
{
	// html 4.0 browser
	//echo("<BUTTON name=\"back\" type=\"button\" id=\"col2\" onfocus=\"location.href='./receive_menu.php';\">\n");
	// Create a table.
	echo ("<table BORDER=\"0\" ALIGN=\"LEFT\">");
	echo ("<TR>");
	echo ("<TD>");
	if (isset($grn))
	{
		echo("<form action=\"transNX.php\" method=\"post\" name=getssnback Onsubmit=\"return processBack();\">\n");
		//echo("<form action=\"transNX.php\" method=\"post\" name=getssnback>\n");
		echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	}
	else
	{
		echo("<form action=\"getdelivery.php\" method=\"post\" name=getssnback Onsubmit=\"return processBack();\">\n");
		if (isset($type))
		{
			echo("<input type=\"hidden\" name=\"type\" value=\"" . $type . "\">");
		}
		if (isset($order))
		{
			echo("<input type=\"hidden\" name=\"order\" value=\"" . $order . "\">");
		}
		if (isset($line))
		{
			echo("<input type=\"hidden\" name=\"line\" value=\"" . $line . "\">");
		}
		if (isset($carrier))
		{
			echo("<input type=\"hidden\" name=\"carrier\" value=\"" . $carrier . "\">");
		}
		if (isset($vehicle))
		{
			echo("<input type=\"hidden\" name=\"vehicle\" value=\"" . $vehicle . "\">");
		}
		if (isset($container))
		{
			echo("<input type=\"hidden\" name=\"container\" value=\"" . $container . "\">");
		}
		if (isset($pallet_type))
		{
			echo("<input type=\"hidden\" name=\"pallet_type\" value=\"" . $pallet_type . "\">");
		}
		if (isset($pallet_qty))
		{
			echo("<input type=\"hidden\" name=\"pallet_qty\" value=\"" . $pallet_qty . "\">");
		}
		if (isset($received_qty))
		{
			echo("<input type=\"hidden\" name=\"received_qty\" value=\"" . $received_qty . "\">");
		}
		if (isset($consignment))
		{
			echo("<input type=\"hidden\" name=\"consignment\" value=\"" . $consignment . "\">");
		}
		if (isset($other1))
		{
			echo("<input type=\"hidden\" name=\"other1\" value=\"" . $other1 . "\">");
		}

	}
	echo("<input type=\"IMAGE\" ");  
	//echo('SRC="/icons/whm/Back_50x100.gif" alt="Back" onClick="return processBack();">');
	echo('SRC="/icons/whm/Back_50x100.gif" alt="Back" >');
	echo("</form>");
	echo ("</TD>");
	echo ("</TR>");
	echo ("</table>");
/*
	echo("<BUTTON name=\"back\" type=\"button\"  onClick=\"return processBack();\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
echo("</div>\n");
/*
echo("<form action=\"transNX.php\" method=\"post\" name=getssnback>");
echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\" >");
echo("</form>");
*/
/* getperson to send forward the pagers chosen order */
	if (isset($grn))
	{
		echo("<form action=\"getPOLine.php\" method=\"post\" name=getperson> \n");
		//echo("<input type=\"hidden\" name=\"grn\" value=\"" . $grn . "\">");
	}
	else
	{
		echo("<form action=\"transND.php\" method=\"post\" name=getperson> \n");
	}
	echo("<INPUT type=\"hidden\" name=\"selorder\"> ");  
	if (isset($type))
	{
		echo("<input type=\"hidden\" name=\"type\" value=\"" . $type . "\">");
	}
	if (isset($order))
	{
		echo("<input type=\"hidden\" name=\"order\" value=\"" . $order . "\">");
	}
	if (isset($line))
	{
		echo("<input type=\"hidden\" name=\"line\" value=\"" . $line . "\">");
	}
	if (isset($carrier))
	{
		echo("<input type=\"hidden\" name=\"carrier\" value=\"" . $carrier . "\">");
	}
	if (isset($vehicle))
	{
		echo("<input type=\"hidden\" name=\"vehicle\" value=\"" . $vehicle . "\">");
	}
	if (isset($container))
	{
		echo("<input type=\"hidden\" name=\"container\" value=\"" . $container . "\">");
	}
	if (isset($pallet_type))
	{
		echo("<input type=\"hidden\" name=\"pallet_type\" value=\"" . $pallet_type . "\">");
	}
	if (isset($pallet_qty))
	{
		echo("<input type=\"hidden\" name=\"pallet_qty\" value=\"" . $pallet_qty . "\">");
	}
	if (isset($received_qty))
	{
		echo("<input type=\"hidden\" name=\"received_qty\" value=\"" . $received_qty . "\">");
	}
	if (isset($consignment))
	{
		echo("<input type=\"hidden\" name=\"consignment\" value=\"" . $consignment . "\">");
	}
	if (isset($other1))
	{
		echo("<input type=\"hidden\" name=\"other1\" value=\"" . $other1 . "\">");
	}

	echo("</form>");

	ibase_free_result($Result);
	//commit
	ibase_commit($dbTran);
	
?>
<script type="text/javascript">
<?php
{
	if ($order == "")
	{
		echo("document.getpo.message.value=\"Enter Part of Purchase Order to find\";\n");
		echo("document.getpo.order.focus();\n");
	}
	else
	{
		echo("document.getpo.message.value=\"Select Purchase Order\";\n");
	}
}
?>
</script>
</html>
