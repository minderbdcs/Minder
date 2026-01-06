<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
include "../login.inc";
?>
<html  xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head><title>Select a Product to Allocate to a user</title>
<style type="text/css">
<!--
.form label {width:4em; float:left; display:block; margin-right:0.5em; text-align:right}
.form .submit input {margin-left:4.5em}
.form input {font-weight:bold}
.colours input {color:#781351; background:#fee3ad; border:1px solid #781351}
.colours .submit input {color:#000; background:#ffa20f; border:2px #d7b9c9 outset}
.form fieldset {border:1px solid #781351; width:22em}
.form legend {background:#ffa20c; border:1px solid #781351; letter-spacing:0}
.form fieldset p {margin-top:0}
label {width:7em; float:left; display:block; margin-right:0.5em; text-align:right}
.submit input {margin-left:4.5em}
//-->
</style></head><body id="reso">

<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include("transaction.php");
include "logme.php";

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
	
logme($Link, $tran_user, $tran_device, "start of getprod prepare");
$wk_order = "";
if (isset($_POST['order']))
{
	$wk_order = $_POST['order'];
}
if (isset($_GET['order']))
{
	$wk_order = $_GET['order'];
}
if (isset($_POST['pickuser']))
{
	$pickuser = $_POST['pickuser'];
}
if (isset($_GET['pickuser']))
{
	$pickuser = $_GET['pickuser'];
}
if (!isset($pickuser))
{
	$pickuser = $tran_user;
}
if (isset($_POST['allocatedevice']))
{
	$allocatedevice = $_POST['allocatedevice'];
}
if (isset($_GET['allocatedevice']))
{
	$allocatedevice = $_GET['allocatedevice'];
}
if (!isset($allocatedevice))
{
	$allocatedevice = $tran_device;
}

$wk_pick_method = "PO2";

echo("<FONT size=\"2\">\n");

$Query = "select distinct pick_order "; 
	$Query .= "from pick_item ";
	$Query .= "where pick_line_status in ('AL','PG','PL') ";
	$Query .= "and device_id = '$tran_device' ";
	$Query .= "order by pick_order ";
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Pick_Item!<BR>\n");
	exit();
}

$got_items = 0;
$wk_orders = "";
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	$got_items = 1;
	$wk_orders .= $Row[0] . " ";
}
//release memory
ibase_free_result($Result);

if ($wk_orders <> "")
{
	echo ("<B><FONT COLOR=RED>You Already Have Order </FONT>");
	echo ("<FONT COLOR=BLUE>$wk_orders</FONT></B><BR>\n");
}

$got_items = 0;
$wk_from_device = "";

// check whether only can see a limited no of cmps and cntrys
$Query = "select first 1 1 "; 
	$Query .= "from options where  options.group_code = 'CMPPKPO2' and options.description = 'T' ";
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Company!<BR>\n");
	exit();
}

$wk_all_cmp_cntry = "T";
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	$wk_all_cmp_cntry = "F";
}
//release memory
ibase_free_result($Result);

