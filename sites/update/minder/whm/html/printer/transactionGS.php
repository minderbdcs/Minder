<?php
session_start();

require_once('DB.php');
require('db_access.php');
require_once('transaction.php');

if (isset($_COOKIE['LoginUser']))
{
	$tran_type = "DSGS";
	// Set the variables for the database access:
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	$label_no = '';
	$order = '';
	$prod_no = '';
	
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: print_Menu.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	include "logme.php";
	$my_object = '';
	// get the passed record id
	$my_record_id = '';
	if (isset($_POST['salesorder']))
	{
		$my_record_id = $_POST['salesorder'];
	}
	if (isset($_GET['salesorder']))
	{
		$my_record_id = $_GET['salesorder'];
	}
	// get the passed matchorders 
	$wk_matchorders = '';
	if (isset($_POST['matchorders']))
	{
		$wk_matchorders = $_POST['matchorders'];
	}
	if (isset($_GET['matchorders']))
	{
		$wk_matchorders = $_GET['matchorders'];
	}
	// now get the sscc and order for this pack
	$wk_sscc = "";
	$wk_pick_order  = "";
	$wk_company  = "" ;
	$wk_pick_order_type  = "";
	$wk_pick_order_sub_type  = "";
	$wk_pick_prod_id  = "";
	$wk_despatch_location  = "";
	$Query = "select ps_sscc, ps_pick_order, p2.company_id ,p2.pick_order_type, p2.pick_order_sub_type , case when p3.prod_id is null then p1.ps_product_gtin else p3.prod_id end , control.default_despatch_location  "; 
	$Query .= "from pack_sscc p1 ";
	$Query .= "join pick_order p2 on p1.ps_pick_order = p2.pick_order ";
	$Query .= "left outer join pick_item p3 on p1.ps_pick_label_no = p3.pick_label_no ";
	$Query .= "join control  on control.record_id = 1 ";
	$Query .= "where  ";
	$Query .= "  p1.record_id = '" . $my_record_id  . "'";
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Packs !<BR>\n");
		exit();
	}
	while (($Row3 = ibase_fetch_row($Result))) {
		$wk_sscc = $Row3[0];
		$wk_pick_order  = $Row3[1];
		$wk_company  = $Row3[2];
		$wk_pick_order_type  = $Row3[3];
		$wk_pick_order_sub_type  = $Row3[4];
		$wk_pick_prod_id  = $Row3[5];
		$wk_despatch_location  = $Row3[6];
	}

	//release memory
	ibase_free_result($Result);
		
	
	$my_source = 'SSBSSKSSS';
	$tran_tranclass = "N";

	$tran_qty = 1;

	$my_location = $wk_despatch_location ;
	$wk_sublocn = "" ;
	$my_ref = '|DC|0.000|0.000|0.000|0.000|0.000|1||||||SYS_EQUIP.DEVICE_ID=PB|';
	$my_message = "";
	$my_message = dotransaction("DSGS", "N", $wk_sscc , $my_location, $wk_sublocn, $my_ref, 0, $my_source, $tran_user, $tran_device, "Y", $wk_pick_prod_id, $wk_company, $wk_pick_order, $wk_pick_order_type, $wk_pick_order_sub_type, "");
	//echo($my_message);
	if ($my_message > "")
	{
		list($my_mess_field, $my_mess_label) = explode("=", $my_message);
		$my_responsemessage = urldecode($my_mess_label) . " ";
	} else {
		$my_responsemessage = "";
	}
	if ($my_responsemessage == "")
	{
		$my_responsemessage = "Processed successfully ";
	}
	//echo($my_responsemessage);
	if ($my_responsemessage <> "Processed successfully ")
	{
		//$message .= $my_responsemessage;
		header("Location: print_Menu.php?" . $my_message);
		exit();
	}
	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result);
	}



	//want to go to print menu screen
	//header("Location: print_Menu.php");
	header("Location: GetSSCC.php?matchorders=" . urlencode($wk_matchorders) . "&" . $my_message);
}
?>
