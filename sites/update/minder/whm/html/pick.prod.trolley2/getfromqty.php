<?php
include "../login.inc";
?>
<html>
<head>
<?php
include "viewport.php";
?>
<title>Pick From Qty</title>
<link rel=stylesheet type="text/css" href="fromqty.css">
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
</head>
<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbtran = ibase_trans(IBASE_DEFAULT, $Link);

include "logme.php";
if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
$ssn = '';
$label_no = '';
$order = '';
$prod_no = '';
$description = '';
$uom = '';
$order_qty = 0;
$picked_qty = 0;
$required_qty = 0;
$scannedssn = '';
/*
if (isset($_COOKIE['BDCSData']))
{
	//list($label_no, $order, $ssn, $prod_no, $uom, $description, $required_qty, $location, $scannedssn, $dummy2) = explode("|", $_COOKIE["BDCSData"]);
}
*/
{
	$wk_cookie = getBDCScookie($Link, $tran_device, "BDCSData" );
	list($label_no, $order, $ssn, $prod_no, $uom, $description, $required_qty, $location, $scannedssn, $dummy2) = explode("|", $wk_cookie . "|||||||||");
}
	
// want ssn label desc
if ($ssn <> "")
{
	$Query = "select s1.locn_id, s1.current_qty, s1.ssn_id "; 
	$Query .= "from issn s1 ";
	$Query .= " where s1.ssn_id = '".$scannedssn."'";
}
else
{
	$Query2 = "select pick_import_ssn_status from control "; 
	if (!($Result = ibase_query($Link, $Query2)))
	{
		//header("Location pick_Menu.php?message=Unable+to+Read+Control");
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

	$Query = "select s1.locn_id, sum(s1.current_qty), s1.prod_id "; 
	$Query .= "from issn s1 ";
	$Query .= " where s1.prod_id = '".$prod_no."'";
	$Query .= " and s1.wh_id = '" . substr($location,0,2) . "' and s1.locn_id = '" . substr($location, 2,strlen($location) -2 ) . "'";
	$Query .= " and pos('" . $allowed_status . "',s1.issn_status,0,1) > -1";
	$Query .= " group by s1.prod_id, s1.locn_id ";
}
//echo($Query);
$rcount = 0;

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!<BR>\n");
	exit();
}

$got_ssn = 0;

echo("<body>\n");
//echo("<FONT size=\"2\">\n");
//echo("<form action=\"checkfromqty.php\" method=\"post\" name=getqty\n>");
//echo("Pick<INPUT type=\"text\" readonly name=\"label\" size=\"7\" value=\"$label_no\">");
//echo("SO<INPUT type=\"text\" readonly name=\"order\" size=\"15\" value=\"$order\"><BR>");
echo ("<div>\n");
echo("<table BORDER=\"0\" ALIGN=\"LEFT\">");
echo("<tr><td>");
echo("Pick</td><td><INPUT type=\"text\" readonly name=\"label\" size=\"7\" value=\"$label_no\"></td><td>");
echo("SO</td><td><INPUT type=\"text\" readonly name=\"order\" size=\"15\" value=\"$order\"></td></tr>");
//echo("</table>");
//echo("<table BORDER=\"0\" ALIGN=\"LEFT\">");
echo("<tr><td>");
if ($ssn <> '')
{
	//echo("SSN<INPUT type=\"text\" readonly name=\"ssn\" size=\"8\" value=\"$ssn\">");
	//echo("<INPUT type=\"text\" readonly name=\"uom\" size=\"8\" value=\"$uom\">");
	//echo("<INPUT type=\"text\" readonly name=\"desc\" size=\"20\" value=\"$description\">");
	echo("SSN</td><td colspan=\"3\"><INPUT type=\"text\" readonly name=\"ssn\" size=\"8\" value=\"$ssn\"></td><td>");
	echo("<INPUT type=\"text\" readonly name=\"uom\" size=\"2\" value=\"$uom\"></td></tr>");
	//echo("</table>");
	//echo ("<table BORDER=\"0\" ALIGN=\"LEFT\">\n");
	echo("<tr><td colspan=\"4\">");
	echo("<INPUT type=\"text\" readonly name=\"desc\" size=\"20\" value=\"$description\">");
	echo("</td></tr></table>");
}
else
{
	//echo("Part: <INPUT type=\"text\" readonly name=\"prod\" size=\"30\" value=\"$prod_no\" >");
	//echo("<INPUT type=\"text\" readonly name=\"uom\" size=\"8\" value=\"$uom\" >");
	//echo("<INPUT type=\"text\" readonly name=\"desc\" size=\"20\" value=\"$description\" >");
	echo("Part</td><td colspan=\"3\"><INPUT type=\"text\" readonly name=\"prod\" size=\"30\" value=\"$prod_no\"></td><td>");
	echo("<INPUT type=\"text\" readonly name=\"uom\" size=\"2\" value=\"$uom\"></td></tr>");
	//echo("</table>");
	//echo ("<table BORDER=\"0\" ALIGN=\"LEFT\">\n");
	echo("<tr><td colspan=\"4\">");
	echo("<INPUT type=\"text\" readonly name=\"desc\" size=\"20\" value=\"$description\" >");
	echo("</td></tr></table>");
}
echo ("</div>\n");
echo ("<div id=\"locns2\">\n");
// echo headers
echo ("<table BORDER=\"1\">\n");
echo ("<tr>\n");
echo("<th>Location</th>\n");
echo("<th>Qty Available</th>\n");
if ($ssn <> "")
{
	echo("<th>SSN</th>\n");
}
else
{
	echo("<th>Product</th>\n");
}
echo ("</tr>\n");

