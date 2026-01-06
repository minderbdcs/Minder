<?php
include "../login.inc";
?>
<html>
<head>
<?php
 include "viewport.php";
?>
  <title>Get GRN for Receive</title>
<link rel=stylesheet type="text/css" href="delivery2.css">
<style type="text/css">
font-size: 1.2em;
</style>
<script type="text/javascript">
var mynull= "";
</script>
</head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FFFFF0">

<h2 ALIGN="LEFT">Scan GRN</h2>

<div id=\"col1\">
 <form action="transND3.php" method="post" name=getgrn>
 <P>
<?php
require_once 'DB.php';
require 'db_access.php';

include "2buttons.php";
include "logme.php";

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	
if (isset($_POST['type'])) 
{
	$wk_type = $_POST['type'];
}
if (isset($_GET['type'])) 
{
	$wk_type = $_GET['type'];
}
if (isset($_POST['grn'])) 
{
	$wk_grn = $_POST['grn'];
}
if (isset($_GET['grn'])) 
{
	$wk_grn = $_GET['grn'];
}
if (isset($_POST['message'])) 
{
	$wk_message = $_POST['message'];
}
if (isset($_GET['message'])) 
{
	$wk_message = $_GET['message'];
}
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<br>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

//echo("<font size=\"2\">\n");
if (isset($wk_message))
{
	echo ("<B><FONT COLOR=RED>$wk_message</FONT></B><BR>\n");
}
if (isset($wk_type))
{
	echo("<INPUT type=\"hidden\" name=\"type\"");
	echo(" value=\"".$wk_type."\"");
	echo(">\n");
}
echo("GRN: <INPUT type=\"text\" name=\"grn\" class=\"default\"");
if (isset($wk_grn)) 
{
	echo(" value=\"".$wk_grn."\"");
}
echo(" size=\"10\"");
echo(" maxlength=\"10\" onfocus=\"document.getgrn.grn.value=mynull;\" ><BR>\n");
echo("Labels :<INPUT type=\"text\" name=\"label_qty\" size=\"4\" maxlength=\"4\" class=\"default\" ");
if (isset($label_qty))
{
	echo(" value=\"$label_qty\">\n");
} else {
	echo(" value=\"1\">\n");
}
echo("<BR>");
//echo("</div><div ID=\"col5\">\n");
echo("Printer:<SELECT name=\"printer\" class=\"sel4\">\n");
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
}
echo("</div>\n");
?>
</P>
<script type="text/javascript">
document.getgrn.grn.focus();
</script>
</body>
</html>
