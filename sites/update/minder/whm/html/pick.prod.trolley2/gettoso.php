<html>
<head>
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
	$Query = "select despatch_location, ssn_id, pick_label_no, pick_order "; 
	$Query .= "from pick_item  ";
	$Query .= " where device_id = '".$tran_device."'";
	$Query .= " and pick_line_status = 'PL'";
	$Query .= " and ssn_id > ''";
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

echo("</head>");
echo("<body>");
//echo("<FONT size=\"2\">\n");
echo("<FORM action=\"gettolocn.php\" method=\"post\" name=getso>\n");
// echo headers
echo ("<TABLE BORDER=\"1\">\n");
echo ("<TR>\n");
echo("<TH>Location</TH>\n");
echo("<TH>SSN/Product</TH>\n");
echo("<TH>Label No</TH>\n");
echo("<TH>Order</TH>\n");
echo ("</TR>\n");

$rcount = 0;
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	$got_items++;
	echo ("<TR>\n");
	echo("<TD>".$Row[0]."</TD>\n");
	{
		echo("<TD>".$Row[1]."</TD>\n");
	}
	echo("<TD>".$Row[2]."</TD>\n");
	echo("<TD>".$Row[3]."</TD>\n");
	echo ("</TR>\n");
}

//release memory
ibase_free_result($Result);

{
	$Query = "select despatch_location, prod_id "; 
	$Query .= "from pick_item  ";
	$Query .= " where device_id = '".$tran_device."'";
	$Query .= " and pick_line_status = 'PL'";
	$Query .= " and prod_id > ''";
	$Query .= " group by despatch_location, prod_id";
}
//echo($Query);

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!<BR>\n");
	exit();
}

// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	$got_items++;
	echo ("<TR>\n");
	echo("<TD>".$Row[0]."</TD>\n");
	{
		echo("<TD>".$Row[1]."</TD>\n");
	}
	echo ("</TR>\n");
}

//release memory
ibase_free_result($Result);

echo ("</TABLE>\n");
echo("<INPUT type=\"text\" name=\"order\" size=\"34\" ><BR>");
echo("<INPUT type=\"hidden\" name=\"ttype\" value=\"I\" >");

echo ("<TABLE>\n");
echo ("<TR>\n");
echo("<TH>Scan SSN, Product or Pick Label</TH>\n");
echo ("</TR>\n");
echo ("</TABLE>\n");

{
	$Query = "select first 1 1 "; 
	$Query .= "from pick_item  ";
	$Query .= " where device_id = '".$tran_device."'";
	$Query .= " and pick_line_status in ('AL', 'PG') ";
}
//echo($Query);

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!<BR>\n");
	exit();
}

$got_back_items = 0;
// Fetch the results from the database.
if ( ($Row = ibase_fetch_row($Result)) ) {
	$got_back_items++;
	//$got_back_items++;
}

//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

if ($got_back_items == 0)
{
	$back_screen = "pick_Menu.php";
}
else
{
	$back_screen = "getfromlocn.php";
}
{
	// html 4.0 browser
 	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	whm2buttons('Accept', $back_screen,"Y","Back_50x100.gif","Back","accept.gif");
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='gettomethod.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
?>
<script type="text/javascript">
<?php
{
	echo("document.getso.order.focus();\n");
}
?>
</script>
</body>
</html>
