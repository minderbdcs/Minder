<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
include "../login.inc";
?>
<html>
 <head>
  <title>Get Location to find SSNs for</title>
<?php
include "viewport.php";
//<meta name="viewport" content="width=device-width">
?>
<link rel=stylesheet type="text/css" href="fromlocn.css">
<style type="text/css">
body {
     font-family: sans-serif;
     font-size: 13px;
     border: 0; padding: 0; margin: 0;
}
form, div {
    border: 0; padding:0 ; margin: 0;
}
table {
    border: 0; padding: 0; margin: 0;
}
</style>
<?php
require_once 'DB.php';
require 'db_access.php';
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	include 'logme.php';
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	
//include "checkdatajs.php";
// create js for location check
/*
whm2scanvars($Link, 'locn','LOCATION', 'LOCATION');
whm2scanvars($Link, 'ssn','BARCODE', 'SSN');
whm2scanvars($Link, 'altssn','ALTBARCODE', 'ALTSSN');
whm2scanvars($Link, 'prod13','PROD_13', 'PROD13');
whm2scanvars($Link, 'prodint','PROD_INTERNAL', 'PRODINTERNAL');
whm2scanvars($Link, 'altprodint','ALT_PROD_INTERNAL', 'ALTPRODINTERNAL');
whm2scanvars($Link, 'device','DEVICE','DEVICE');
*/
?>

<script type="text/javascript">
function processEdit() {
  var mytype;
/*
  mytype = checkSsn(document.getlocn.location.value); 
  if (mytype == "SSN")
  {
	return true;
  }
  alert('ssn:' + mytype);
  mytype = checkAltssn(document.getlocn.location.value); 
  if (mytype == "ALTSSN")
  {
	return true;
  }
  alert('altssn:' + mytype);
  mytype = checkLocn(document.getlocn.location.value); 
  if (mytype == "LOCATION")
  {
	return true;
  }
  alert('location:' + mytype);
  mytype = checkDevice(document.getlocn.location.value); 
  if (mytype == "DEVICE")
  {
	return true;
  }
  alert('device:' + mytype);
  mytype = checkProd13(document.getlocn.location.value); 
  if (mytype == "PROD13")
  {
	return true;
  }
  alert('prod13:' + mytype);
  mytype = checkProdint(document.getlocn.location.value); 
  if (mytype == "PRODINTERNAL")
  {
	return true;
  }
  alert('prodinternal:' + mytype);
  alert("Not an SSN, Location, Device or Product");
  return false;
*/
  return true;
}
</script>

 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FFFFFF">


<?php
	setBDCScookie($Link, $tran_device, "ssn_id", "");
	setBDCScookie($Link, $tran_device, "location", "");
 // <h2 ALIGN="LEFT">Enter Location , Product or SSN to View</h2>
 echo('<h4 ALIGN="LEFT">Enter Location , Product or SSN to View</h4>');
  if (isset($_POST['message']))
  {
	$wk_message = $_POST['message'];
  }
  if (isset($_GET['message']))
  {
	$wk_message = $_GET['message'];
  }
  if (isset($wk_message))
  {
		//$message = $_GET['message'];
		echo ("<B><FONT COLOR=RED>$wk_message</FONT></B>\n");
  }
 echo('<br><br>');
 //echo('<form action="ssn.php" method="post" name=getlocn ONSUBMIT="return processEdit();">');
 echo('<form action="ssn.php" method="post" name=getlocn >');
?>
 <P>
<?php
//Location: <input type="text" name="location" size="30" ONBLUR="return processEdit();" ><BR>
echo('Location: <input type="text" name="location" size="22" ><br>');
?>
 <table BORDER="0" ALIGN="LEFT">
<?php
include "2buttons.php";
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<input type=\"submit\" name=\"send\" value=\"Send!\">\n");
	echo("</FORM>\n");
	//echo("<FORM action=\"../query/query.php\" method=\"post\" name=goback>\n");
	echo("<FORM action=\"./query.php\" method=\"post\" name=goback>\n");
	echo("<input type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
*/
{
	// html 4.0 browser
	//whm2buttons('Send', '../query/query.php');
	whm2buttons('Send',"../query/query.php" ,"Y" ,"Back_50x100.gif" ,"Back" ,"send.gif");
/*
	echo("<BUTTON name=\"send\" value=\"Send!\" type=\"submit\">\n");
echo("Send<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
	echo("</FORM>\n");
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='../query/query.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
//release memory
//ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

?>
<script type="text/javascript">
document.getlocn.location.focus();
</script>
</body>
</html>
