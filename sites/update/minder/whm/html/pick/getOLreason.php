<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
$ssn = '';
$label_no = '';
$order = '';
$prod_no = '';
$description = '';
$uom = '';
$order_qty = 0;
$picked_qty = 0;
$required_qty = 0;
$scannedssn = '';
if (isset($_COOKIE['BDCSData']))
{
	list($label_no, $order, $ssn, $prod_no, $uom, $description, $required_qty, $location, $scannedssn, $picked_qty) = explode("|", $_COOKIE["BDCSData"]);
}
	
//echo($Query);

echo("<FONT size=\"2\">\n");
echo("<FORM action=\"transactionOL.php\" method=\"post\" name=getreason\n>");
echo("Pick<INPUT type=\"text\" readonly name=\"label\" size=\"7\" value=\"$label_no\">");
echo("SO<INPUT type=\"text\" readonly name=\"order\" size=\"7\" value=\"$order\"><BR>");
if ($ssn <> '')
{
	echo("SSN<INPUT type=\"text\" readonly name=\"ssn\" size=\"8\" value=\"$ssn\">");
	echo("<INPUT type=\"text\" readonly name=\"uom\" size=\"8\" value=\"$uom\">");
	echo("<INPUT type=\"text\" readonly name=\"desc\" size=\"20\" value=\"$description\">");
}
else
{
	echo("Part: <INPUT type=\"text\" readonly name=\"prod\" size=\"8\" value=\"$prod_no\" >");
	echo("<INPUT type=\"text\" readonly name=\"uom\" size=\"8\" value=\"$uom\" >");
	echo("<INPUT type=\"text\" readonly name=\"desc\" size=\"20\" value=\"$description\" ><BR>");
}
echo("ShortFall Reason<SELECT name=\"reference\" >");
echo("<OPTION value=\"No Stock\">No Stock");
echo("<OPTION value=\"Damaged Stock\">Damaged Stock");
echo("<OPTION value=\"Wrong Stock\">Wrong Stock");
echo("<OPTION value=\"None\">No Reason");
echo("</SELECT>");

echo("<BR>Qty Reqd<INPUT type=\"text\" readonly name=\"required_qty\" size=\"4\" value=\"$required_qty\">");
echo("Picked<INPUT type=\"text\" readonly name=\"qtypicked\" size=\"4\" value=\"$picked_qty\">");

echo ("<TABLE>\n");
echo ("<TR>\n");
echo("<TH>Enter Reason</TH>\n");
echo ("</TR>\n");
echo ("</TABLE>\n");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	echo("<INPUT type=\"submit\" name=\"accept\" value=\"Accept\">\n");
}
else
{
	echo("<BUTTON name=\"accept\" value=\"Accept\" type=\"submit\">\n");
	echo("Accept<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
}
*/
//echo total
//echo("</FORM>\n");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<FORM action=\"getfromssn.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
*/
{
	// html 4.0 browser
 	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	whm2buttons('Accept', 'getfromssn.php');
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='getfromssn.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
?>
<SCRIPT>
document.getreason.reference.focus();
</SCRIPT>
</HTML>
