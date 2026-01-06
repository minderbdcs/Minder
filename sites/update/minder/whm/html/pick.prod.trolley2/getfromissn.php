<?php
include "../login.inc";
?>
<html>
 <head>
  <title>Get SSN to Pick</title>
<?php
include "viewport.php";
if (strpos($_SERVER['HTTP_USER_AGENT'] , "NetFront2") === false)
{
	echo('<link rel=stylesheet type="text/css" href="product.css">');
}
else
{
	echo('<link rel=stylesheet type="text/css" href="product.css">');
}
?>
 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FFFFFF">
  <h4 ALIGN="LEFT">Pick by ISSN</h4>

<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
include "logme.php";
$ssn = '';
$label_no = '';
$order = '';
$prod_no = '';
$description = '';
$uom = '';
$order_qty = 0;
$picked_qty = 0;
$required_qty = 0;
{
	$wk_cookie = getBDCScookie($Link, $tran_device, "BDCSData" );
	if (sizeof($wk_cookie) > 1) {
		list($label_no, $order, $ssn, $prod_no, $uom, $description, $required_qty, $location, $dummy, $dummy2) = explode("|", $wk_cookie);
	}
}
	
if (isset($_POST['locnfound']))
{
	$location_found = $_POST['locnfound'];
}
if (isset($_GET['locnfound']))
{
	$location_found = $_GET['locnfound'];
	//echo("locn found ".$location_found);
}
if (isset($_POST['scannedssn']))
{
	$scannedssn = $_POST['scannedssn'];
}
if (isset($_GET['scannedssn']))
{
	$scannedssn = $_GET['scannedssn'];
}
if (isset($_POST['message']))
{
	$message = $_POST['message'];
}
if (isset($_GET['message']))
{
	$message = $_GET['message'];
}
// want ssn label desc
$Query = "select pick_import_ssn_status from control "; 
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Control<BR>\n");
	$allowed_status = ",,";
}
else
{
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$allowed_status = $Row[0];
	}
	else
	{
		$allowed_status = ",,";
	}
}
//release memory
ibase_free_result($Result);

$rcount = 0;

$got_ssn = 0;
echo("<br><br>");
//echo("<FONT size=\"2\">\n");
echo("<form action=\"checkfromissn.php\" method=\"post\" name=getssn>");
echo("<table BORDER=\"0\" ALIGN=\"LEFT\">");
echo("<tr><td>");
// echo headers
//release memory
//ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

//echo("SSN: <INPUT type=\"text\" name=\"scannedssn\" size=\"20\">");
echo("SSN: <INPUT type=\"text\" name=\"scannedssn\" size=\"20\" class=\"default\">");
echo ("<table>\n");
echo ("<tr>\n");
echo("<th>Scan SSN</th>\n");
echo ("</tr>\n");
echo ("</table>\n");
if (isset($message))
{
	echo ("<table>\n");
	echo ("<tr>\n");
	echo("<th><input type=\"text\" name=\"message\" size=\"40\" value=\"" . $scannedssn . " " . $message . "\"></th>\n");
	echo ("</tr>\n");
	echo ("</table>\n");
}
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	echo("<INPUT type=\"submit\" name=\"accept\" value=\"Accept\">\n");
}
else
{
	echo("<BUTTON name=\"accept\" value=\"Accept\" type=\"submit\">\n");
	echo("Accept<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
}
*/
//echo total
//echo("</form>\n");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<form action=\"getfromlocn.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</form>\n");
}
else
*/
{
	// html 4.0 browser
 	echo("<table BORDER=\"0\" ALIGN=\"LEFT\">");
	whm2buttons('Accept', 'pick_Menu.php');
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='getfromlocn.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
?>
<script type="text/javascript">
<?php
{
	if (isset($location_found))
	{
	}
	else
	{
		echo("document.getssn.scannedssn.focus();\n");
	}
	//echo("document.getssn.scannedssn.focus();\n");
}
?>
</script>
</body>
</html>
