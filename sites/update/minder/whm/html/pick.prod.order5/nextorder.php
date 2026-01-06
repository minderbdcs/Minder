<?php
include "../login.inc";
?>
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
	
logme($Link, $tran_user, $tran_device, "start nextorder");
echo("<FONT size=\"2\">\n");

$wk_pick_method = "PO2";

$Query = "select first 1 pick_order "; 
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
// now need a procedure to calc the next prod and transfer just
// that to the new device

//if ($wk_all_cmp_cntry == "T")
{
	//$Query = "SELECT FIRST 1 P1.PICK_ORDER
	$Query = "SELECT DISTINCT P1.PICK_ORDER
FROM PICK_ITEM P1
JOIN PICK_ORDER P2 ON P2.PICK_ORDER= P1.PICK_ORDER
WHERE P1.PICK_LINE_STATUS IN ('OP','UP') 
AND   P2.PICK_STATUS IN ('OP','DA') 
ORDER BY ZEROTIME(P1.CREATE_DATE),P1.PICK_LINE_PRIORITY "; 
}
//ORDER BY P1.CREATE_DATE  
//AND  P1.PICK_LINE_PRIORITY > 0

//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!<BR>\n");
	exit();
}

$wk_order = "";
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) and $wk_order == "" ) {
	//$wk_order = $Row[0];
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
		$wk_order = $wk_order2;
	}
	/*
	else
	{
		// go to get the next order
	}
	*/
}
//release memory
ibase_free_result($Result);

echo "Next Order [" . $wk_order . "]";


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

	logme($Link, $tran_user, $tran_device, "before start of PKAL");
	$transaction_type = "PKAL";
	$my_object = $wk_order;
	$my_source = 'SSSSSSSSS';
	$tran_tranclass = "I";
	$tran_qty = 0;
	$my_ref = $tran_user;
	$location = $tran_device . 'T' . '|' . $wk_printer;
	$my_sublocn = "";
	//$my_ref = "transfer pick of order to handheld" ;

	$my_message = "";
	$my_message = dotransaction_response($transaction_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
	logme($Link, $tran_user, $tran_device, "after end of PKAL");
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

	logme($Link, $tran_user, $tran_device, "end of prepare nextorder");
	//commit
	ibase_commit($dbTran);
	
	//close
	//ibase_close($Link);
}


echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	echo ("<TR>");
	echo ("<TD>");
	echo("<FORM action=\"getfromlocn.php\" method=\"post\" name=all>\n");
	//echo("<FORM action=\"transactionUA.php\" method=\"post\" name=all>\n");
	echo("<INPUT type=\"IMAGE\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=Confirm+Despatch&fromimage=');
	echo('Blank_Button_50x100.gif" alt="Despatch"></INPUT>');
*/
	echo('SRC="/icons/whm/continue_picks.gif" alt="ContinuePick"></INPUT>');
	echo("</FORM>");
	echo ("<TD>");
echo ("</TR>");
echo ("</TABLE>");
if ($wk_orders == "")
{
	echo ("<script>\n");
	echo ("document.forms.all.submit();");
	echo ("</script>\n");
}
?>
</html>
