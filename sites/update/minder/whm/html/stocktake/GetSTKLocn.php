<?php
include "../login.inc";
setcookie("BDCSData","");
?>
<html>
 <head>
  <title>Get Location you are working in</title>
<?php
include "viewport.php";
require_once 'DB.php';
require 'db_access.php';
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
include "checkdatajs.php";
// create js for location check
whm2scanvars($Link, 'locn','LOCATION', 'LOCATION');
?>

<script type="text/javascript">
function processEdit() {
/* # check for valid location */
  var mytype;
  mytype = checkLocn(document.getlocn.location.value); 
  if (mytype == "none")
  {
	alert("Not a Location");
  	return false;
  }
  else
  {
	return true;
  }
}
</script>

 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FFFFFF">

  <h3 ALIGN="LEFT">Enter Location</h3>

 <TABLE BORDER="0" >
 <FORM action="GetSTKSSN.php" method="post" name=getlocn ONSUBMIT="return processEdit();">
 <P>
<?php
include "2buttons.php";
$reference="Description";
echo("<INPUT type=\"hidden\" name=\"reference\"");
echo(" value=\"".$reference."\">");
echo("<INPUT type=\"hidden\" name=\"doaulo\"");
echo(" value=\"Y\">");
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
echo(" maxlength=\"10\"");
echo(" ONBLUR=\"return processEdit();\"");
echo(" ><BR>\n");
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
	whm2buttons('Send',"../mainmenu.php", "Y","Back_50x100.gif","Back","accept.gif");
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
document.getlocn.location.focus();
</script>
</body>
</html>
