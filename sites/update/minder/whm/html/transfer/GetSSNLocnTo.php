<?php
include "../login.inc";
?>
<html>
 <head>
  <title>Get Location you are taking to</title>
<?php
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
  mytype = checkLocn(document.getlocn.locationto.value); 
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

  <H3 ALIGN="LEFT">Enter Location TO</H3>

 <FORM action="PostTo.php" method="post" name=getlocn ONSUBMIT="return processEdit();">
 <P>
<?php
echo("<INPUT type=\"hidden\" name=\"transaction2_type\" value=\"TRIL\">");
echo("Location: <INPUT type=\"text\" name=\"locationto\"");
if (isset($_POST['locationto'])) 
{
	echo(" value=\"".$_POST['locationto']."\"");
}
if (isset($_GET['locationto'])) 
{
	echo(" value=\"".$_GET['locationto']."\"");
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
document.getlocn.locationto.focus();
</script>
</body>
</html>
