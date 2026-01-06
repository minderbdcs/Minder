<?php
include "../login.inc";
?>
<html>
 <head>
<?php
include "viewport.php";
include "db_access.php";
?>
  <title>Get the quantity you are Issuing/Receiving</title>
 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FFFFFF">

  <h3 ALIGN="LEFT">Enter Quantity to Move</h3>

 <FORM action="GetLocnTo.php" method="post" name=getqty>
 <P>
<?php
if (isset($_POST['ssn_from'])) 
{
	$ssn_from = $_POST['ssn_from'];
}
if (isset($_GET['ssn_from'])) 
{
	$ssn_from = $_GET['ssn_from'];
}
if (isset($ssn_from))
{
	echo("SSN <INPUT type=\"text\" readonly name=\"ssnfrom\" value=\"$ssn_from\"><br>");
}
if (isset($_POST['location_from'])) 
{
	$location_from = $_POST['location_from'];
}
if (isset($_GET['location_from'])) 
{
	$location_from = $_GET['location_from'];
}
if (isset($location_from))
{
	echo("From <INPUT type=\"text\" readonly name=\"locationfrom\" value=\"$location_from\"><br>");
}
echo("QTY: <INPUT type=\"text\" name=\"qtyto\"");
if (isset($_POST['qtyto'])) 
{
	echo(" value=\"".$_POST['qtyto']."\"");
}
if (isset($_GET['qtyto'])) 
{
	echo(" value=\"".$_GET['qtyto']."\"");
}
echo(" size=\"10\"");
echo(" maxlength=\"10\"><BR>\n");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<INPUT type=\"submit\" name=\"send\" value=\"Send!\">\n");
	echo("</FORM>\n");
}
else
*/
{
	// html 4.0 browser
	$alt = "Send";
	// Create a table.
	echo ("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	echo ("<TR>");
	echo ("<TD>");
	echo("<INPUT type=\"IMAGE\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
*/
	echo('SRC="/icons/whm/accept.gif" alt="' . $alt . '">');
	echo("</FORM>");
	echo ("</TD>");
	echo ("</TR>");
	echo ("</TABLE>");
/*
	echo("<BUTTON name=\"send\" value=\"Send!\" type=\"submit\">\n");
	echo("Send<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
	echo("</FORM>\n");
*/
}
?>
</P>
<script type="text/javascript">
document.getqty.qtyto.focus();
</script>
</body>
</html>
