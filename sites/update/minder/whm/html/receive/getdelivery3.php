<?php
include "../login.inc";
?>
<html>
 <head>
  <title>Receive Delivery Extra </title>
<?php
include "viewport.php";
if (strpos($_SERVER['HTTP_USER_AGENT'] , "NetFront") === false)
{
	echo('<link rel=stylesheet type="text/css" href="verify3LD.css">');
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
function processEdit() {
	var dowhat;
  	var csum;
echo("got 1");
  /* document.getprodqtys.message.value="in process edit"; */
  if ( document.getprodqtys.other_qty1.value=="")
  {
	document.getprodqtys.other_qty1.value = "0";
  }
echo("got 2");
  if ( document.getprodqtys.other_qty3.value=="")
  {
	document.getprodqtys.other_qty3.value = "0";
  }
echo("got 3");
  if ( document.getprodqtys.other_qty2.value=="")
  {
	document.getprodqtys.other_qty2.value = "0";
  }
echo("got 4");
  if ( document.getprodqtys.other_qty4.value=="")
  {
	document.getprodqtys.other_qty4.value = "0";
  }
echo("got 5");
  if ( chkNumeric(document.getprodqtys.other_qty1.value)==false)
  {
  	document.getprodqtys.message.value="First Qty Not Numeric";
	document.getprodqtys.other_qty1.focus();
  	return false;
  }
echo("got 6");
  if ( chkNumeric(document.getprodqtys.other_qty3.value)==false)
  {
  	document.getprodqtys.message.value="Third Qty Not Numeric";
	document.getprodqtys.other_qty3.focus();
  	return false;
  }
echo("got 7");
  if ( chkNumeric(document.getprodqtys.other_qty2.value)==false)
  {
  	document.getprodqtys.message.value="Second Qty Not Numeric";
	document.getprodqtys.other_qty2.focus();
  	return false;
  }
echo("got 8");
	{
		/* the yes */
		document.goforw.other_qty1.value = document.getprodqtys.other_qty1.value ;
		document.goforw.other_qty2.value = document.getprodqtys.other_qty2.value ;
		document.goforw.other_qty3.value = document.getprodqtys.other_qty3.value ;
		document.goforw.other_qty4.value = document.getprodqtys.other_qty4.value ;
echo("got 9");
		dowhat = document.getprodqtys.owner.selectedIndex ;
		document.goforw.owner.value = document.getprodqtys.owner.options[dowhat].value ;
		alert("before submit goforw"):
		document.goforw.submit();
		alert("submitted goforw"):
	}
echo("got 10");
  return false;
}
function processComment() {
	var dowhat;
	document.getcomment.other_qty1.value = document.getprodqtys.other_qty1.value ;
	document.getcomment.other_qty2.value = document.getprodqtys.other_qty2.value ;
	document.getcomment.other_qty3.value = document.getprodqtys.other_qty3.value ;
	document.getcomment.other_qty4.value = document.getprodqtys.other_qty4.value ;
	dowhat = document.getprodqtys.owner.selectedIndex ;
	document.getcomment.owner.value = document.getprodqtys.owner.options[dowhat].value ;
	document.getcomment.submit();
  return true;
}
onerror = errorHandler
</script>
<?php
require_once 'DB.php';
require 'db_access.php';
require 'logme.php';

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
}
/* end of function */
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
	$owner   = getBDCScookie($Link, $tran_device, "owner" );

	$retfrom = getBDCScookie($Link, $tran_device, "retfrom" );
	$product = getBDCScookie($Link, $tran_device, "product" );
	$other_qty1 = getBDCScookie($Link, $tran_device, "other_qty1" );
	$other_qty3 = getBDCScookie($Link, $tran_device, "other_qty3" );
	$other_qty2 = getBDCScookie($Link, $tran_device, "other_qty2" );
	$other_qty4 = getBDCScookie($Link, $tran_device, "other_qty4" );
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
	$printer = "PA";
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

