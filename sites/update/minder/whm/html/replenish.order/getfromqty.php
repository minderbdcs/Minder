<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Replenish Get from Qty</title>
<link rel=stylesheet type="text/css" href="product.css">
<?php
include "viewport.php";
?>
<script type="text/javascript">
function processEdit() {
  if ( document.getqty.scannedprod.value=="")
  {
	document.getqty.scannedprod.value="submitted";
  	return true;
  }
  else
  {
  	alert("Already Submitted");
  	return false;
  }
}
</script>
 </head>
<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include "logme.php";

/*
06/02/07 only allow submit once
was doing
getfromqty -> (submit)
	checkfromqty  -> via header
	transactionOL.php -> via header to next screen
	I have combined these 2 into 1 page
*/
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
$ssn = '';
$label_no = '';
$order = '';
$prod_no = '';
$company_id = '';
$description = '';
$uom = '';
$order_qty = 0;
$picked_qty = 0;
$required_qty = 0;
$scannedssn = '';
$wk_to_wh_id = '';
//if (isset($_COOKIE['BDCSData']))
{
	//list($label_no, $order, $ssn, $prod_no, $uom, $description, $required_qty, $location, $scannedssn, $dummy2) = explode("|", $_COOKIE["BDCSData"]);
}
$wk_cookie = getBDCScookie($Link, $tran_device, "BDCSData" );
list($label_no, $order, $ssn, $prod_no, $uom, $description, $required_qty, $location, $scannedssn, $dummy2) = explode("|", $wk_cookie);
// get company zone and to wh_id	
$Query1 = "select company_id, to_wh_id from transfer_request where trn_line_no = '" . $label_no . "'"; 
if (!($Result = ibase_query($Link, $Query1)))
{
	// error in sql
	echo("Unable to Read Transfer Requests!<BR>\n");
}
else
{
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$company_id = $Row[0];
		$wk_to_wh_id  = $Row[1];
	}
}
//release memory
ibase_free_result($Result);

// want ssn label desc
if ($ssn == "")
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
	$Query .= " and   s1.company_id  = '".$company_id."'";
	$Query .= " and s1.wh_id = '" . substr($location,0,2) . "' and s1.locn_id = '" . substr($location, 2,strlen($location) -2 ) . "'";
	$Query .= " and pos('" . $allowed_status . "',s1.issn_status,0,1) > -1";
	$Query .= " group by s1.locn_id, s1.prod_id ";
}
//echo($Query);
$rcount = 0;

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read ISSNs Qtys!<BR>\n");
	exit();
}

$got_ssn = 0;

echo("<body bgcolor=\"#FFFFF0\">\n");
echo("<h2>Replenish - Product Qty</h2>\n");
//echo("<FONT size=\"2\">\n");
//echo("<FORM action=\"checkfromqty.php\" method=\"post\" name=getqty\n>");
//echo("Pick<INPUT type=\"text\" readonly name=\"label\" size=\"7\" value=\"$label_no\">");
//echo("SO<INPUT type=\"text\" readonly name=\"order\" size=\"15\" value=\"$order\"><BR>");
echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
echo("<TR><TD>");
echo("Line</TD><TD><INPUT type=\"text\" readonly name=\"label\" size=\"4\" value=\"$label_no\"></TD>");
//echo("</TR></TABLE><BR><BR>");
//echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
//echo("<TR><TD>");
echo("<TD>");
if ($ssn == '')
{
	//echo("Part: <INPUT type=\"text\" readonly name=\"prod\" size=\"30\" value=\"$prod_no\" >");
	//echo("<INPUT type=\"text\" readonly name=\"uom\" size=\"8\" value=\"$uom\" >");
	//echo("<INPUT type=\"text\" readonly name=\"desc\" size=\"20\" value=\"$description\" >");
	echo("Prod</TD><TD><INPUT type=\"text\" readonly name=\"prod\" size=\"30\" value=\"$prod_no\"></TD><TD>");
	//echo("<INPUT type=\"text\" readonly name=\"uom\" size=\"8\" value=\"$uom\"></TD></TR></TABLE><BR><BR>");
	echo("<INPUT type=\"text\" readonly name=\"uom\" size=\"8\" value=\"$uom\"></TD></TR>");
	//echo ("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
	echo("<TR><TD colspan=\"5\">");
	echo("<INPUT type=\"text\" readonly name=\"desc\" size=\"60\" value=\"$description\" >");
	//echo("</TD></TR></TABLE><BR><BR><BR>");
	echo("</TD></TR>");
}
echo ("<TR><TD colspan=\"5\">\n");
// echo headers
echo ("<TABLE BORDER=\"1\" align=\"bottom\">\n");
echo ("<TR>\n");
echo("<TH>Location</TH>\n");
echo("<TH>Qty Available</TH>\n");
if ($ssn == "")
{
	echo("<TH>Product</TH>\n");
}
echo ("</TR>\n");

$rcount = 0;
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($got_ssn == 0) {
		// echo headers
		$got_ssn = 1;
	}
	echo ("<TR>\n");
	echo("<TD>".$Row[0]."</TD>\n");
	echo("<TD>".$Row[1]."</TD>\n");
	echo("<TD>".$Row[2]."</TD>\n");
	echo ("</TR>\n");
}

echo ("</TABLE>\n");
echo ("</TD></TR>\n");
echo ("<TR><TD colspan=\"5\">\n");
echo ("<TABLE BORDER=\"0\" align=\"bottom\">\n");
echo ("<TR><TD>\n");
echo("Qty Reqd</TD><TD><INPUT type=\"text\" readonly name=\"required_qty\" size=\"4\" value=\"$required_qty\" ></TD>");
//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

echo ("</TD></TR>\n");
echo ("<TR><TD>\n");
echo("<FORM action=\"checkfromqty.php\" method=\"post\" name=\"getqty\" onsubmit=\"return processEdit();\" \n>");
echo("<INPUT type=\"hidden\" name=\"scannedprod\" value=\"\">");
echo("Qty Taken:</TD><TD><INPUT type=\"text\" name=\"qtypicked\" size=\"4\">");
echo ("</TD></TR>\n");
echo ("</TABLE>\n");
echo ("</TD></TR>\n");
echo ("<TR><TD colpan=\"5\">\n");
echo ("<TABLE>\n");
echo ("<TR>\n");
if ($ssn == "")
{
	echo("<TH>Enter Qty</TH>\n");
}
echo ("</TR>\n");
echo ("</TABLE>\n");
echo ("</TD></TR>\n");
echo ("<TR><TD colspan=\"5\">\n");
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
//echo("</FORM>\n");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<FORM action=\"getfromssn.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
*/
{
	// html 4.0 browser
 	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	if ($ssn == "")
	{
		whm2buttons('Accept', 'getfromlocn.php',"Y","Back_50x100.gif","Back","accept.gif");
	}
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='getfromssn.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
echo ("</TD></TR>\n");
echo ("</TABLE>\n");
}
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
