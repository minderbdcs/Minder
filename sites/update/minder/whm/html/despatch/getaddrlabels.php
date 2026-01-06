<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";

echo("<html>\n");
echo("<head>\n");
include "viewport.php";
echo("<title>Get Address Labels you are working on</title>\n");
//echo("<link rel=stylesheet type="text/css" href="addrprodlabel.css">\n");
echo("</head>\n");
echo("<body>\n");
if (isset($_GET['message']))
{
	$message = $_GET['message'];
	echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
}
if (isset($_POST['consignment']))
{
	$consignment = $_POST['consignment'];
}
if (isset($_GET['consignment']))
{
	$consignment = $_GET['consignment'];
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
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

$Query = "SELECT count(*) FROM PACK_ID JOIN PICK_DESPATCH ON PICK_DESPATCH.DESPATCH_ID = PACK_ID.DESPATCH_ID WHERE (PACK_ID.DESPATCH_LABEL_NO IS NOT NULL)  AND PICK_DESPATCH.AWB_CONSIGNMENT_NO = '$consignment'";
//echo($Query);

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Query Packs for Consignment!<BR>\n");
	exit();
}

while ( ($Row = ibase_fetch_row($Result)) ) {
	$scanned_qty = $Row[0];
}
echo("<FONT size=\"2\">\n");
echo("<FORM action=\"transactionOL.php\" method=\"post\" name=getlabel\n>");

echo("Consignment <INPUT type=\"text\" name=\"consignment\" readonly size=\"16\" value=\"$consignment\"><BR>");
echo("Printer <INPUT type=\"text\" name=\"printer\" readonly size=\"2\" value=\"$printer\"></BR>");
echo("Qty Labels <INPUT type=\"text\" name=\"label_qty\" readonly size=\"3\" value=\"$label_qty\">");
echo("Scanned <INPUT type=\"text\" name=\"scanned_qty\" readonly size=\"3\" value=\"$scanned_qty\"><BR>");
echo("Address Label: <INPUT type=\"text\" name=\"addrlabel\" size=\"20\" maxsize=\"20\">");
echo ("<TABLE>\n");
echo ("<TR>\n");
echo("<TH>Scan Address Labels on Connote</TH>\n");
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
//echo("</FORM>\n");

/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<FORM action=\"./despatch_menu.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
*/
{
	// html 4.0 browser
	echo ("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
	//whm2buttons("Accept","./despatch_menu.php");
	whm2buttons('Accept',"./despatch_menu.php" ,"Y" ,"Back_50x100.gif" ,"Back" ,"accept.gif");
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='./despatch_menu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
?>
<script type="text/javascript">
document.getlabel.addrlabel.focus();
</script>
</body>
</html>
