<?php
include "../login.inc";
?>
<html>
 <head>
<?php
 include "viewport.php";
?>
  <title>Get SSN you are splitting to</title>
 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FFFFFF">

  <h3 ALIGN="LEFT">Enter SSN TO</h3>

 <FORM action="GetSSNSplitQtyTo.php" method="post" name=getssn>
 <P>
<?php
echo("<INPUT type=\"hidden\" name=\"transaction2_type\" value=\"TRSS\">");
echo("<INPUT type=\"hidden\" name=\"ssnsplit\" value=\"Y\">");
echo("SSN: <INPUT type=\"text\" name=\"ssnto\"");
if (isset($_POST['ssnto'])) 
{
	echo(" value=\"".$_POST['ssnto']."\"");
}
if (isset($_GET['ssnto'])) 
{
	echo(" value=\"".$_GET['ssnto']."\"");
}
echo(" size=\"20\"");
echo(" maxlength=\"20\"><BR>\n");
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
document.getssn.ssnto.focus();
</script>
</body>
</html>

