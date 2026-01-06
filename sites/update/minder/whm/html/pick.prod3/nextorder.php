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
	
echo("<FONT size=\"2\">\n");

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

$Query = "SELECT FIRST 1 P1.PICK_ORDER
FROM PICK_ITEM P1
WHERE P1.PICK_LINE_STATUS IN ('AL') 
AND  P1.DEVICE_ID = '" . $wk_from_device . "'
ORDER BY P1.PICK_LINE_PRIORITY"; 
//AND  P1.PICK_LINE_PRIORITY > 0

//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!<BR>\n");
	exit();
}

$wk_order = "";
// Fetch the results from the database.
if ( ($Row = ibase_fetch_row($Result)) ) {
	$wk_order = $Row[0];
}
//release memory
ibase_free_result($Result);

echo "Next Order [" . $wk_order . "]";


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


echo ("<TABLE>\n");
echo ("<TR>\n");
//if ($got_orders == 1)
/*
else
{
	echo("<TH>Select Sales Order</TH>\n");
}
*/
echo ("</TR>\n");
echo ("</TABLE>\n");
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
echo ("</TR>");
echo ("</TABLE>");
?>
<script>
document.forms.all.submit();
</script>
</html>
