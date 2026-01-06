<?php
include "../login.inc";
?>
<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include("transaction.php");
include("logme.php");

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
	$Query .= "join options on options.group_code = 'CMPPKPROD' and options.code = company.company_id and options.description = 'P' ";
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
echo "Device From [" . $wk_from_device . "]";
//release memory
ibase_free_result($Result);

// now get the pick sequence to use
/*
$Query = "select description from session where device_id='" . $DBDevice . "' and code = 'CURRENT_PICK_SEQ' "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Sessions!<BR>\n");
	//exit();
}
$current_pickseq = "";
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] > "")
	{
		//echo( " Seq:" . $Row[0]);
		$current_pickseq = $Row[0];
	}
}
//release memory
ibase_free_result($Result);
*/
	$current_pickseq = getBDCScookie($Link, $tran_device, "CURRENT_PICK_SEQ" );

// now get the pick direction to use
/*
$Query = "select description from session where device_id='" . $DBDevice . "' and code = 'CURRENT_PICK_DIR' "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Sessions!<BR>\n");
	//exit();
}
$current_pickdir = "";
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] > "")
	{
		$current_pickdir = $Row[0];
	}
}
//release memory
ibase_free_result($Result);
*/
	$current_pickdir = getBDCScookie($Link, $tran_device, "CURRENT_PICK_DIR" );
if ($current_pickdir == "")
{
	// no pickdir so use asc
	$wk_pi_pickdir = 'ASC';
}
else
{
	// get pickdir from options
	$Query = "select description from options where group_code = 'PICK_DIR'  and code = '" . $current_pickdir . "'"; 
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Sequence Options!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_pi_pickdir = $Row[0];
	}
	//release memory
	ibase_free_result($Result);
}

if ($current_pickseq == "")
{
	// no pickseq so use from zero == all
	$wk_pi_locn_seq = 0;
}
else
{
	// get pickseq from options
	$Query = "select description from options where group_code = 'PICK_SEQ'  and code = '" . $current_pickseq . "'"; 
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Sequence Options!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_pi_locn_seq = $Row[0];
	}
	//release memory
	ibase_free_result($Result);
}


// ok now have the company and from device 
// the to device is $tran_device
// now need a procedure to calc the next prod and transfer just
// that to the new device

/*
$Query = "SELECT FIRST 1 P1.PROD_ID
FROM PICK_ITEM P1
WHERE P1.PICK_LINE_STATUS IN ('AL') 
AND  P1.DEVICE_ID = '" . $wk_from_device . "'
AND  P1.OVER_SIZED = 'T'
AND  P1.PICK_LINE_PRIORITY > 0
ORDER BY P1.PICK_LINE_PRIORITY"; 
*/
/*
$Query = "SELECT FIRST 1 P1.PROD_ID
FROM PICK_ITEM P1
WHERE  P1.DEVICE_ID = '" . $wk_from_device . "'
AND  P1.OVER_SIZED = 'T'
ORDER BY P1.PICK_LINE_PRIORITY"; 
*/
if ($wk_pi_pickdir == 'ASC')
{
	$Query = "SELECT FIRST 1 P1.PROD_ID
FROM PICK_ITEM P1
WHERE  P1.DEVICE_ID = '" . $wk_from_device . "'
AND  P1.OVER_SIZED = 'T'
AND PICK_LOCN_SEQ >= '" . $wk_pi_locn_seq . "'
ORDER BY P1.PICK_LINE_PRIORITY"; 
}
else
{
	$Query = "SELECT FIRST 1 P1.PROD_ID
FROM PICK_ITEM P1
WHERE  P1.DEVICE_ID = '" . $wk_from_device . "'
AND  P1.OVER_SIZED = 'T'
AND PICK_LOCN_SEQ <= '" . $wk_pi_locn_seq . "'
ORDER BY P1.PICK_LINE_PRIORITY DESC"; 
}

//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!<BR>\n");
	exit();
}

$wk_prod = "";
// Fetch the results from the database.
if ( ($Row = ibase_fetch_row($Result)) ) {
	$wk_prod = $Row[0];
}
//release memory
ibase_free_result($Result);

echo "Next Prod [" . $wk_prod . "]";


if ($wk_prod <> "")
{
	$transaction_type = "TRPK";
	$my_object = $wk_prod;
	$my_source = 'SSSSSSSSS';
	$tran_tranclass = "P";
	$tran_qty = 0;
	$my_sublocn = $tran_device;
	$location = $wk_from_device . 'T';
	$my_ref = "transfer pick of product to handheld" ;

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
	echo("<INPUT type=\"IMAGE\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=Confirm+Despatch&fromimage=');
	echo('Blank_Button_50x100.gif" alt="Despatch"></INPUT>');
*/
	echo('SRC="/icons/whm/continue_picks.gif" alt="ContinuePick"></INPUT>');
	echo("</FORM>");
echo ("</TR>");
echo ("</TABLE>");
/*
<script>
document.forms.all.submit();
</script>
*/
?>
</html>
