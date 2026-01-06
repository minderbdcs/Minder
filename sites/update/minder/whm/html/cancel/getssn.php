<html>
 <head>
  <title>Get SSN to find details for</title>
 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FFFFFF">

  <h2 align="LEFT">Enter SSN to Cancel from Receive</h2>

 <TABLE BORDER="0" ALIGN="LEFT">
 <FORM action="getssn_details.php" method="post" name=getssn>
 <P>
<?php
include "2buttons.php";
if (isset($_GET['message']))
{
	$message = $_GET['message'];
}
if (isset($_POST['message']))
{
	$message = $_POST['message'];
}
if (isset($message))
{
	echo ("<B><FONT COLOR=RED>$message</FONT></B><BR>\n");
}
echo('SSN: <INPUT type="text" name="ssn_id" value=""><BR>');
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<INPUT type=\"submit\" name=\"send\" value=\"Send!\">\n");
	echo("</FORM>\n");
	echo("<FORM action=\"../mainmenu.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
*/
{
	// html 4.0 browser
	whm2buttons('Send');
/*
	echo("<BUTTON name=\"send\" value=\"Send!\" type=\"submit\">\n");
	echo("More<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
	echo("</FORM>\n");
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='../mainmenu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
?>
<script type="text/javascript">
document.getssn.ssn_id.focus();
</script>
</body>
</html>
