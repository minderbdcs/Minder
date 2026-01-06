<?php
include "../login.inc";
include "viewport.php";
?>
<html>
 <head>
  <title>Receive Delivery</title>
<link rel=stylesheet type="text/css" href="delivery2.css">
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
    border: 0; padding: 7px 4px; margin: 0;
}
</style>
 </head>
<script type="text/javascript">
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
function chkColon(strString)
{
//check for valid characters
	var strValidChars = ":";
	var strChar;
	var blnResult = true;
	var i;
	if (strString.length == 0) return false;
	for (i = 0; i<strString.length && blnResult == true; i++)
	{
		strChar = strString.charAt(i);
		if (strValidChars.indexOf(strChar) > -1)
		{
			blnResult = false;
		}
	}
	return blnResult;
}
function chkOrder() {
        if (!chkColon(document.getcons.order.value) )
	{
  		document.getcons.message.value="Not allowed : in Order";
		document.getcons.order.focus();
  		return false;
	}
	else
	{
  		return true;
	}
}
function processEdit() {
  /* document.getcons.message.value="in process edit"; */
  if ( document.getcons.order.value!="")
  {
	var myorder = document.getcons.order.value;
	document.getcons.order.value = myorder.toUpperCase();
  }
  if ( document.getcomp.owner.value=="")
  {
  	document.getcons.message.value="Must Select the Owner";
	document.getcomp.owner.focus();
  	return false;
  }
  if ( document.getcomp.owner.value==" ")
  {
  	document.getcons.message.value="Must Select the Owner";
	document.getcomp.owner.focus();
  	return false;
  }
  if ( document.getcomp.owner.value=="  ")
  {
  	document.getcons.message.value="Must Select the Owner";
	document.getcomp.owner.focus();
  	return false;
  }
  if ( document.getcons.owner.value=="")
  {
  	document.getcons.message.value="Must Select the Owner";
	document.getcomp.owner.focus();
  	return false;
  }

  if ( document.getcons.order.value>"")
  {
	/*  check no : */
        if ( !chkColon(document.getcons.order.value) )
	{
  		document.getcons.message.value="Not allowed : in Order";
		document.getcons.order.focus();
  		return false;
	}
  }

  if ( document.getcons.retfrom.value=="")
  {
  	document.getcons.message.value="Must Select the Supplier";
	document.getcons.retfrom.focus();
  	return false;
  }
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
  /*  if ( document.getcons.pallet_type.value!="NONE") */
  if ( document.getcons.pallet_type.value!="N")
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
  if ( document.getcons.container.value=="")
  {
  	document.getcons.message.value="Must Enter On Container";
	document.getcons.container.focus();
  	return false;
  }
  return true;
}
</script>
<?php
require_once 'DB.php';
require 'db_access.php';

include "2buttons.php";
include "logme.php";

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	
//if (isset($_COOKIE['BDCSData']))
//{
//	list($received_qty, $type, $order, $line, $carrier, $vehicle, $container, $pallet_type, $pallet_qty, $consignment, $grn, $owner, $retfrom, $label_qty, $printer) = explode("|", $_COOKIE["BDCSData"]);
//}
$owner = "";
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
if (isset($_POST['owner']))
{
	$owner = $_POST['owner'];
}
if (isset($_GET['owner']))
{
	$owner = $_GET['owner'];
}
if (isset($_POST['label_qty']))
{
	$label_qty = $_POST['label_qty'];
}
if (isset($_GET['label_qty']))
{
	$label_qty = $_GET['label_qty'];
}
if (isset($_POST['printer']))
{
	$printer = $_POST['printer'];
}
if (isset($_GET['printer']))
{
	$printer = $_GET['printer'];
}
if (isset($_POST['retfrom']))
{
	$retfrom = $_POST['retfrom'];
}
if (isset($_GET['retfrom']))
{
	$retfrom = $_GET['retfrom'];
}
if (isset($_POST['message']))
{
	$message = $_POST['message'];
}
if (isset($_GET['message']))
{
	$message = $_GET['message'];
}

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<br>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

//echo("<font size=\"2\">\n");
echo("<div id=\"col3\">");
echo("<form action=\"getdelivery2.php\" method=\"post\" name=getcomp>");
if (isset($type))
{
	echo("<INPUT type=\"hidden\" name=\"type\" value=\"$type\" >");
}