// ok now have the company and from device 
// the to device is $tran_device
if ($wk_order <> "" and $wk_orders == "")
{
	$Query = "select options.description "; 
	$Query .= "from pick_order ";
	$Query .= "join options on options.group_code = 'CMPPKPRT' and options.code = (pick_order.company_id || '|' || pick_order.p_country) ";
	$Query .= "where pick_order.pick_order = '$wk_order' ";
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Pick_Item!<BR>\n");
		exit();
	}

	$wk_printer = "";
	// Fetch the results from the database.
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_printer = $Row[0] ;
	}
	//release memory
	ibase_free_result($Result);

	logme($Link, $tran_user, $tran_device, "start PKAL");
	$transaction_type = "PKAL";
	$my_object = $wk_order;
	$my_source = 'SSSSSSSSS';
	$tran_tranclass = "J";
	$tran_qty = 0;
	//$my_sublocn = $tran_device;
	$my_sublocn = $allocatedevice;
	$location = $tran_device . 'T|' . $wk_printer;
	//$my_ref = "transfer pick of order to handheld" ;
	//$my_ref = $tran_user ;
	$my_ref = $pickuser ;

	$my_message = "";
	$my_message = dotransaction_response($transaction_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
	logme($Link, $tran_user, $tran_device, "end PKAL");
	if ($my_message > "")
	{
		list($my_mess_field, $my_mess_label) = explode("=", $my_message);
		$my_responsemessage = urldecode($my_mess_label) . " ";
	}
	else
	{
		$my_responsemessage = " ";
	}
	if (($my_responsemessage == " ") or
	    ($my_responsemessage == ""))
	{
		$my_responsemessage = "Processed successfully ";
	}

	logme($Link, $tran_user, $tran_device, "end screen prepare1");
	//commit
	ibase_commit($dbTran);
	
	//close
	//ibase_close($Link);
}
else
{
	// no order yet
	echo("<FORM action=\"" .  basename($_SERVER["PHP_SELF"])  . "\" method=\"post\" name=all>\n");
	if (isset($wk_message))
	{
		//$message = $_GET['message'];
		echo ("<B><FONT COLOR=RED>$wk_message</FONT></B>\n");
	}
	// do inputs
	//echo ("<TABLE>\n");
	//echo("<TR><TD>");
	//echo("Order</TD><TD><INPUT type=\"text\" name=\"order\" size=\"15\" ></TD></TR>");
	//$Query = "SELECT DISTINCT P1.PROD_ID FROM PICK_ITEM P1 JOIN PICK_ORDER P2 ON P2.PICK_ORDER= P1.PICK_ORDER WHERE P1.PICK_LINE_STATUS IN ('OP','UP') AND P2.PICK_STATUS IN ('OP','DA') ORDER BY ZEROTIME(P1.CREATE_DATE),P1.PICK_LINE_PRIORITY "; 
	$Query = "SELECT DISTINCT P1.PROD_ID FROM PICK_ITEM P1 JOIN PICK_ORDER P2 ON P2.PICK_ORDER= P1.PICK_ORDER WHERE P1.PICK_LINE_STATUS IN ('OP','UP') AND P2.PICK_STATUS IN ('OP','DA')  "; 
	//echo($Query);
	//$Query .= " UNION SELECT DISTINCT S1.PROD_ID FROM PICK_ITEM P3 JOIN PICK_ORDER P4 ON P4.PICK_ORDER= P3.PICK_ORDER JOIN ISSN S1 ON S1.SSN_ID = P3.SSN_ID WHERE P3.PICK_LINE_STATUS IN ('OP','UP') AND P4.PICK_STATUS IN ('OP','DA') ORDER BY ZEROTIME(P3.CREATE_DATE),P3.PICK_LINE_PRIORITY "; 
	$Query  = $Query . " UNION SELECT  DISTINCT S1.PROD_ID FROM PICK_ITEM P3 JOIN PICK_ORDER P4 ON P4.PICK_ORDER= P3.PICK_ORDER JOIN ISSN S1 ON S1.SSN_ID = P3.SSN_ID WHERE P3.PICK_LINE_STATUS IN ('OP','UP') AND P4.PICK_STATUS IN ('OP','DA')  "; 
	//echo($Query);
	//echo("Product:</TD><TD><SELECT name=\"order\" class=\"sel3\">\n");
	echo("<p><label for=\"order\">Product:</label><SELECT id=\"order\" name=\"order\" class=\"sel3\">\n");
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Orders!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_order2 = $Row[0];
		$wk_include_me = "N";
		if ($wk_all_cmp_cntry == "T")
		{
			$wk_include_me = "T";
		}
		else
		{
			$Query3 = "SELECT O1.DESCRIPTION FROM PICK_ORDER P2 JOIN OPTIONS O1 ON O1.GROUP_CODE = 'CMPPKPO2' AND O1.CODE = (P2.COMPANY_ID || '|' || P2.P_COUNTRY) WHERE P2.PICK_ORDER = '" . $wk_order2 . "' "; 
			//echo($Query3);
			if (!($Result3 = ibase_query($Link, $Query3)))
			{
				echo("Unable to Read Returner!<BR>\n");
				exit();
			}
			while ( ($Row3 = ibase_fetch_row($Result3)) ) {
				$wk_include_me = $Row3[0];
			}
			//release memory
			ibase_free_result($Result3);
		}
		if ($wk_include_me == "T")
		{
			echo( "<OPTION value=\"$Row[0]\">$Row[0]");
			$Query2 = "select count(*) from pick_item  left outer join issn on issn.ssn_id=pick_item.ssn_id where (pick_item.prod_id='$wk_order2' or issn.prod_id = '$wk_order2') and pick_item.pick_line_status in ('OP','UP')  "; 
			//echo($Query);
			if (!($Result2 = ibase_query($Link, $Query2)))
			{
				echo("Unable to Read Lines!<BR>\n");
				exit();
			}
			while ( ($Row2 = ibase_fetch_row($Result2)) ) {
				echo( " $Row2[0]\n");
			}
			//release memory
			ibase_free_result($Result2);
		}
	}
	//release memory
	ibase_free_result($Result);
	logme($Link, $tran_user, $tran_device, "end screen prepare2");
	//echo("</SELECT></TD>\n");
	echo("</SELECT>\n");
	echo ("</p>\n");

	$Query = "select user_id from sys_user order by user_id";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Users!<BR>\n");
		exit();
	}
	//echo("Allocate to User:<SELECT name=\"pickuser\" >\n");
	echo("<p><label for=\"pickuser\">Allocate to User:</label><SELECT id=\"pickuser\" name=\"pickuser\" class=\"sel3\">\n");
	// Fetch the results from the database.
	while (($Row2 = ibase_fetch_row($Result))) {
		if ($Row2[0] == $pickuser)
		{
			echo("<OPTION value=\"" . $Row2[0] . "\" selected >$Row2[0]\n");
		}
		else
		{
			echo("<OPTION value=\"" . $Row2[0] . "\">$Row2[0]\n");
		}
	}
	echo("</SELECT>\n");
	echo ("</p>\n");
	//release memory
	ibase_free_result($Result);
	$Query = "select device_id from sys_equip where device_type = 'HH' order by device_id";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Users!<BR>\n");
		exit();
	}
	//echo("<BR>Allocate to Device:<SELECT name=\"allocatedevice\" >\n");
	echo("<p><label for=\"allocatedevice\">Allocate to Device:</label><SELECT id=\"allocatedevice\" name=\"allocatedevice\" class=\"sel3\">\n");
	// Fetch the results from the database.
	while (($Row2 = ibase_fetch_row($Result))) {
		if ($Row2[0] == $allocatedevice)
		{
			echo("<OPTION value=\"" . $Row2[0] . "\" selected >$Row2[0]\n");
		}
		else
		{
			echo("<OPTION value=\"" . $Row2[0] . "\">$Row2[0]\n");
		}
	}
	echo("</SELECT>\n");
	echo ("</p>\n");

	//echo ("<TR>\n");
	{
		//echo("<TH>Enter Product</TH>\n");
		echo("<p>Select Product</p>\n");
	}
	//echo ("</TR>\n");
	//echo ("</TABLE>\n");
}
echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	echo ("<TR>");
	echo ("<TD>");
if ($wk_order <> "")
{
	echo("<FORM action=\"getfromlocn.php\" method=\"post\" name=all>\n");
	whm2buttons('ContinuePick', 'pick_Menu.php', "Y","Back_50x100.gif","Back","continue_picks.gif");
}
else
{
	whm2buttons('GetOrder', 'pick_Menu.php', "Y","Back_50x100.gif","Back","nextorder.gif");
}
echo "<script>\n";
if ($wk_order <> "" and $wk_orders == "")
{
	echo "document.forms.all.submit();\n";
}
else
{
	echo "document.all.order.focus();\n";
}
echo "</script>\n";
?>
</body>
</html>
