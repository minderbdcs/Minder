<?php
include "../login.inc";
setcookie("BDCSData","");
?>
<html>
 <head>
<?php
 include "viewport.php";
?>
  <title>Get SSN you are working on</title>
 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FFFFFF">
  <h4 ALIGN="LEFT">Enter SSN to Split</h4>

<?php
	include "db_access.php";
	include "2buttons.php";
	if (isset($_GET['message']))
	{
		$message = $_GET['message'];
		if ($message == 'connect')
		{
			echo ("<B><FONT COLOR=RED>Can't Connect to DATABASE!</FONT></B>\n");
		}
		if ($message == 'query')
		{
			echo ("<B><FONT COLOR=RED>Can't Query ISSN!</FONT></B>\n");
		}
		if ($message == 'nossn')
		{
			echo ("<B><FONT COLOR=RED>SSN Not Found!</FONT></B>\n");
		}
		if ($message == 'noqty')
		{
			echo ("<B><FONT COLOR=RED>SSN has NO Quantity!</FONT></B>\n");
		}
		if ($message == 'zeroqty')
		{
			echo ("<B><FONT COLOR=RED>SSN Quantity too Small to Split!</FONT></B>\n");
		}
		if ($message == 'lowqty')
		{
			echo ("<B><FONT COLOR=RED>SSN Quantity too Small for this Split!</FONT></B>\n");
		}
	}
?>

 <FORM action="PostFrom.php" method="post" name=getssn>
 <P>
<?php
echo("<INPUT type=\"hidden\" name=\"transaction_type\" value=\"TRSS\">");
echo("<INPUT type=\"hidden\" name=\"tran_type\" value=\"SSN\">");
echo("<INPUT type=\"hidden\" name=\"ssnsplit\" value=\"Y\">");
echo("SSN:      <INPUT type=\"text\" name=\"ssnfrom\"");
if (isset($_POST['ssnfrom'])) 
{
	echo(" value=\"".$_POST['ssnfrom']."\"");
}
if (isset($_GET['ssnfrom'])) 
{
	echo(" value=\"".$_GET['ssnfrom']."\"");
}
echo(" size=\"20\"");
echo(" maxlength=\"20\"><BR>\n");
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
 	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	whm2buttons('Send', 'Transfer_Menu.php',"Y","Back_50x100.gif","Back","accept.gif");
/*
	echo("<BUTTON name=\"send\" value=\"Send!\" type=\"submit\">\n");
	echo("Send<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
	echo("</FORM>\n");
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='Transfer_Menu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
?>
</P>
<script type="text/javascript">
document.getssn.ssnfrom.focus();
</script>
</body>
</html>