//echo("Owned by:<SELECT name=\"owner\" class=\"sel3\" onchange=\"forms.getcomp.submit();\">\n");
echo("Owner:<SELECT name=\"owner\" class=\"sel3\" onchange=\"forms.getcomp.submit();\">\n");
//$wk_get_control_company = "Y";
$wk_get_control_company = "NotYet";
$default_comp = "";
if (isset($owner))
{
	$owner = trim($owner);
	if ($owner <> "")
	{
		$default_comp = $owner;
		$wk_get_control_company = "N";
	}
}
if ($wk_get_control_company == "Y")
{
	$Query = "select company_id from control "; 
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Control!<br>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		if ($wk_get_control_company == "Y")
		{
			$default_comp = $Row[0];
		}
		//echo("<script>\n");
		//echo('wkDefaultCompany="' . $Row[0] . '"');
		//echo("\n</script>\n");
	}
	//release memory
	ibase_free_result($Result);
}

//$default_comp = "";
if ($default_comp == "")
{
	echo( "<OPTION value=\"\" selected >\n");
} else {
	echo( "<OPTION value=\"\" >\n");
}
$Query = "select company_id, name from company order by name "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Company!<br>\n");
	exit();
}
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
echo("</SELECT>\n");
echo("</form>");
echo("<form action=\"transND2.php\" method=\"post\" name=getcons ONSUBMIT=\"return processEdit();\">");
if (isset($type))
{
	echo("<INPUT type=\"hidden\" name=\"type\" value=\"$type\" >");
}
if (isset($owner))
{
	echo("<INPUT type=\"hidden\" name=\"owner\" value=\"$owner\" >");
} else {
	echo("<INPUT type=\"hidden\" name=\"owner\" value=\"$default_comp\" >");
}
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
if ($type == "LD" or $type = "LP")
{
	//echo("<INPUT type=\"text\" name=\"order\" maxlength=\"10\" size=\"10\" value=\"$order\">\n");
	echo("<INPUT type=\"text\" name=\"order\" maxlength=\"10\" size=\"10\" class=\"default\" "); 
	if (isset ($order))
	{
		echo(" value=\"$order\">\n");
	} else {
		echo(" value=\"\" onchange=\"chkOrder()\" onblur=\"chkOrder()\">\n");
	}
/*
	echo("<SELECT name=\"order\" size=\"1\" class=\"sel3\">\n");
	echo( "<OPTION value=\"\">NONE\n");
	$Query = "select purchase_order from purchase_order where order_type = '$type' and po_status = 'OP' and company_id='$default_comp' order by purchase_order "; 
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Orders!<br>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		if ($order == $Row[0])
			echo( "<OPTION value=\"$Row[0]\" selected>$Row[0]\n");
		else
			echo( "<OPTION value=\"$Row[0]\">$Row[0]\n");
	}
	//release memory
	ibase_free_result($Result);
	echo("</SELECT><br>\n");
*/
	echo("<br>\n");
	//echo("<INPUT type=\"text\" name=\"line\" maxlength=\"4\" size=\"4\" value=\"$line\"><br>\n");
}
else
{
	echo("<INPUT type=\"text\" name=\"order\" maxlength=\"10\" size=\"10\" value=\"$order\" class=\"default\">\n");
	echo("<INPUT type=\"text\" name=\"line\" maxlength=\"4\" size=\"4\" value=\"$line\" class=\"default\"><br>\n");
}
echo("Supplier:<SELECT name=\"retfrom\" class=\"sel3\">\n");
//$Query = "select person_id, first_name, last_name from person where person_type in ('LE', 'RP') or person_type starting 'C' order by first_name, last_name "; 
$Query = "select person_id, first_name, last_name from person where person_type in ('LE') order by first_name, last_name "; 
$Query = "select person_id, first_name, last_name from person where person_type in ('LE', 'RP') or (person_type starting 'C' and person_type <> 'CA') order by first_name, last_name "; 
$Query = "select person_id, first_name, last_name from person where  (person_type starting 'C' and person_type <> 'CA') order by first_name, last_name "; 
$Query = "select person_id, first_name, last_name from person where person_type in ('RP','CO','CS','CV','CR','IS')  order by first_name, last_name "; 
$Query = "select person_id, first_name, last_name from person where person_type in ('CS','IS')  order by first_name, last_name "; 
$Query = "select person_id, first_name, last_name from person where person_type in ('CS','IS') and status = 'CU'  order by first_name, last_name "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Supplier!<br>\n");
	exit();
}
while ( ($Row = ibase_fetch_row($Result)) ) {
	if (isset($retfrom))
	{
		if ($retfrom == $Row[0])
		{
			echo( "<OPTION value=\"$Row[0]\" selected>$Row[1] $Row[2]\n");
		}
		else
		{
			echo( "<OPTION value=\"$Row[0]\">$Row[1] $Row[2]\n");
		}
	}
	else
	{
		echo( "<OPTION value=\"$Row[0]\">$Row[1] $Row[2]\n");
	}
}
//release memory
ibase_free_result($Result);
echo("</SELECT><br>\n");
//echo("Carrier:<INPUT type=\"text\" name=\"carrier\" size=\"10\" maxlength=\"10\"><br>");
echo("Carrier:<SELECT name=\"carrier\" size=\"1\" class=\"sel3\">\n");
$Query = "select carrier_id from carrier order by carrier_id desc"; 
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
	if ($wk_carrier_line == 1)
		echo( "<OPTION value=\"$Row[0]\" selected>$Row[0]\n");
	else
		echo( "<OPTION value=\"$Row[0]\">$Row[0]\n");
}
//release memory
ibase_free_result($Result);
echo("</SELECT>\n");
//echo("<div>Veh.Reg.:<INPUT type=\"text\" name=\"vehicle\" size=\"8\" maxlength=\"10\" value=\"$vehicle\"><br>\n");
//echo("<div>Veh.Reg.:<INPUT type=\"text\" name=\"vehicle\" size=\"8\" maxlength=\"10\" ");
echo("<div id=\"col8\">Veh.Reg.:<INPUT type=\"text\" name=\"vehicle\" size=\"8\" maxlength=\"10\" class=\"default\" ");
if (isset($vehicle))
{
	echo(" value=\"$vehicle\"><br>\n");
} else {
	echo(" value=\"\"><br>\n");
}
echo("Container:<label for=\"radioyes\"><INPUT type=\"radio\" name=\"container\" value=\"Y\" id=\"containeryes\">yes</label> \n");
//echo("<label for=\"radiono\"><INPUT type=\"radio\" name=\"container\" value=\"N\" id=\"containerno\">no</label></div> \n");
echo("<label for=\"radiono\"><INPUT type=\"radio\" name=\"container\" value=\"N\" id=\"containerno\">no</label> \n");
//echo("<div>Pallets:<SELECT name=\"pallet_type\" size=\"1\" class=\"sel1\">\n");
echo("<br>Pallets:<SELECT name=\"pallet_type\" size=\"1\" class=\"sel1\">\n");
//echo("<SELECT name=\"pallet_type\" size=\"1\" class=\"sel1\">\n");
/*
echo( "<OPTION value=\"N\">NONE\n");
echo( "<OPTION value=\"U\">UNKNOWN\n");
echo( "<OPTION value=\"C\">CHEP\n");
echo( "<OPTION value=\"O\">OWN\n");
*/
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
		echo( "<OPTION value=\"$Row[0]\" selected>$Row[1]\n");
		$wk_have_pallet_owners = "T";
	}
	else
		echo( "<OPTION value=\"$Row[0]\">$Row[1]\n");
}
//release memory
ibase_free_result($Result);
if ($wk_have_pallet_owners == "F")
{
	echo( "<OPTION value=\"N\">NONE\n");
	echo( "<OPTION value=\"U\">UNKNOWN\n");
	echo( "<OPTION value=\"C\">CHEP\n");
	echo( "<OPTION value=\"O\">OWN\n");
}
echo("</SELECT>\n");
//echo("Qty:<INPUT type=\"text\" name=\"pallet_qty\" size=\"4\" maxlength=\"4\" value=\"$pallet_qty\"><br>\n");
echo("Qty:<INPUT type=\"text\" name=\"pallet_qty\" size=\"4\" maxlength=\"4\" class=\"default\" ");
if (isset($pallet_qty))
{
	echo(" value=\"$pallet_qty\"><br>\n");
} else {
	echo(" value=\"\"><br>\n");
}
//echo("Received:<INPUT type=\"text\" name=\"received_qty\" size=\"4\" maxlength=\"4\" value=\"$received_qty\"><br>\n");
echo("Received:<INPUT type=\"text\" name=\"received_qty\" size=\"4\" maxlength=\"4\" class=\"default\" ");
if (isset($received_qty))
{
	echo(" value=\"$received_qty\"><br>\n");
} else {
	echo(" value=\"\"><br>\n");
}
//echo("Labels :<INPUT type=\"text\" name=\"label_qty\" size=\"4\" maxlength=\"4\" value=\"$label_qty\">\n");
echo("Labels :<INPUT type=\"text\" name=\"label_qty\" size=\"4\" maxlength=\"4\" class=\"default\" ");
if (isset($label_qty))
{
	echo(" value=\"$label_qty\">\n");
} else {
	echo(" value=\"1\">\n");
}
//echo("Con/AWB No.<INPUT type=\"text\" name=\"consignment\" maxlength=\"20\" size=\"20\" value=\"$consignment\"><br>\n");
echo("Con/AWB No.<INPUT type=\"text\" name=\"consignment\" maxlength=\"20\" size=\"20\" class=\"default\" ");
if (isset($consignment))
{
	echo(" value=\"$consignment\"><br>\n");
} else {
	echo(" value=\"\"><br>\n");
}
echo("</div><div ID=\"col5\">\n");
echo("Prt:<SELECT name=\"printer\" class=\"sel4\">\n");
$Query = "select device_id from sys_equip where device_type = 'PR' order by device_id "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Devices!<BR>\n");
	exit();
}
$wk_first = True;
while ( ($Row = ibase_fetch_row($Result)) ) {
	if (isset($printer))
	{
		if ($Row[0] == $printer)
		{
			echo( "<OPTION value=\"$Row[0]\" selected >$Row[0]\n");
		}
		else
		{
			echo( "<OPTION value=\"$Row[0]\">$Row[0]\n");
		}
	} else {
		if ($wk_first)
		{
			echo( "<OPTION value=\"$Row[0]\" selected >$Row[0]\n");
			$wk_first = False;
		} else {
			echo( "<OPTION value=\"$Row[0]\">$Row[0]\n");
		}
	}
}
//release memory
ibase_free_result($Result);
echo("</SELECT><BR>\n");
echo("</div>\n");
echo("<div id=\"col7\">\n");
{
	// html 4.0 browser
	echo("<table border=\"0\" align=\"LEFT\">");
	whm2buttons('Accept', './receive_menu.php',"N","Back_50x100.gif","Back","accept.gif", "Y");
	//whm2buttons('Accept', './receive_menu.php',"Y","Back_50x100.gif","Back","accept.gif", "Y");
	//whm2buttons('Accept', './receive_menu.php',"Y","Back_50x100.gif","Back","accept.gif"  );
}
//if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
//{
//	echo("<INPUT type=\"submit\" name=\"accept\" value=\"Accept\">\n");
//}
//else
//{
//	echo("<BUTTON name=\"accept\" value=\"Accept\" type=\"submit\" id=\"col1\">\n");
//	echo("Accept<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
//}

//if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
//{
//	// html 3.2 browser
//	echo("<form action=\"./receive_menu.php\" method=\"post\" name=goback>\n");
//	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
//	echo("</form>\n");
//}
//else
//{
//	// html 4.0 browser
//	echo("<BUTTON name=\"back\" type=\"button\" id=\"col2\" onfocus=\"location.href='./receive_menu.php';\">\n");
//	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
//}
echo("</div>\n");
echo("<div id=\"col6\">\n");
echo("<INPUT type=\"text\" name=\"message\" readonly size=\"30\" class=\"message\">\n");
echo("</div>\n");
?>
<script type="text/javascript">
<?php
if (isset($message))
{
		echo("document.getcons.message.value=\"" . $message . "\";\n");
}
else
{
	if ($owner == "")
	{
		echo("document.getcons.message.value=\"Select Owner\";\n");
		echo("document.getcomp.owner.focus();\n");
	}
	else
	{
		echo("document.getcons.message.value=\"Scan Consignment No\";\n");
		echo("document.getcons.consignment.focus();\n");
	}
}
?>
</script>
</html>
