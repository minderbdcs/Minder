<?php
include "../login.inc";
include "viewport.php";
?>
<html>
 <head>
  <title>Get GRN Barcode for Receive</title>
<link rel=stylesheet type="text/css" href="delivery2.css">
<script type="text/javascript">
var mynull= "";
</script>
 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FFFFF0">

  <h2 ALIGN="LEFT">Scan GRN Barcode</h2>

<div id=\"col1\">
 <form action="verifyLD2.php" method="post" name=getgrn>
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
if (isset($_POST['grnorder'])) 
{
	$wk_grnorder = $_POST['grnorder'];
}
if (isset($_GET['grnorder'])) 
{
	$wk_grnorder = $_GET['grnorder'];
}
if (isset($_POST['message'])) 
{
	$wk_message = $_POST['message'];
}
if (isset($_GET['message'])) 
{
	$wk_message = $_GET['message'];
}
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
echo("GRN Barcode: <INPUT type=\"text\" name=\"grnorder\" class=\"default\"");
if (isset($wk_grnorder)) 
{
	echo(" value=\"".$wk_grnorder."\"");
}
echo(" size=\"22\"");
echo(" maxlength=\"22\" onfocus=\"document.getgrn.grnorder.value=mynull;\" ><BR>\n");

echo("</div>\n");
echo("<div id=\"col7\">\n");
{
	// html 4.0 browser
	echo("<table border=\"0\" align=\"LEFT\">");
	whm2buttons('Accept', './receive_menu.php',"N","Back_50x100.gif","Back","accept.gif", "Y");
	//whm2buttons('Accept', './receive_menu.php',"Y","Back_50x100.gif","Back","accept.gif", "Y");
	//whm2buttons('Accept', './receive_menu.php',"Y","Back_50x100.gif","Back","accept.gif"  );
}
echo("</div>\n");
?>
</P>
<script type="text/javascript">
document.getgrn.grnorder.focus();
</script>
</body>
</html>
