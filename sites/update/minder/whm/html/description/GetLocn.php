<?php
include "../login.inc";
setcookie("BDCSData","");
?>
<html>
 <head>
  <title>Get Location you are working in</title>
<?php
 include "viewport.php";
?>
 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FFFFFF">

  <h3 ALIGN="LEFT">Enter Location</h3>

 <table BORDER="0" >
 <form action="GetSSN.php" method="post" name=getlocn>
 <P>
<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
$reference="Description";
print("<INPUT type=\"hidden\" name=\"reference\"");
print(" value=\"".$reference."\">");
print("Location: <INPUT type=\"text\" name=\"location\"");
if (isset($_POST['location'])) 
{
	print(" value=\"".$_POST['location']."\"");
}
if (isset($_GET['location'])) 
{
	print(" value=\"".$_GET['location']."\"");
}
print(" size=\"10\"");
print(" maxlength=\"10\"><BR>\n");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	print("<INPUT type=\"submit\" name=\"send\" value=\"Send!\">\n");
	print("</form>\n");
	print("<form action=\"../mainmenu.php\" method=\"post\" name=goback>\n");
	print("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	print("</form>\n");
}
else
*/
{
	// html 4.0 browser
	//whm2buttons('Send!');
	whm2buttons('Send',"../mainmenu.php" ,"Y" ,"Back_50x100.gif" ,"Back" ,"send.gif");
/*
	print("<BUTTON name=\"send\" value=\"Send!\" type=\"submit\">\n");
	print("Send<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
	print("</form>\n");
	print("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='../mainmenu.php';\">\n");
	print("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
?>
</P>
</body>
<script type="text/javascript">
document.getlocn.location.focus();
</script>
</html>
