<?php
include "../login.inc";
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
	$Query = "select first 2 despatch_location, ssn_id, prod_id, pick_label_no, pick_order "; 
	$Query .= "from pick_item  ";
	$Query .= " where device_id = '".$tran_device."'";
	$Query .= " and pick_line_status = 'PL'";
	$Query .= " order by pick_order";
}
//echo($Query);
$rcount = 0;

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!<BR>\n");
	exit();
}

$got_items = 0;

//echo("<FONT size=\"2\">\n");
echo("<FORM action=\"gettolocn.php\" method=\"post\" name=getso>\n");
// echo headers
echo ("<TABLE BORDER=\"1\">\n");
echo ("<TR>\n");
echo("<TH>Location</TH>\n");
echo("<TH>SSN</TH>\n");
echo("<TH>Label No</TH>\n");
echo("<TH>Order</TH>\n");
echo ("</TR>\n");

$rcount = 0;
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	$got_items++;
	echo ("<TR>\n");
	echo("<TD>".$Row[0]."</TD>\n");
	if ($Row[1] == "")
	{
		echo("<TD>".$Row[2]."</TD>\n");
	}
	else
	{
		echo("<TD>".$Row[1]."</TD>\n");
	}
	echo("<TD>".$Row[3]."</TD>\n");
	echo("<TD>".$Row[4]."</TD>\n");
	echo ("</TR>\n");
}

echo ("</TABLE>\n");
echo("<INPUT type=\"text\" name=\"order\" size=\"20\" ><BR>");
echo("<INPUT type=\"hidden\" name=\"type\" value=\"I\" >");
//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

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
//echo("</FORM>\n");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<FORM action=\"gettomethod.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
*/
{
	// html 4.0 browser
 	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	whm2buttons('Accept', 'gettomethod.php');
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='gettomethod.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
echo ("<TABLE>\n");
echo ("<TR>\n");
echo("<TH>Scan SSN, Product or Pick Label</TH>\n");
echo ("</TR>\n");
echo ("</TABLE>\n");
?>
<SCRIPT>
<?php
{
	echo("document.getso.order.focus();\n");
}
?>
</SCRIPT>
</HTML>
