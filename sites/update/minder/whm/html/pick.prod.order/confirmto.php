<?php
include "../login.inc";
?>
<SCRIPT>
function processCancel() {
	var dowhat;
	dowhat=confirm("Unpick and Cancel All?");
	if(dowhat)
	{
		document.forms.cancelall.submit();
		return true;
	}
	else
	{
		return false;
	}
}
function processUnpick() {
	var dowhat;
	dowhat=confirm("Unpick All?");
	if(dowhat)
	{
		document.forms.unpickall.submit();
		return true;
	}
	else
	{
		return false;
	}
}
</SCRIPT>
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
	
if (isset($_POST['order']))
{
	$order = $_POST['order'];
}
if (isset($_GET['order']))
{
	$order = $_GET['order'];
}

echo("<FONT size=\"2\">\n");

{
	$Query = "select count(prod_id), pick_order "; 
	$Query .= "from pick_item  ";
	$Query .= " where device_id = '".$tran_device."'";
	$Query .= " and pick_line_status = 'PL'";
	$Query .= " and prod_id > ''";
	$Query .= " group by pick_order ";
}
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!<BR>\n");
	exit();
}

$got_items = 0;
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	$got_items = $Row[0];
	if ($order == "")
	{
		$order = $Row[1];
	}
}
//release memory
ibase_free_result($Result);

$Query = "select default_despatch_location from control "; 

//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!<BR>\n");
	exit();
}

$wk_default_locn = "";
// Fetch the results from the database.
if ( ($Row = ibase_fetch_row($Result)) ) {
	$wk_default_locn = $Row[0];
}
//release memory
ibase_free_result($Result);

echo("Tot Items <INPUT type=\"text\" readonly name=\"qtyitems\" size=\"1\" value=\"$got_items\" ><BR>");


echo ("<TABLE>\n");
echo ("<TR>\n");
//if ($got_orders == 1)
{
	echo("<TH>Select Confirm Despatch or Cancel</TH>\n");
}
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
	echo("<FORM action=\"transactionIL.php\" method=\"post\" name=all>\n");
	echo("<INPUT type=\"hidden\" name=\"ttype\" value=\"M\">");
	echo("<INPUT type=\"hidden\" name=\"location\" value=\"$wk_default_locn\">");
	echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\">");
	echo("<INPUT type=\"IMAGE\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=Confirm+Despatch&fromimage=');
	echo('Blank_Button_50x100.gif" alt="Despatch"></INPUT>');
*/
	echo('SRC="/icons/whm/confirmdespatch.gif" alt="Despatch"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	echo ("<TD>");
	echo("<FORM action=\"transactionCN.php\" method=\"post\" onsubmit=\"return processUnpick();\" name=unpickall>\n");
	echo("<INPUT type=\"hidden\" name=\"trans_type\" value=\"PKCA\">\n");
	echo("<INPUT type=\"IMAGE\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=UnPick+All&fromimage=');
	echo('Blank_Button_50x100.gif" alt="all"></INPUT>');
*/
	echo('SRC="/icons/whm/unpickall.gif" onClick=\"return processUnpick();\" alt="UnPickAll"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
echo ("</TR>");
echo ("<TR>");
	echo ("<TD>");
	//echo("<FORM action=\"transactionCN.php?trans_code=C\" method=\"get\" onsubmit=\"return processCancel();\" name=cancelall>\n");
	echo("<FORM action=\"transactionCN.php\" method=\"get\" onsubmit=\"return processCancel();\" name=cancelall>\n");
	echo("<INPUT type=\"hidden\" name=\"trans_type\" value=\"PKCA\">\n");
	echo("<INPUT type=\"hidden\" name=\"trans_code\" value=\"C\">\n");
	echo("<INPUT type=\"IMAGE\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=Cancel+All&fromimage=');
	echo('Blank_Button_50x100.gif" alt="all"></INPUT>');
*/
	echo('SRC="/icons/whm/cancelall.gif" onClick=\"return processCancel();\" alt="cnAll"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
echo ("</TR>");
echo ("</TABLE>");
?>
</HTML>
