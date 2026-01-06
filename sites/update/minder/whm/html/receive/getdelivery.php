<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
include "../login.inc";
?>
<html>
 <head>
  <title>Receive Delivery</title>
<?php
include "viewport.php";
?>
<link rel=stylesheet type="text/css" href="delivery.css">
<script type="text/javascript">
function processEdit(wk_from) {
  /* document.getcons.message.value="in process edit"; */
  if ( document.getcons.consignment.value=="")
  {
  	document.getcons.message.value="Must Enter the Consignment";
	document.getcons.consignment.focus();
  	return false;
  }
  if ( document.getcons.carrier.value=="")
  {
  	document.getcons.message.value="Must Enter the Carrier";
	document.getcons.carrier.focus();
  	return false;
  }
  else
  {
    if ( document.getcons.message.value == "Must Enter the Carrier")
    {
  	document.getcons.message.value="Enter the Pallet Type";
	document.getcons.pallet_type.focus();
  	return false;
    }
  }
  if ( document.getcons.pallet_type.value!="NONE")
  {
    if ( document.getcons.pallet_qty.value=="")
    {
  	document.getcons.message.value="Must Enter the Pallet Qty";
	document.getcons.pallet_qty.focus();
  	return false;
    }
  }
  if ( document.getcons.received_qty.value=="")
  {
  	document.getcons.message.value="Must Enter Received Qty";
	document.getcons.received_qty.focus();
  	return false;
  }
  if ( document.getcons.received_qty.value=="0")
  {
  	document.getcons.message.value="Must Enter Received Qty";
	document.getcons.received_qty.focus();
  	return false;
  }
  if ( document.getcons.container.value=="")
  {
  	document.getcons.message.value="Must Enter On Container";
	document.getcons.container.focus();
  	return false;
  }
  document.getcons.message.value="Press Accept to Continue";
  if (wk_from=="D")
  {
  	document.getcons.submit();
  }
  if (wk_from=="S")
  {
  	return true;
  }
}
/*
function errorHandler(errorMessage,url,line) 
{
	document.write("<p><b>Error in JS:</b> "+errorMessage+"<br>")
	document.write("<b>URL:</b> "+url+"<br>")
	document.write("<b>Line:</b> "+line+"</p>")
	return true
}
*/
function processCarrier() {
	var dowhat;
	dowhat = document.getcons.carrier.selectedIndex ;
	document.getcarrier.carrier.value = document.getcons.carrier.options[dowhat].value ;
	document.getcarrier.vehicle.value = document.getcons.vehicle.value ;
	document.getcarrier.other1.value = document.getcons.other1.value ;
	if (document.getcons.container.checked == true)
	{
		document.getcarrier.container.value = "Y" ;
	}
	else
	{
		document.getcarrier.container.value = "N" ;
	}
	dowhat = document.getcons.pallet_type.selectedIndex ;
	document.getcarrier.pallet_type.value = document.getcons.pallet_type.options[dowhat].value ;
	document.getcarrier.pallet_qty.value = document.getcons.pallet_qty.value ;
	document.getcarrier.received_qty.value = document.getcons.received_qty.value ;
	if (document.getcons.problem.checked == true)
	{
		document.getcarrier.problem.value = "T" ;
	}
	else
	{
		document.getcarrier.problem.value = "" ;
	}
	dowhat = document.getcons.sday.selectedIndex ;
	document.getcarrier.sday.value = document.getcons.sday.options[dowhat].value ;
	dowhat = document.getcons.smonth.selectedIndex ;
	document.getcarrier.smonth.value = document.getcons.smonth.options[dowhat].value ;
	dowhat = document.getcons.syear.selectedIndex ;
	document.getcarrier.syear.value = document.getcons.syear.options[dowhat].value ;
	document.getcarrier.consignment.value = document.getcons.consignment.value ;
	document.getcarrier.submit();
  return true;
  /* return false; */
}
/* onerror = errorHandler */
</script>
<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include "logme.php";
 echo("</head>\n");

echo("<!-- Background white, links blue (unvisited), navy (visited), red (active) -->\n");
echo("<body>\n");

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	

