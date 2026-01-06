<?php
include "../login.inc";
?>
<html>
 <head>
<?php
include "viewport.php";
include "db_access.php";
require_once "logme.php";
//include "checkdata.php";
require_once "checkdata.php";
//include "logme.php";
list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
$cookiedata = $_COOKIE["BDCSData"];
$cookiedata = getBDCScookie($Link, $tran_device, "transfer");
list($tran_type, $transaction_type, $location_from, $ssn_from, $product_from, $qty_from ) = explode("|", $cookiedata);
?>
  <title>Get Location you are taking From</title>
 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FFFFFF">

  <h3 ALIGN="LEFT">Enter Location From</h3>

 <FORM action="GetSSNNoLabelQtyTo.php" method="post" name=getlocn>
 <P>
<?php
if (isset($ssn_from)) 
{
	echo("SSN <INPUT type=\"text\" readonly name=\"ssn_from\" value=\"$ssn_from\" ><BR>");
}
echo("Location: <INPUT type=\"text\" name=\"location_from\"");
/*
if (isset($_POST['location_from'])) 
{
	echo(" value=\"".$_POST['location_from']."\"");
}
if (isset($_GET['location_from'])) 
{
	echo(" value=\"".$_GET['location_from']."\"");
}
*/
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
	//commit
	//$Link->commit();
	ibase_commit($dbTran);
	
	//close
	//$Link->disconnect();
	//ibase_close($Link);

?>
</P>
<script type="text/javascript">
document.getlocn.location_from.focus();
</script>
</body>
</html>
