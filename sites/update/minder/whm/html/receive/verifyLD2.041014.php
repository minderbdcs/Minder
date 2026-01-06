<?php
include "../login.inc";
?>
<html>
 <head>
  <title>Receive Verify Load</title>
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
  		document.getssnqtys.message.value="Select the Returned From";
		document.getssnqtys.retfrom.focus();
		return false;
	}
*/
/* treat as if yes is wanted in cancel */
		document.forms.getssnback.submit();
		return true;
}
function processEdit() {
	var dowhat;
  /* document.getssnqtys.message.value="in process edit"; */
  if ( document.getssnqtys.retfrom.value=="")
  {
  	document.getssnqtys.message.value="Must Select the Sent From";
	document.getssnqtys.retfrom.focus()
  	return false
  }
  if ( document.getssnqtys.owner.value=="")
  {
  	document.getssnqtys.message.value="Must Select the Owner";
	document.getssnqtys.owner.focus()
  	return false
  }
  if ( document.getssnqtys.owner.value==" ")
  {
  	document.getssnqtys.message.value="Must Select the Owner";
	document.getssnqtys.owner.focus()
  	return false
  }
  if ( document.getssnqtys.received_ssn_qty.value=="")
  {
  	document.getssnqtys.message.value="Must Enter Received Qty";
	document.getssnqtys.received_ssn_qty.focus()
  	return false
  }
  if ( document.getssnqtys.received_ssn_qty.value=="0")
  {
  	document.getssnqtys.message.value="Must Enter Received Qty";
	document.getssnqtys.received_ssn_qty.focus()
  	return false
  }
  if ( document.getssnqtys.location.value=="")
  {
  	document.getssnqtys.message.value="Must Select the Location";
	document.getssnqtys.location.focus()
  	return false
  }
  if ( document.getssnqtys.printer.value=="")
  {
  	document.getssnqtys.message.value="Must Select the printer";
	document.getssnqtys.printer.focus()
  	return false
  }
	dowhat=confirm("Is Delivery Complete?");
	if(dowhat)
	{
		/* the yes */
		document.getssnqtys.complete.value = "Y";
		document.goforw.complete.value = document.getssnqtys.complete.value ;
		document.goforw.retfrom.value = document.getssnqtys.retfrom.value ;
		document.goforw.owner.value = document.getssnqtys.owner.value ;
		document.goforw.received_ssn_qty.value = document.getssnqtys.received_ssn_qty.value ;
		document.goforw.location.value = document.getssnqtys.location.value ;
		document.goforw.printer.value = document.getssnqtys.printer.value ;
		document.goforw.problem.value = document.getssnqtys.problem.value ;
		document.goforw.submit();
	}
	else
	{
		dowhat=confirm("Is Delivery InComplete?");
		if(dowhat)
		{
			/* the no */
			document.getssnqtys.complete.value = "N";
			document.goforw.complete.value = document.getssnqtys.complete.value ;
			document.goforw.retfrom.value = document.getssnqtys.retfrom.value ;
			document.goforw.owner.value = document.getssnqtys.owner.value ;
			document.goforw.received_ssn_qty.value = document.getssnqtys.received_ssn_qty.value ;
			document.goforw.location.value = document.getssnqtys.location.value ;
			document.goforw.printer.value = document.getssnqtys.printer.value ;
			document.goforw.problem.value = document.getssnqtys.problem.value ;
			document.goforw.submit();
		}
		else
		{
			dowhat=confirm("Cancel Delivery?");
			if(dowhat)
			{
				document.forms.getssnback.submit();
			}
			else
			{
  				document.getssnqtys.message.value="Select the Returned From";
				document.getssnqtys.retfrom.focus();
				return false;
			}
		}
	}
  return false;
}
function processComment() {
	var dowhat;
	document.getcomment.retfrom.value = document.getssnqtys.retfrom.value ;
	document.getcomment.owner.value = document.getssnqtys.owner.value ;
	document.getcomment.received_ssn_qty.value = document.getssnqtys.received_ssn_qty.value ;
	document.getcomment.location.value = document.getssnqtys.location.value ;
	document.getcomment.printer.value = document.getssnqtys.printer.value ;
	document.getcomment.problem.value = document.getssnqtys.problem.value ;
	document.getcomment.submit();
  return true;
}
onerror = errorHandler
</script>
<?php
require_once 'DB.php';
require 'db_access.php';
require 'logme.php';

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
}
if (isset($_POST['type']))
{
	$type = $_POST['type'];
}
if (isset($_GET['type']))
{
	$type = $_GET['type'];
}
if (isset($_POST['grnorder']))
{
	$grnorder = $_POST['grnorder'];
}
if (isset($_GET['grnorder']))
{
	$grnorder = $_GET['grnorder'];
}
if (isset($grnorder))
{
	//if (strlen($grnorder) == 8)
	{
		if (preg_match("/[A-D][0-9]*[A-D]/i", $grnorder))
		{
			//$grnorder1 = substr($grnorder,1,6);
			$grnorder1 = substr($grnorder,1,strlen($grnorder - 2));
			$grnorder = $grnorder1; 
		}
	}
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
// if printer not specified then try one saved in the session table
if (!isset($printer))
{
	$printer = getBDCScookie($Link, $tran_device, "printer" );
	if ($printer == "")
	{
		unset($printer);
	}
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
if (isset($_POST['problem']))
{
	$problem = $_POST['problem'];
}
if (isset($_GET['problem']))
{
	$problem = $_GET['problem'];
}


if (isset($grnorder))
{
	$Query = "select grn, order_no, company_id, supplier_id from grn_order where grn_label_no = '$grnorder' "; 
	//print($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read GRN_ORDER!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$grn = $Row[0];
		$order = $Row[1];
		$line = "1";
		$default_comp = $Row[2];
		$default_supplier = $Row[3];
		$received_qty = getBDCScookie($Link, $tran_device, "received_qty" );

		setBDCScookie($Link, $tran_device, "type", $type);
		setBDCScookie($Link, $tran_device, "order", $order);
		setBDCScookie($Link, $tran_device, "line", $line);
		setBDCScookie($Link, $tran_device, "owner", $default_comp);
		$owner = $default_comp;
		setBDCScookie($Link, $tran_device, "grn", $grn);
		setBDCScookie($Link, $tran_device, "retfrom", $default_supplier );
		$retfrom = $default_supplier;
		$problem = "";
		setBDCScookie($Link, $tran_device, "problem", $problem);
/*
		setBDCScookie($Link, $tran_device, "carrier", $carrier);
		setBDCScookie($Link, $tran_device, "vehicle", $vehicle);
		setBDCScookie($Link, $tran_device, "container", $container);
		setBDCScookie($Link, $tran_device, "pallet_type", $pallet_type);
		setBDCScookie($Link, $tran_device, "pallet_qty", $pallet_qty);
		setBDCScookie($Link, $tran_device, "consignment", $consignment);
*/

	}
	//release memory
	ibase_free_result($Result);
}
if (!isset($default_comp))
{
	// no grnorder so error and go back
	header("Location: getgrnorder.php?type=" . $type . "&message=Invalid+GrnOrder" );
}
echo("<FONT size=\"2\">\n");
echo("<div id=\"col3\">");
echo("<FORM method=\"post\" name=getssnqtys ONSUBMIT=\"return processEdit();\">");
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
		//echo("Load No.:");
		echo("Non-Product:");
		break;
	case "LP":
		echo("Product Load No.:");
		break;
}
echo("<INPUT type=\"text\" name=\"order\" maxlength=\"10\" size=\"10\" readonly value=\"$order\" class=\"noread\">\n");
echo("<INPUT type=\"text\" name=\"line\" maxlength=\"4\" size=\"4\" readonly value=\"$line\" class=\"noread\" ><BR>\n");
/*
echo("Load Sent From:<BR><SELECT name=\"retfrom\" class=\"sel3\">\n");
$Query = "select person_id, first_name, last_name from person where person_type in ('LE', 'RP') or person_type starting 'C' order by first_name, last_name "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Returner!<BR>\n");
	exit();
}
while ( ($Row = ibase_fetch_row($Result)) ) {
	echo( "<OPTION value=\"$Row[0]\">$Row[1] $Row[2]\n");
}
//release memory
ibase_free_result($Result);
echo("</SELECT><BR>\n");
*/
echo("<input type=\"hidden\" name=\"retfrom\" value=\"$retfrom\">\n");
//if ($owner == "")
if (trim($owner) == "")
{
	echo("Owned by:<BR><SELECT name=\"owner\" class=\"sel3\">\n");
	$Query = "select company_id from control "; 
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
	$Query = "select company_id, name from company order by name "; 
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Company!<BR>\n");
		exit();
	}
	$default_comp=' ';
	echo( "<OPTION value=\"\" selected>\n");
	while ( ($Row = ibase_fetch_row($Result)) ) {
		if ($default_comp == $Row[0])
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
	echo("</SELECT><BR>\n");
} else {
	echo("<input type=\"hidden\" name=\"owner\" value=\"$owner\">\n");
}
echo("Received :<INPUT type=\"text\" name=\"received_ssn_qty\" size=\"4\" maxlength=\"4\" value=\"$received_ssn_qty\" class=\"default\"> Items<BR>\n");
echo("Location:<SELECT name=\"location\" class=\"sel5\">\n");
//$Query = "select wh_id, locn_id, locn_name from location where store_area = 'RC' order by locn_name "; 
$Query = "select ln.wh_id, ln.locn_id, ln.locn_name from location ln join session on session.device_id = '" . $tran_device . "' and session.code='CURRENT_WH_ID' where ln.wh_id = session.description and ln.store_area = 'RC' order by ln.locn_name " ; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Locations!<BR>\n");
	exit();
}
while ( ($Row = ibase_fetch_row($Result)) ) {
	echo( "<OPTION value=\"$Row[0]$Row[1]\">$Row[2]\n");
}
//release memory
ibase_free_result($Result);
echo("</SELECT><BR>\n");
echo("Problem:<INPUT type=\"checkbox\" name=\"problem\" value=\"T\"");
echo(" onchange=\"processEdit();\"");
if ($problem > "")
{
	echo(" checked ");
}
echo(" >");
echo("<DIV ID=\"col5\">\n");
echo("Print on:<SELECT name=\"printer\" class=\"sel4\">\n");
$Query = "select device_id from sys_equip where device_type = 'PR' order by device_id "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Devices!<BR>\n");
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
echo("</SELECT><BR>\n");
echo("</DIV>\n");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	echo("<INPUT type=\"submit\" name=\"accept\" value=\"Accept\">\n");
}
else
{
	echo("<BUTTON name=\"accept\" value=\"Accept\" type=\"submit\" id=\"col1\" >\n");
	echo("Accept<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
}
*/
//echo("</FORM>");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<FORM action=\"./receive_menu.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
*/
{
	// html 4.0 browser
	$alt = "Accept";
	// Create a table.
	echo ("<TABLE BORDER=\"0\" ALIGN=\"LEFT\" id=\"col1\">");
	echo ("<TR>");
	echo ("<TD>");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
	echo("</FORM>");
	echo ("</TD>");
	echo ("<TD>");
	echo("<FORM action=\"transNX.php\"  method=\"post\" onsubmit=\"return processBack();\" name=getssnback>\n");
	echo("<INPUT type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/Back_50x100.gif" onClick="return processBack();" alt="Back">');
	echo("</FORM>");
	echo ("</TD>");
	echo ("</TR>");
	echo ("<TR>");
	echo ("<TD>");
	$alt = "Comments";
	echo("<FORM action=\"comments.php\"  method=\"post\" onsubmit=\"return processComment();\" name=getcomment>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
	echo("<INPUT type=\"hidden\" name=\"type\" value=\"$type\" >");
	echo("<INPUT type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\">\n");
	echo("<INPUT type=\"hidden\" name=\"line\" value=\"$line\">\n");
	echo("<INPUT type=\"hidden\" name=\"retfrom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"owner\" >\n");
	echo("<INPUT type=\"hidden\" name=\"received_ssn_qty\" >\n");
	echo("<INPUT type=\"hidden\" name=\"location\" >\n");
	echo("<INPUT type=\"hidden\" name=\"printer\" >\n");
	echo("<INPUT type=\"hidden\" name=\"problem\" >\n");
	echo("<INPUT type=\"hidden\" name=\"from\" value=\"verifyLD.php\" >\n");
	echo("</FORM>");
	echo ("</TD>");
	echo ("</TR>");
	echo ("</TABLE>");

/*
	//echo("<BUTTON name=\"back\" type=\"button\" id=\"col2\" onfocus=\"location.href='./receive_menu.php';\">\n");
	echo("<BUTTON name=\"back\" type=\"button\" id=\"col2\" onClick=\"return processBack();\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
echo("</div>\n");
/*
echo("<FORM action=\"transNX.php\" method=\"post\" name=getssnback >");
echo("<INPUT type=\"hidden\" name=\"grn\" value=\"$grn\" >");
echo("</FORM>");
*/
echo("<FORM action=\"transLDNV.php\" method=\"post\" name=goforw >");
echo("<INPUT type=\"hidden\" name=\"type\" value=\"$type\" >");
echo("<INPUT type=\"hidden\" name=\"grn\" value=\"$grn\" >");
echo("<INPUT type=\"hidden\" name=\"complete\" >");
echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\">\n");
echo("<INPUT type=\"hidden\" name=\"line\" value=\"$line\">\n");
echo("<INPUT type=\"hidden\" name=\"retfrom\" >\n");
echo("<INPUT type=\"hidden\" name=\"owner\" >\n");
echo("<INPUT type=\"hidden\" name=\"received_ssn_qty\" >\n");
echo("<INPUT type=\"hidden\" name=\"location\" >\n");
echo("<INPUT type=\"hidden\" name=\"printer\" >\n");
echo("<INPUT type=\"hidden\" name=\"problem\" >\n");
echo("</FORM>");
?>
<script type="text/javascript">
<?php
{
	echo("document.getssnqtys.message.value=\"Enter Received\";\n");
	echo("document.getssnqtys.received_ssn_qty.focus();\n");
}
?>
</script>
</html>