function getDefaultOption($Link, $code)
{
	{
		$Query = "select description from options where group_code='RECEIVE'  and code = 'DEFAULT|" . $code . "' "; 
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Options!<br>\n");
			$log = fopen('/data/tmp/getdelivery.log' , 'a');
			fwrite($log, $Query);
			fclose($log);
			//exit();
		}
		$wk_data = "";
		while ( ($Row = ibase_fetch_row($Result)) ) {
			if ($Row[0] > "")
			{
				$wk_data = $Row[0];
			}
		}
		//release memory
		ibase_free_result($Result);
	} 
	return $wk_data;
}
/* end of function */


function getVehicleOption($Link, $code)
{
	{
		$Query = "select description, description2 from options where group_code='RECEIVE'  and code = 'VEHICLE|" . $code . "' "; 
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Options!<br>\n");
			$log = fopen('/data/tmp/getdelivery.log' , 'a');
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
}
/* end of function */

$vehicle = "";
$pallet_qty = 0;
$received_qty = 1;
$consignment = "";
$order = "";
$line = "";
$grn = "";
$problem = "";
$other1 = "";
if (isset($_COOKIE['BDCSData']))
{
	$bdcs_cookie = $_COOKIE["BDCSData"] . "|||||||||||";
	list($received_qty, $type, $order, $line, $carrier, $vehicle, $container, $pallet_type, $pallet_qty, $consignment, $grn, $problem) = explode("|", $bdcs_cookie);
	if ($received_qty > "")
	{
		if ($received_qty == "deleted")
		{
			$received_qty = 1;
			$pallet_qty = 0;
		}
	}
}
//if (!($Link = ibase_connect($DBName2, $User, $Password)))
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
	$other1 = getBDCScookie($Link, $tran_device, "other1" );
	$wh_id = getBDCScookie($Link, $tran_device, "WH_ID" );
	$wk_original_type = $type;
}
if (isset($_POST['type']))
{
	$type = $_POST['type'];
}
if (isset($_GET['type']))
{
	$type = $_GET['type'];
}
if ($type <> $wk_original_type)
{
	setBDCScookie($Link, $tran_device, "type", $type);
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
if (isset($_POST['problem']))
{
	$problem = $_POST['problem'];
}
if (isset($_GET['problem']))
{
	$problem = $_GET['problem'];
}
if (isset($_POST['other1']))
{
	$other1 = $_POST['other1'];
}
if (isset($_GET['other1']))
{
	$other1 = $_GET['other1'];
}
if (isset($_POST['message']))
{
	$message = $_POST['message'];
}
if (isset($_GET['message']))
{
	$message = $_GET['message'];
}

$wk_current_wh_id  = getBDCScookie($Link, $tran_device, "CURRENT_WH_ID" );

$useVehicle = True;
$useVehicleDesc  = getVehicleOption($Link,  "vehicle" );
if (strtoupper($useVehicleDesc[0]) == "T") 
	$useVehicle = True;
if (strtoupper($useVehicleDesc[0]) == "F") 
	$useVehicle = False;
$useVehicleDesc  = getVehicleOption($Link,  "other1" );
//var_dump($useVehicleDesc);
if (strtoupper($useVehicleDesc[0]) == "T") 
	$useVehicle = False;
if (strtoupper($useVehicleDesc[0]) == "F") 
	$useVehicle = False;


/* if there are system defaults for 
	$received_qty = getBDCScookie($Link, $tran_device, "received_qty" );
	$carrier = getBDCScookie($Link, $tran_device, "carrier" );
	$vehicle = getBDCScookie($Link, $tran_device, "vehicle" );
	$container = getBDCScookie($Link, $tran_device, "container" );
	$pallet_type = getBDCScookie($Link, $tran_device, "pallet_type" );
	$pallet_qty = getBDCScookie($Link, $tran_device, "pallet_qty" );
	$other1 = getBDCScookie($Link, $tran_device, "other1" );
and the current value is empty
then default to these
*/
if ($received_qty == "") 
{
	// check for a default
	$received_qty = getDefaultOption($Link,  "received_qty" );
}
if ($carrier == "") 
{
	// check for a default
	$carrier = getDefaultOption($Link, "carrier" );
}
if ($vehicle == "") 
{
	// check for a default
	$vehicle = getDefaultOption($Link, "vehicle" );
}
if ($container == "") 
{
	// check for a default
	$container = getDefaultOption($Link, "container" );
}
if ($pallet_type == "") 
{
	// check for a default
	$pallet_type = getDefaultOption($Link, "pallet_type" );
}
if ($pallet_qty == "") 
{
	// check for a default
	$pallet_qty = getDefaultOption($Link, "pallet_qty" );
}
if ($other1 == "") 
{
	// check for a default
	$other1 = getDefaultOption($Link, "other1" );
}
//echo("<FONT size=\"2\">\n");
echo("<div id=\"carriers\">");
	//echo("<form action=\"carrier.php\"  method=\"get\" onsubmit=\"return processCarrier();\" name=getcarrier>\n");
	echo("<form action=\"carrier.php\"  method=\"post\" onsubmit=\"return processCarrier();\" name=getcarrier>\n");
	//echo("<form action=\"carrier.php\"  method=\"post\"  name=getcarrier>\n");
	echo("<input type=\"hidden\" name=\"type\" value=\"$type\" >");
	echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	echo("<input type=\"hidden\" name=\"order\" value=\"$order\">\n");
	echo("<input type=\"hidden\" name=\"line\" value=\"$line\">\n");
	echo("<input type=\"hidden\" name=\"carrier\" >\n");
	echo("<input type=\"hidden\" name=\"vehicle\" >\n");
	echo("<input type=\"hidden\" name=\"container\" >\n");
	echo("<input type=\"hidden\" name=\"pallet_type\" >\n");
	echo("<input type=\"hidden\" name=\"pallet_qty\" >\n");
	echo("<input type=\"hidden\" name=\"received_qty\" >\n");
	echo("<input type=\"hidden\" name=\"problem\" >\n");
	echo("<input type=\"hidden\" name=\"consignment\" >\n");
	echo("<input type=\"hidden\" name=\"sday\" >\n");
	echo("<input type=\"hidden\" name=\"smonth\" >\n");
	echo("<input type=\"hidden\" name=\"syear\" >\n");
	echo("<input type=\"hidden\" name=\"from\" value=\"getdelivery.php\" >\n");
	echo("<input type=\"hidden\" name=\"other1\" >\n");
	echo("<input type=\"hidden\" name=\"WH_ID\" >\n");
	echo("</form>");
echo("</div>");
echo("<div id=\"col3\">");
//echo("<FORM action=\"transND.php\" method=\"post\" name=getcons ONSUBMIT=\"return processEdit('S');\">");
//echo("<FORM action=\"getdelivery.php\" method=\"get\" name=getcons ONSUBMIT=\"return processEdit();\">");
switch ($type )
{
	case "PO":
	case "WO":
	case "TR":
	case "RA":
		echo("<form action=\"getPO.php\" method=\"post\" name=getcons ONSUBMIT=\"return processEdit('S');\">");
		break;
	case "LD":
	case "LP":
		echo("<form action=\"transND.php\" method=\"post\" name=getcons ONSUBMIT=\"return processEdit('S');\">");
		break;
}

if (isset($type))
{
	echo("<input type=\"hidden\" name=\"type\" value=\"$type\" >");
}

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
//echo("Type:".$type);
if ($type == "LD" or $type == "LP")
{
	//echo("<input type=\"text\" name=\"order\" maxlength=\"10\" size=\"10\" readonly value=\"$order\" class=\"noread\">\n");
	echo("<input type=\"text\" name=\"order\" maxlength=\"20\" size=\"17\" readonly value=\"$order\" class=\"noread\">\n");
	echo("<input type=\"text\" name=\"line\" maxlength=\"4\" size=\"4\" readonly value=\"$line\" class=\"noread\"><br>\n");
}
else
{
	//echo("<input type=\"text\" name=\"order\" maxlength=\"10\" size=\"10\" value=\"$order\" class=\"default\">\n");
	echo("<input type=\"text\" name=\"order\" maxlength=\"20\" size=\"17\" value=\"$order\" class=\"default\">\n");
	echo("<input type=\"text\" name=\"line\" maxlength=\"4\" size=\"4\" value=\"$line\" class=\"default\"><br>\n");
}
//echo("Carrier:<input type=\"text\" name=\"carrier\" size=\"10\" maxlength=\"10\"><br>");
echo("Carrier:<select name=\"carrier\" size=\"1\" class=\"sel3\">\n");
$Query = "select carrier_id from carrier order by carrier_id desc"; 
$Query = "select carrier.carrier_id,person.first_name from carrier join person on carrier.carrier_id = person.person_id order by person.first_name desc"; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Carrier!<br>\n");
	exit();
}
$wk_carrier_line = 0;
while ( ($Row = ibase_fetch_row($Result)) ) {
 	$wk_carrier_line ++;
	//if ($wk_carrier_line == 1)
	if ((($carrier == "") and ($wk_carrier_line == 1)) or (($carrier <> "") and ($carrier == $Row[0])))
	{
		//echo( "<option value=\"$Row[0]\" selected>$Row[0]\n");
		echo( "<option value=\"$Row[0]\" selected>$Row[1]\n");
	} else {
		//echo( "<option value=\"$Row[0]\">$Row[0]\n");
		echo( "<option value=\"$Row[0]\">$Row[1]\n");
	}
}
//release memory
ibase_free_result($Result);
//echo("</select><br>\n");
echo("</select>\n");
//if  ($type == "LP" )
{
		echo("<input type=\"button\" name=\"addcarrier\" value=\"A\" ");  
		echo(' alt="Add Carrier" onclick="processCarrier();" >');
}
echo("<br>\n");
if ($useVehicle)
{
	echo("Veh.Reg.:<input type=\"text\" name=\"vehicle\" size=\"8\" maxlength=\"10\" value=\"$vehicle\" class=\"default\"><br>\n");
	echo("<input type=\"hidden\" name=\"other1\"  value=\"$other1\">\n");
} else {
	echo("<input type=\"hidden\" name=\"vehicle\"  value=\"$vehicle\">\n");
	echo($useVehicleDesc[1] . ":<input type=\"text\" name=\"other1\" size=\"18\" maxlength=\"20\" value=\"$other1\" class=\"default\"><br>\n");
}
echo("<div>Container:<label for=\"radioyes\"><input type=\"radio\" name=\"container\" value=\"Y\" id=\"containeryes\"");
echo(" onchange=\"processEdit('D');\">");
echo("yes</label> \n");
echo("<label for=\"radiono\"><input type=\"radio\" name=\"container\" value=\"N\" id=\"containerno\"
>no</label></div> \n");
echo("Pallets:<select name=\"pallet_type\" size=\"1\" class=\"sel1\"");
echo(" onchange=\"processEdit('D');\">");
//echo( "<option value=\"NONE\">NONE\n");
//echo( "<option value=\"UNKNOWN\">UNKNOWN\n");
//echo( "<option value=\"CHEP\">CHEP\n");
//echo( "<option value=\"OWN\">OWN\n");
$Query = "select code, description from options where group_code='PACK_OWNER' order by description"; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Options!<br>\n");
	exit();
}
$wk_have_pallet_owners = "F";
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] == 'N')
	{
		echo( "<option value=\"$Row[0]\" selected>$Row[1]\n");
		$wk_have_pallet_owners = "T";
	}
	else
		echo( "<option value=\"$Row[0]\">$Row[1]\n");
}
//release memory
ibase_free_result($Result);
if ($wk_have_pallet_owners == "F")
{
	echo( "<option value=\"N\">NONE\n");
	echo( "<option value=\"U\">UNKNOWN\n");
	echo( "<option value=\"C\">CHEP\n");
	echo( "<option value=\"O\">OWN\n");
}
echo("</select>\n");
echo("Qty:<input type=\"text\" name=\"pallet_qty\" size=\"4\" maxlength=\"4\" value=\"$pallet_qty\" class=\"default\" ");
echo(" onchange=\"processEdit('D');\"><br>");
echo("Received:<input type=\"text\" name=\"received_qty\" size=\"4\" maxlength=\"4\" value=\"" . $received_qty . "\" class=\"default\"");
echo(" onchange=\"processEdit('D');\">");
echo("Problem:<input type=\"checkbox\" name=\"problem\" value=\"T\"");
echo(" onchange=\"processEdit('D');\"");
if ($problem > "")
{
	echo(" checked ");
}
//echo(" ><br>");
echo(" >");
echo("WH:<select name=\"WH_ID\" class=\"sel4\">\n");
$Query = "select ln.wh_id  from warehouse ln join access_user au on au.wh_id = ln.wh_id  and au.user_id = '" . $tran_user . "' order by ln.wh_id  "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Warehouses!<br>\n");
	exit();
}
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($wk_current_wh_id == $Row[0])
	{
		echo( "<option value=\"$Row[0]\" selected>$Row[0]\n");
	} 
	else
	{
		echo( "<option value=\"$Row[0]\">$Row[0]\n");
	}
}  
//release memory
ibase_free_result($Result);
echo("</select>\n");
echo(" <br>");