if (isset($_POST['owner']))
{
	$owner = $_POST['owner'];
}
if (isset($_GET['owner']))
{
	$owner = $_GET['owner'];
}
$other_qty1 = 0;
$other_qty2 = 0;
$other_qty3 = 0;
$other_qty4 = 0;
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
/*
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<br>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
*/

$useVehicleDesc1  = getVehicleOption($Link,  "OTHER_QTY1" );
$useVehicleDesc2  = getVehicleOption($Link,  "OTHER_QTY2" );
$useVehicleDesc3  = getVehicleOption($Link,  "OTHER_QTY3" );

//echo("<FONT size=\"2\">\n");
echo("<div id=\"col3\">");
echo("<form action=\"transNDother.php\" method=\"post\" name=getprodqtys onsubmit=\"return processEdit();\">");
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
echo("<INPUT type=\"text\" name=\"order\" maxlength=\"10\" size=\"10\" readonly value=\"$order\" class=\"noread\">\n");
//echo("<INPUT type=\"text\" name=\"line\" maxlength=\"4\" size=\"4\" readonly value=\"$line\" class=\"noread\"><br>\n");
echo("<INPUT type=\"hidden\" name=\"line\"  value=\"$line\" >\n");
echo("<INPUT type=\"text\" name=\"grn\" maxlength=\"5\" size=\"5\" readonly value=\"$grn\" class=\"noread\"><br>\n");
echo("Owned by:<BR><select name=\"owner\" class=\"sel3\">\n");
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
if (isset($owner))
{
	if ($owner != "")
	{
		$default_comp = $owner;
	}
}
$Query = "select company_id, name from company order by name "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Company!<BR>\n");
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
echo("</select>\n");

