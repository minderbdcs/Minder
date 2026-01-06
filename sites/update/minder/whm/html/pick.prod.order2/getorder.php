<?php
include "../login.inc";
?>
<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include("transaction.php");

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
	
$wk_order = "";
if (isset($_POST['order']))
{
	$wk_order = $_POST['order'];
}
if (isset($_GET['order']))
{
	$wk_order = $_GET['order'];
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

$Query = "select zone.default_device_id  "; 
$Query .= "from company ";
$Query .= "join options on options.group_code = 'CMPPKPROD' and options.code = company.company_id and options.description = 'O' ";
$Query .= "join zone on zone.company_id = company.company_id ";
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Company!<BR>\n");
	exit();
}
	
$got_items = 0;
$wk_from_device = "";
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	$got_items = 1;
	$wk_from_device = $Row[0];
}
//release memory
ibase_free_result($Result);

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

	$transaction_type = "TRPK";
	$my_object = $wk_order;
	$my_source = 'SSSSSSSSS';
	$tran_tranclass = "O";
	$tran_qty = 0;
	$my_sublocn = $tran_device;
	$location = $wk_from_device . 'T|' . $wk_printer;
	$my_ref = "transfer pick of order to handheld" ;

	$my_message = "";
	$my_message = dotransaction_response($transaction_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
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
	echo ("<TABLE>\n");
	echo("<TR><TD>");
	//echo("Order</TD><TD><INPUT type=\"text\" name=\"order\" size=\"15\" ></TD></TR>");
	echo("Order:</TD><TD><SELECT name=\"order\" class=\"sel3\">\n");
	{
		//$Query = "select distinct pick_order  from pick_item where pick_line_status in ('AL','PG','PL') and device_id='$wk_from_device' order by create_date "; 
		$Query = "select distinct pick_order  from pick_item where pick_line_status in ('AL','PG','PL') and device_id='$wk_from_device' order by zerotime(create_date),pick_line_priority "; 
	}
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Returner!<BR>\n");
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
			$Query2 = "select count(*) from pick_item where pick_order='$wk_order2' and pick_line_status in ('AL','PG','PL') and device_id='$wk_from_device'  "; 
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
	echo("</SELECT></TD>\n");

	echo ("<TR>\n");
	{
		echo("<TH>Enter Sales Order</TH>\n");
	}
	echo ("</TR>\n");
	echo ("</TABLE>\n");
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
</html>
