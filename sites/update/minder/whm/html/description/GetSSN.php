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

  <h4 ALIGN="LEFT">Enter SSN</h4>

 <table BORDER="0" ALIGN="LEFT">
 <P>
<?php
require_once('DB.php');
require('db_access.php');
include "2buttons.php";
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

function checkauob()
{
	global $Link, $dbTran;
	$Query = "SELECT SEND_AUOB FROM CONTROL";
	//echo($Query); 
	$wk_auob = "";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read CONTROL!<BR>\n");
	}
	else
	if (($Row = ibase_fetch_row($Result)))
	{
		$wk_auob =  $Row[0];
		ibase_free_result($Result); 
		unset($Result); 
	}
	// null means yes
	if ($wk_auob == "")
	{
		$wk_auob = "T";
	}
	return $wk_auob;
}

list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
echo('<FORM action="GetSSN.php" method="post" name=getlocn>');
echo("Location: <INPUT type=\"text\" name=\"location\"");
if (isset($_POST['location'])) 
{
	echo(" value=\"".$_POST['location']."\"");
}
if (isset($_GET['location'])) 
{
	echo(" value=\"".$_GET['location']."\"");
}
echo(" size=\"10\"");
echo(" maxlength=\"10\"><BR>\n");
echo("<INPUT type=\"hidden\" name=\"reference\"");
if (isset($_POST['reference'])) 
{
	echo(" value=\"".$_POST['reference']."\"");
}
if (isset($_GET['reference'])) 
{
	echo(" value=\"".$_GET['reference']."\"");
}
echo(">");
echo("</FORM>");
echo('<FORM action="GetSlash.php" method="post" name=getssn>');
echo("\n");
echo("<INPUT type=\"hidden\" name=\"reference\"");
if (isset($_POST['reference'])) 
{
	echo(" value=\"".$_POST['reference']."\"");
}
if (isset($_GET['reference'])) 
{
	echo(" value=\"".$_GET['reference']."\"");
}
echo(">");
// if do auob then
if (checkauob() == "T") 
{
	echo("<INPUT type=\"hidden\" name=\"addssn\" value=\"Y\">");
}
//
echo("<INPUT type=\"hidden\" name=\"location\"");
if (isset($_POST['location'])) 
{
	echo(" value=\"".$_POST['location']."\"");
}
if (isset($_GET['location'])) 
{
	echo(" value=\"".$_GET['location']."\"");
}
echo(" ><BR>\n");
echo("SSN: <INPUT type=\"text\" name=\"ssn\"");
if (isset($_POST['ssn'])) 
{
	echo(" value=\"".$_POST['ssn']."\"");
}
if (isset($_GET['ssn'])) 
/*
{
	echo(" value=\"".$_GET['ssn']."\"");
}
*/
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
	whm2buttons('Send',"../mainmenu.php" ,"Y" ,"Back_50x100.gif" ,"Back" ,"send.gif");
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
document.getssn.ssn.focus();
</script>
</body>
</html>
