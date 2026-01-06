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
echo("<FONT size=\"2\">\n");

if ($wk_order <> "")
{
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
	
	// ok now have the company and from device 
	// the to device is $tran_device
	// now need a procedure to calc the next prod and transfer just
	// that to the new device
	
	$wk_prod = "";
	$Query = "SELECT FIRST 1 P1.PROD_ID
	FROM PICK_ITEM P1
	WHERE P1.PICK_ORDER = '" . $wk_order . "'
	AND  P1.PICK_LINE_STATUS IN ('AL','PL','PG') 
	AND  P1.OVER_SIZED = 'T' "; 
	
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Picks!<BR>\n");
		exit();
	}
	
	// Fetch the results from the database.
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_prod = $Row[0];
	}
	//release memory
	ibase_free_result($Result);

	if ($wk_prod == "")
	{
		$wk_order = "";
		$wk_message = "No UGLY lines to Pick";
	}
}

if ($wk_order <> "")
{
	$transaction_type = "TRPK";
	$my_object = $wk_order;
	$my_source = 'SSSSSSSSS';
	$tran_tranclass = "O";
	$tran_qty = 0;
	$my_sublocn = $tran_device;
	$location = $wk_from_device . 'T';
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
	echo("Order</TD><TD><INPUT type=\"text\" name=\"order\" size=\"15\" ></TD></TR>");


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
if ($wk_order <> "")
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
