<?php
include "../login.inc";
setcookie("BDCSData","");
?>
<html>
 <head>
  <title>Get SSN you are working on</title>
<?php
include "viewport.php";
?>
 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FFFFFF">
  <h4 ALIGN="LEFT">Enter SSN</h4>

 <TABLE BORDER="0" >
<?php
	require_once('DB.php');
	require('db_access.php');
include "2buttons.php";
	if (isset($_GET['message']))
	{
		$message = $_GET['message'];
		if ($message == 'connect')
		{
			echo ("<B><FONT COLOR=RED>Can't Connect to DATABASE!</FONT></B>\n");
		}
		else if ($message == 'query')
		{
			echo ("<B><FONT COLOR=RED>Can't Query ISSN!</FONT></B>\n");
		}
		else if ($message == 'nossn')
		{
			echo ("<B><FONT COLOR=RED>SSN Not Found!</FONT></B>\n");
		}
		else
		{
			echo ("<B><FONT COLOR=RED>" . $_GET['message'] . "!</FONT></B>\n");
		}
	}

echo(" <FORM action=\"GetType.php\" method=\"post\" name=getssn>\n");
echo(" <P>\n");
echo("SSN:      <INPUT type=\"text\" name=\"ssn_id\"");
if (isset($_POST['ssn_id'])) 
{
	echo(" value=\"".$_POST['ssn_id']."\"");
}
if (isset($_GET['ssn_id'])) 
{
	echo(" value=\"".$_GET['ssn_id']."\"");
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
	//whm2buttons('Send');
	whm2buttons('Send',"../mainmenu.php" ,"Y" ,"Back_50x100.gif" ,"Back" ,"accept.gif");
/*
	echo("<BUTTON name=\"send\" value=\"Send!\" type=\"submit\">\n");
	echo("Send<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
	echo("</FORM>\n");
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='../mainmenu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
?>
</P>
<script type="text/javascript">
document.getssn.ssn_id.focus();
</script>
</body>
</html>