$rcount = 0;
$qty_available = 0;
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($got_ssn == 0) {
		// echo headers
		$got_ssn = 1;
	}
	echo ("<tr>\n");
	echo("<td>".$Row[0]."</td>\n");
	echo("<td>".$Row[1]."</td>\n");
	$qty_available += $Row[1];
	echo("<td>".$Row[2]."</td>\n");
	echo ("</tr>\n");
}

echo ("</table>\n");
echo ("</div>\n");
echo ("</div>\n");
echo ("<div ID=\"col1\">\n");
//echo("Qty Reqd <INPUT type=\"text\" readonly name=\"required_qty\" size=\"4\" value=\"$required_qty\" >");
echo("<label for=\"total_required_qty\">Total Qty Reqd</label><input type=\"text\"  id=\"total_required_qty\" name=\"total_required_qty\" size=\"4\" readonly value=\"" . $required_qty . "\"  class=\"locationform\" ><br><br>");
//echo("<label for=\"required_qty\">Qty Reqd</label><input type=\"text\"  id=\"required_qty\" name=\"required_qty\" size=\"4\" readonly value=\"" . min($required_qty, $qty_available) . "\"  class=\"locationform\" >");
//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbtran);

//close
//ibase_close($Link);

echo("<form action=\"checkfromqty.php\" method=\"post\" name=getqty\n>");
//echo("Picked: <INPUT type=\"text\" name=\"qtypicked\" size=\"4\">");
//echo("<label for=\"qtypicked\">Picked:</label><input type=\"text\"  id=\"qtypicked\" name=\"qtypicked\" size=\"4\"  value=\"$required_qty\"  class=\"locationform\" >");
echo("<label for=\"qtypicked\">Picked:</label><input type=\"text\"  id=\"qtypicked\" name=\"qtypicked\" size=\"4\"  value=\"" . min($required_qty, $qty_available) . "\"  class=\"locationform\" >");
echo ("<table>\n");
echo ("<tr>\n");
if ($ssn <> "")
{
	echo("<th>SSN=$scannedssn:Enter Qty</th>\n");
}
else
{
	echo("<th>Enter Qty</th>\n");
}
echo ("</tr>\n");
echo ("</table>\n");
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
	echo("<form action=\"getfromssn.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</form>\n");
}
else
*/
{
	// html 4.0 browser
 	echo("<table BORDER=\"0\" ALIGN=\"LEFT\">");
	if ($ssn <> "")
	{
		whm2buttons('Accept', 'getfromssn.php',"Y","Back_50x100.gif","Back","accept.gif");
	}
	else
	{
		whm2buttons('Accept', 'getfromlocn.php', "Y","Back_50x100.gif","Back","accept.gif");
	}
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='getfromssn.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
echo ("</div>\n");
?>
<script type="text/javascript">
<?php
{
	echo("document.getqty.qtypicked.focus();\n");
}
?>
</script>
</body>
</html>
