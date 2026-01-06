<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";

?>
<html>
 <head>
<?php
 include "viewport.php";
?>
  <title>Despatch Exit a Consignment</title>
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
<?php
if (strpos($_SERVER['HTTP_USER_AGENT'] , "NetFront") === false)
{
	echo('<link rel=stylesheet type="text/css" href="getexit.css">');
}
else
{
	echo('<link rel=stylesheet type="text/css" href="getexit.css">');
}
?>
</head>
<body>

<?php
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

/*
want to have an input field like in putaway
allow scanning a connote no
or pack label
use that to get the connote no for te despatch
*/
echo ("<h4>Despatch Exit</h4>\n");
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

$Query = "select pick_despatch.awb_consignment_no from pick_despatch where pick_despatch.despatch_status = 'DC' group by pick_despatch.awb_consignment_no ";
//echo($Query);

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Get Connote for Choice!<BR>\n");
	exit();
}

echo("<FONT size=\"2\">\n");
echo("<br><br>\n");
echo("<FORM action=\"transactionDX.php\" method=\"post\" name=getexit\n>");

echo("Consignment:<select name=\"consignment\" size=\"1\" >\n");

while ( ($Row = ibase_fetch_row($Result)) ) {
	echo( "<OPTION value=\"$Row[0]\">$Row[0]\n");
}
echo("</select>\n");
echo("<br><br>Input: <INPUT type=\"text\" name=\"infield\" size=\"26\"");
echo(" onchange=\"document.getssn.submit();\">");
echo ("<br><br><table>\n");
echo ("<tr>\n");
echo("<th>Select/Scan Consignment</th>\n");
echo ("</tr>\n");
echo ("</table>\n");
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
	echo("<table BORDER=\"0\" ALIGN=\"LEFT\">");
	//whm2buttons("Accept","./despatch_menu.php");
	whm2buttons('Accept',"./despatch_menu.php" ,"Y" ,"Back_50x100.gif" ,"Back" ,"accept.gif");
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='./despatch_menu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
/*
<script type="text/javascript">
 document.getexit.connote.focus(); 
</script>
*/
?>
<script type="text/javascript">
document.getexit.infield.focus();
</script>
</body>
</html>
