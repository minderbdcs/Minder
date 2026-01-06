<?php
include "../login.inc";
?>
<html>
<head>
<?php
 include "viewport.php";
?>
<title>Get Location</title>
</head>
<body>
<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";

if (isset($_GET['message']))
{
	$message = $_GET['message'];
	echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
}
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

$Query = "select location.wh_id, location.locn_id, warehouse.description from location join warehouse on warehouse.wh_id = location.wh_id where locn_id = 'INTRANST'";
//echo($Query);

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Location for Choice!<BR>\n");
	exit();
}

echo("<FONT size=\"2\">\n");
echo("<FORM action=\"transactionDM.php\" method=\"post\" name=getlocn\n>");

//echo("Location: <INPUT type=\"text\" name=\"location\" size=\"10\"></BR>");
echo("Location:<SELECT name=\"location\" size=\"1\" >\n");
while ( ($Row = ibase_fetch_row($Result)) ) {
	echo( "<OPTION value=\"$Row[0]$Row[1]\">$Row[2]\n");
}
echo("</SELECT>\n");
echo("Printer <INPUT type=\"text\" readonly name=\"printer\" value=\"PD\" size=\"2\"></BR>");
//echo("Qty <INPUT type=\"text\" name=\"labelqty\" value=\"1\" size=\"1\"></BR>");
echo ("<TABLE>\n");
echo ("<TR>\n");
echo("<TH>Select Despatch Location</TH>\n");
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
	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	//whm2buttons("Accept","./despatch_menu.php");
	whm2buttons('Accept',"./despatch_menu.php" ,"Y" ,"Back_50x100.gif" ,"Back" ,"accept.gif");
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='./despatch_menu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
?>
<script type="text/javascript">
document.getlocn.location.focus();
</script>
</body>
</html>
