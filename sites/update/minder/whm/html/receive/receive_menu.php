<?php
set_include_path(get_include_path() . PATH_SEPARATOR . '../includes');
include "../login.inc";
setcookie("BDCSData","", time()+11186400, "/");
include "viewport.php";

?>
<html>
 <head>
  <TITLE>Get Receive Type</TITLE>
<?php
if (strpos($_SERVER['HTTP_USER_AGENT'] , "NetFront") === false)
{
  echo('<link rel=stylesheet type="text/css" href="receive.css">');
}
else
{
  echo('<link rel=stylesheet type="text/css" href="receive-netfront.css">');
}
?>
<style type="text/css">
body {
     font-family: sans-serif;
     font-size: 13px;
     border: 0; padding: 0; margin: 0;
}
form, div {
    border: 0; padding:0 ; margin: 0;
}
table {
    border: 0; padding: 7px 4px; margin: 0;
}
#tablebutton { 
position: absolute;
top: 15pt;
}
</style>
 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="GREEN">

<H4>Receive - Select Receive Type</H4>
<?php
require_once 'DB.php';
require 'db_access.php';
include 'logme.php';
include "2buttons.php";

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
  echo("Can't connect to DATABASE!");
  //exit();
}
list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
$Query = "select sys_admin, editable from sys_user where user_id = '" . $tran_user . "'";
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
  echo("Unable to Read User!<BR>\n");
  //exit();
}
$sysadmin = "";
$editable = "";
while ( ($Row = ibase_fetch_row($Result)) ) {
  if ($Row[0] > "")
  {
    $sysadmin = $Row[0];
  }
  if ($Row[1] > "")
  {
    $editable = $Row[1];
  }
}

//release memory
ibase_free_result($Result);
$Query = "select pick_order_weight_calc, receive_method from control ";
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
  echo("Unable to Read Control!<BR>\n");
  //exit();
}
$do_recalc_weight = "";
$receive_method = "";
while ( ($Row = ibase_fetch_row($Result)) ) {
  if ($Row[1] > "")
  {
    $do_recalc_weight = $Row[0];
    $receive_method = $Row[1];
  }
}

//release memory
ibase_free_result($Result);

$wk_screen_id = 'RECEIVE_MENU';
if ( $sysadmin == "T") 
{
	$Query = "select description from options where group_code = 'RECEIVE' and code = 'RECEIVE_MENU|AD'  ";
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Options!<BR>\n");
		//exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		if ($Row[0] > "")
		{
	    		$receive_method .= $Row[0];
  		}
	}

	//release memory
	ibase_free_result($Result);
}

//$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
$wk_reset_grn_params = ''; /* get from options table */
$Query = "select description from options where group_code = 'RECEIVE' and code = 'RECEIVE_MENU|RESETLASTGRN'  ";
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
  echo("Unable to Read Options!<BR>\n");
  //exit();
}
while ( ($Row = ibase_fetch_row($Result)) ) {
  {
    $wk_reset_grn_params  = $Row[0];
  }
}

//release memory
ibase_free_result($Result);
//echo ("reset grn:" . $wk_reset_grn_params);

if ($wk_reset_grn_params == 'T')
{
	$received_qty = getBDCScookie($Link, $tran_device, "received_qty" );
	$order = getBDCScookie($Link, $tran_device, "order" );
	$line = getBDCScookie($Link, $tran_device, "line" );
	$carrier = getBDCScookie($Link, $tran_device, "carrier" );
	$vehicle = getBDCScookie($Link, $tran_device, "vehicle" );
	$container = getBDCScookie($Link, $tran_device, "container" );
	$pallet_type = getBDCScookie($Link, $tran_device, "pallet_type" );
	$pallet_qty = getBDCScookie($Link, $tran_device, "pallet_qty" );
	$consignment = getBDCScookie($Link, $tran_device, "consignment" );
	$grn = getBDCScookie($Link, $tran_device, "grn" );
	$problem = getBDCScookie($Link, $tran_device, "problem" );
	if (($received_qty != "") or
 	    ($order != "") or
 	    ($line != "") or
	    ($carrier != "") or
	    ($vehicle != "") or
	    ($container != "") or
	    ($pallet_type != "") or
	    ($pallet_qty != "") or
	    ($consignment != "") or
	    ($grn != "") or
	    ($problem != ""))
	{
		setBDCScookie($Link, $tran_device, "grn", "");
		setBDCScookie($Link, $tran_device, "order", "");
		setBDCScookie($Link, $tran_device, "line", "");

		setBDCScookie($Link, $tran_device, "received_qty", "");
		setBDCScookie($Link, $tran_device, "carrier", "");
		setBDCScookie($Link, $tran_device, "vehicle", "");
		setBDCScookie($Link, $tran_device, "container", "");
		setBDCScookie($Link, $tran_device, "pallet_type", "");
		setBDCScookie($Link, $tran_device, "pallet_qty", "");
		setBDCScookie($Link, $tran_device, "consignment", "");
		setBDCScookie($Link, $tran_device, "problem", "");

		setBDCScookie($Link, $tran_device, "retfrom", "");
		setBDCScookie($Link, $tran_device, "product", "");
		setBDCScookie($Link, $tran_device, "label_qty1", "");
		setBDCScookie($Link, $tran_device, "ssn_qty1", "");
		setBDCScookie($Link, $tran_device, "weight_qty1", "");
		setBDCScookie($Link, $tran_device, "weight_uom", "");
		//setBDCScookie($Link, $tran_device, "printer", "");
		setBDCScookie($Link, $tran_device, "received_ssn_qty", "");
		setBDCScookie($Link, $tran_device, "location", "");
		setBDCScookie($Link, $tran_device, "uom", "");
		setBDCScookie($Link, $tran_device, "printed_ssn_qty", "");
		setBDCScookie($Link, $tran_device, "other1", "");
		setBDCScookie($Link, $tran_device, "label_qty2", "");
		setBDCScookie($Link, $tran_device, "ssn_qty2", "");
		setBDCScookie($Link, $tran_device, "owner", "" );
		setBDCScookie($Link, $tran_device, "other_qty1", "" );
		setBDCScookie($Link, $tran_device, "other_qty3", "" );
		setBDCScookie($Link, $tran_device, "other_qty2", "" );
		setBDCScookie($Link, $tran_device, "other_qty4", "" );
		setBDCScookie($Link, $tran_device, "complete", "" );
		setBDCScookie($Link, $tran_device, "WH_ID", "" );
		//commit
		ibase_commit($dbTran);
	}
         
}
?>
    <!-- html 4.0 browser -->
    <TABLE BORDER="0">
    <TR>