echo("Shipped Date:");
include "date.php";
echo("Consignment/AWB/Del.Docket No.<input type=\"text\" name=\"consignment\" maxlength=\"20\" size=\"20\" value=\"$consignment\" class=\"default\"");
echo(" onchange=\"processEdit('D');\"><br>");

{
	// html 4.0 browser
	echo("<table border=\"0\" align=\"LEFT\">");
	whm2buttons('Accept', './receive_menu.php',"N","Back_50x100.gif","Back","accept.gif", "N");
	//whm2buttons('Accept', './receive_menu.php',"Y","Back_50x100.gif","Back","accept.gif", "Y");
	//whm2buttons('Accept', './receive_menu.php',"Y","Back_50x100.gif","Back","accept.gif"  );
	echo ("<td>");
	$alt = "Next GRN";
	echo("<form action=\"nextgrn.php\"  method=\"post\"  name=cleargrn>\n");
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
	//echo('SRC="/icons/whm/comment.gif" alt="' . $alt . '">');
	//echo("<form action=\"carrier.php\"  method=\"post\" onsubmit=\"return processCarrier();\" name=getcarrier>\n");
	echo("<input type=\"hidden\" name=\"from\" value=\"getdelivery.php\" >\n");
/*
	echo("<input type=\"hidden\" name=\"type\" value=\"$type\" >");
	echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	echo("<input type=\"hidden\" name=\"order\" value=\"$order\">\n");
	echo("<input type=\"hidden\" name=\"line\" value=\"$line\">\n");
	echo("<input type=\"hidden\" name=\"carrier\" >\n");
	echo("<input type=\"hidden\" name=\"vehicle\" >\n");
	echo("<input type=\"hidden\" name=\"container\" >\n");
	echo("<input type=\"hidden\" name=\"pallet_type\" >\n");
	echo("<input type=\"hidden\" name=\"pallet_qty\" >\n");
	echo("<input type=\"hidden\" name=\"received_qty\" >\n");
	echo("<input type=\"hidden\" name=\"problem\" >\n");
	echo("<input type=\"hidden\" name=\"consignment\" >\n");
	echo("<input type=\"hidden\" name=\"sday\" >\n");
	echo("<input type=\"hidden\" name=\"smonth\" >\n");
	echo("<input type=\"hidden\" name=\"syear\" >\n");
	echo("<input type=\"hidden\" name=\"from\" value=\"getdelivery.php\" >\n");
*/
	echo("</form>");
	echo ("</td>");
	echo ("</tr>");
	echo ("</table>");
}
echo("</div>\n");
//<script type="text/Javascript">
?>
<script type="text/javascript">
<?php
	if (isset($message)) {
		echo('document.getcons.message.value="' . $message . '";');
	} else {
		echo('document.getcons.message.value="Scan Consignment No";');
	}
?>
	document.getcons.consignment.focus();
</script>
</body>
</html>
