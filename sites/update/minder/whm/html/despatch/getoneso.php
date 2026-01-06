<?php
include "../login.inc";
?>
<html>
<head>
<?php
 include "viewport.php";
?>
<title>Get One Sales Order</title>
</head>
<body>
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

if (isset($_POST['order'])) 
{
	$wk_order_no = $_POST["order"];
}
if (isset($_GET['order'])) 
{
	$wk_order_no = $_GET["order"];
}
include "checkdata.php";
if (isset($wk_order_no))
{
	$field_type = checkForTypein($wk_order_no, 'SALESORDER' ); 
	if ($field_type == "none")
	{
		// not a sales order
		$order_data = $wk_order_no;
	}
	else
	{
		$order_data = substr($wk_order_no, $startposn);
	}
	$wk_order_no = $order_data;
}

if (isset($wk_order_no))
{
	$wk_order_exists = "";
	// check that the order exists and the supplier list
	$Query4 = "SELECT pick_order  FROM pick_order where pick_order = '" . $wk_order_no . "'";
	if (!($Result4 = ibase_query($Link, $Query4)))
	{
		echo("Unable to Read Order!<BR>\n");
		exit();
	}
	if ( ($Row4 = ibase_fetch_row($Result4)) ) 
	{
		$wk_order_exists = $Row4[0];
	}
	//release memory
	ibase_free_result($Result4);
}
if (isset($wk_order_no))
{
	if ($wk_order_exists == "")
	{
		unset($wk_order_no);
  		echo("<h3 ALIGN=\"LEFT\">Order Dosn't Exist</h3>");
	}
}
echo("<FONT size=\"2\">\n");
if (isset($wk_order_no))
{
	echo("<FORM action=\"getcarrier.php\" method=\"post\" name=getorder >");
}
else
{
	echo("<FORM action=\"getoneso.php\" method=\"post\" name=getorder >");
}

//echo("order: <INPUT type=\"text\" name=\"order\" size=\"10\">");
echo("Order: <INPUT type=\"text\" name=\"order\" size=\"10\" ");
if (isset($wk_order_no))
{
	echo("value=\"" . $wk_order_no . "\"");
}
echo(" >");
echo ("<TABLE>\n");
echo ("<TR>\n");
echo("<TH>Scan Order</TH>\n");
echo ("</TR>\n");
echo ("</TABLE>\n");

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
	echo("<FORM action=\"./despatch_menu.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
*/
{
	// html 4.0 browser
	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\"");
	//whm2buttons("Accept","./despatch_menu.php");
	whm2buttons('Accept',"./despatch_menu.php" ,"Y" ,"Back_50x100.gif" ,"Back" ,"accept.gif");
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"order.href='./despatch_menu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
?>
<?php
{

	echo("<script type=\"text/javascript\">\n");
	if (isset($wk_order_no))
	{
		echo("document.getorder.submit();\n");
	}
	else
	{
		echo("document.getorder.order.focus();\n");
	}
	echo("</script>");
}
?>
</body>
</html>