echo("</div>");
echo("<div id=\"col7\">");
echo("<table border=\"0\">\n");
echo("<tr><td>");
if ($useVehicleDesc1[0] == "T" )
{
	echo("<label for=\"other_qty1\">$useVehicleDesc1[1]:</label>");
	echo("</td><td>");
	echo("<INPUT type=\"text\" name=\"other_qty1\" size=\"6\" maxlength=\"6\" class=\"default\" value=\"$other_qty1\">\n");
	echo("</td></tr><tr><td>");
} else {
	echo("<INPUT type=\"hidden\" name=\"other_qty1\"  value=\"$other_qty1\">\n");
}
if ($useVehicleDesc2[0] == "T" )
{
	echo("<label for=\"other_qty2\">$useVehicleDesc2[1]:</label>");
	echo("</td><td>");
	echo("<INPUT type=\"text\" name=\"other_qty2\" size=\"6\" maxlength=\"6\" class=\"default\" value=\"$other_qty2\">\n");
	echo("</td></tr><tr><td>");
} else {
	echo("<INPUT type=\"hidden\" name=\"other_qty2\"  value=\"$other_qty2\">\n");
}
if ($useVehicleDesc3[0] == "T" )
{
	echo("<label for=\"other_qty3\">$useVehicleDesc3[1]:</label>");
	echo("</td><td>");
	echo("<INPUT type=\"text\" name=\"other_qty3\" size=\"6\" maxlength=\"6\" class=\"default\" value=\"$other_qty3\">\n");
	echo("</td></tr><tr><table>");
} else {
	echo("<INPUT type=\"hidden\" name=\"other_qty3\"  value=\"$other_qty3\">\n");
}
echo("<INPUT type=\"hidden\" name=\"other_qty4\"   value=\"$other_qty4\">\n");
echo("</div>");
echo("<div id=\"col8\">");
echo("<br>\n");
{
	// html 4.0 browser
	$alt = "Accept";
	// Create a table.
	echo ("<table BORDER=\"0\" ALIGN=\"LEFT\" id=\"col9\">");
	echo ("<tr>");
	echo ("<td>");
	//echo("<INPUT type=\"IMAGE\" ");  
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
*/
	echo('SRC="/icons/whm/accept.gif" alt="' . $alt . '">');
	echo("</form>");
	echo ("</td>");
	echo ("<td>");
	echo("<form action=\"transNX.php\"  method=\"post\" onsubmit=\"return processBack();\" name=getssnback>\n");
	echo("<INPUT type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	//echo("<INPUT type=\"IMAGE\" ");  
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/Back_50x100.gif" onClick="return processBack();" alt="Back">');
	echo("</form>");
	echo ("</td>");
//	echo ("</tr>");

//	echo ("<tr>");
	echo ("<td>");
	$alt = "Comments";
	echo("<form action=\"comments.php\"  method=\"post\" onsubmit=\"return processComment();\" name=getcomment>\n");
	//echo("<INPUT type=\"IMAGE\" ");  
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
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
	echo("<INPUT type=\"hidden\" name=\"location\" >\n");
	echo("<INPUT type=\"hidden\" name=\"printer\" >\n");
	echo("<INPUT type=\"hidden\" name=\"problem\" >\n");
	echo("<INPUT type=\"hidden\" name=\"uom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"product\" >\n");
	echo("<INPUT type=\"hidden\" name=\"other_qty1\" >\n");
	echo("<INPUT type=\"hidden\" name=\"other_qty2\" >\n");
	echo("<INPUT type=\"hidden\" name=\"other_qty3\" >\n");
	echo("<INPUT type=\"hidden\" name=\"other_qty4\" >\n");
	echo("<INPUT type=\"hidden\" name=\"from\" value=\"".$_SERVER['PHP_SELF']."\" >");
	echo("</form>");
	echo ("</td>");
	echo ("</tr>");
	echo ("</table>");

/*
	//echo("<BUTTON name=\"back\" type=\"button\" id=\"col2\" onfocus=\"location.href='./receive_menu.php';\">\n");
	echo("<BUTTON name=\"back\" type=\"button\" id=\"col10\" onClick=\"return processBack();\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}

echo("</div>\n");
	echo ("</tr>");
	echo ("</table>");
/*
echo("<form action=\"transNX.php\" method=\"post\" name=getssnback >");
echo("<INPUT type=\"hidden\" name=\"grn\" value=\"$grn\" >");
echo("</form>");
*/
echo("<form action=\"transNDother.php\" method=\"post\" name=goforw >");
echo("<INPUT type=\"hidden\" name=\"type\" value=\"$type\" >");
echo("<INPUT type=\"hidden\" name=\"grn\" value=\"$grn\" >");
echo("<INPUT type=\"hidden\" name=\"complete\" >");
echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\">\n");
echo("<INPUT type=\"hidden\" name=\"line\" value=\"$line\">\n");
echo("<INPUT type=\"hidden\" name=\"retfrom\" >\n");
echo("<INPUT type=\"hidden\" name=\"uom\" >\n");
echo("<INPUT type=\"hidden\" name=\"product\" >\n");
echo("<INPUT type=\"hidden\" name=\"received_ssn_qty\" >\n");
echo("<INPUT type=\"hidden\" name=\"location\" >\n");
echo("<INPUT type=\"hidden\" name=\"printer\" >\n");
echo("<INPUT type=\"hidden\" name=\"other_qty1\" >\n");
echo("<INPUT type=\"hidden\" name=\"other_qty2\" >\n");
echo("<INPUT type=\"hidden\" name=\"other_qty3\" >\n");
echo("<INPUT type=\"hidden\" name=\"other_qty4\" >\n");
echo("<INPUT type=\"hidden\" name=\"problem\" >\n");
echo("<INPUT type=\"hidden\" name=\"owner\" >\n");
echo("</form>");
?>
<script type="text/javascript">
<?php
{
	echo("document.getprodqtys.message.value=\"Select Owner\";\n");
	echo("document.getprodqtys.owner.focus();\n");
}
?>
</script>
</html>