<?php

{
	// Create a table.
	echo ("<table border=\"0\" align=\"left\" class=\"tablebutton\" id=\"tablebutton\" >");
	echo ("<tbody>");
	echo ("<tr>");
	$wk_label_posn = 0;
	addMenuButton(",BACK,", ",BACK,", "../mainmenu.php", "back", "/icons/whm/Back_50x100.gif", "Back");
	addMenuButton(",LP,", $receive_method, "./newreceive.php?type=LP", "loadProduct", "/icons/whm/Load_Product_50x100.gif", "Load 
Product");
	addMenuButton(",LP-TR,", $receive_method, "./newreceive.php?type=LP&alttype=TR", "ProductTransfer", "/icons/whm/TRANSFER_50x100.gif", "Product
Transfers");
	//addMenuButton(",POF,", $receive_method, "./newreceive.php?type=PO", "newpurchase", "/icons/whm/button.php?text=New+Purchase&fromimage=Blank_Button_50x100.gif", "New Purchase");
	addMenuButton(",POF,", $receive_method, "./newreceive.php?type=PO", "newpurchase", "/icons/whm/purchaseorder.gif", "Purchase");
	//addMenuButton(",LPO,", $receive_method, "./getdelivery.php?type=LP", "oldproduct", "/icons/whm/button.php?text=Product&fromimage=Blank_Button_50x100.gif", "Product");
	addMenuButton(",LPO,", $receive_method, "./getdelivery.php?type=LP", "oldproduct", "/icons/whm/Load_Product_50x100.gif", "Product");
	addMenuButton(",PO,", $receive_method, "./getdelivery.php?type=PO", "purchase", "/icons/whm/purchaseorder.gif", "Purchase");
	addMenuButton(",RA,", $receive_method, "./getdelivery.php?type=RA", "return", "/icons/whm/returns.gif", "Returns");
	addMenuButton(",TR,", $receive_method, "./getdelivery.php?type=TR", "transfer", "/icons/whm/TRANSFER_50x100.gif", "Transfers");
	addMenuButton(",WO,", $receive_method, "./getdelivery.php?type=WO", "WO", "/icons/whm/workorder.gif", "WorkOrder");
	addMenuButton(",LD,", $receive_method, "./getdelivery.php?type=LD", "load", "/icons/whm/Load_50x100.gif", "Load");
	addMenuButton(",LD-LP,", $receive_method, "./newreceive.php?type=LD", "load", "/icons/whm/Load_50x100.gif", "Load");
	addMenuButton(",LD-DEL,", $receive_method, "./getdelivery2.php?type=LD", "deliveryOnly", "/icons/whm/button.php?text=Delivery+Only&fromimage=Blank_Button_50x100.gif", "Delivery
Only");
	addMenuButton(",LD-VER,", $receive_method, "./getgrnorder.php?type=LD", "VerifyOnly", "/icons/whm/button.php?text=Verify+Only&fromimage=Blank_Button_50x100.gif", "Verify
Only");
	addMenuButton(",REPRINT,", ",REPRINT,", "./reprint.php", "Reprint", "/icons/whm/REPRINT_50x100.gif", "Reprint 
Labels");
	addMenuButton(",LD-LBL,", $receive_method, "./getgrn.php?type=LD", "ReLabelOnly", "/icons/whm/button.php?text=Reprint+GRN+Only&fromimage=Blank_Button_50x100.gif", "Reprint
GRN
Labels");
	//addMenuButton(",LP-NG,", $receive_method, "./getverifyLP.php?type=LP", "ReVerifyOnly", "/icons/whm/button.php?text=Re-Verify+LP&fromimage=Blank_Button_50x100.gif", "Verify 
	addMenuButton(",LP-NG,", $receive_method, "./getgrnLP.php?type=LP", "ReVerifyOnly", "/icons/whm/button.php?text=Re-Verify+LP&fromimage=Blank_Button_50x100.gif", "Verify 
LP
Only");
}

?>
    </tr>
  </tbody>
  </table>
 </body>
</html>
