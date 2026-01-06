<?php
if (isset($_COOKIE['LoginUser']))
{
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	
	
	if (isset($_POST['product']))
	{
		$product = $_POST['product'];
	}
	if (isset($_GET['product']))
	{
		$product = $_GET['product'];
	}
	if (isset($_POST['net_weight']))
	{
		$net_weight = $_POST['net_weight'];
	}
	if (isset($_GET['net_weight']))
	{
		$net_weight = $_GET['net_weight'];
	}
	if (isset($_POST['net_weight_uom']))
	{
		$net_weight_uom = $_POST['net_weight_uom'];
	}
	if (isset($_GET['net_weight_uom']))
	{
		$net_weight_uom = $_GET['net_weight_uom'];
	}
	if (isset($_POST['inner_qty']))
	{
		$inner_qty = $_POST['inner_qty'];
	}
	if (isset($_GET['inner_qty']))
	{
		$inner_qty = $_GET['inner_qty'];
	}
	if (isset($_POST['inner_uom']))
	{
		$inner_uom = $_POST['inner_uom'];
	}
	if (isset($_GET['inner_uom']))
	{
		$inner_uom = $_GET['inner_uom'];
	}
	if (isset($_POST['order_qty']))
	{
		$order_qty = $_POST['order_qty'];
	}
	if (isset($_GET['order_qty']))
	{
		$order_qty = $_GET['order_qty'];
	}
	if (isset($_POST['order_uom']))
	{
		$order_uom = $_POST['order_uom'];
	}
	if (isset($_GET['order_uom']))
	{
		$order_uom = $_GET['order_uom'];
	}
	if (isset($_POST['order_weight']))
	{
		$order_weight = $_POST['order_weight'];
	}
	if (isset($_GET['order_weight']))
	{
		$order_weight = $_GET['order_weight'];
	}
	if (isset($_POST['order_weight_uom']))
	{
		$order_weight_uom = $_POST['order_weight_uom'];
	}
	if (isset($_GET['order_weight_uom']))
	{
		$order_weight_uom = $_GET['order_weight_uom'];
	}
	if (isset($_POST['from']))
	{
		$from = $_POST['from'];
	}
	if (isset($_GET['from']))
	{
		$from = $_GET['from'];
	}
	if (isset($_POST['grn']))
	{
		$grn = $_POST['grn'];
	}
	if (isset($_GET['grn']))
	{
		$grn = $_GET['grn'];
	}
	if (isset($_POST['company']))
	{
		$company = $_POST['company'];
	}
	if (isset($_GET['company']))
	{
		$company = $_GET['company'];
	}
	if (isset($_POST['prod_stock']))
	{
		$prod_stock = $_POST['prod_stock'];
	}
	if (isset($_GET['prod_stock']))
	{
		$prod_stock = $_GET['prod_stock'];
	}
	if (isset($_POST['stock']))
	{
		$prod_stock = $_POST['stock'];
	}
	if (isset($_GET['stock']))
	{
		$prod_stock = $_GET['stock'];
	}
	if (isset($_POST['pallet_qty']))
	{
		$pallet_qty = $_POST['pallet_qty'];
	}
	if (isset($_GET['pallet_qty']))
	{
		$pallet_qty = $_GET['pallet_qty'];
	}
	if (isset($_POST['pallet_uom']))
	{
		$pallet_uom = $_POST['pallet_uom'];
	}
	if (isset($_GET['pallet_uom']))
	{
		$pallet_uom = $_GET['pallet_uom'];
	}
	if (isset($_POST['per_pallet_qty']))
	{
		$per_pallet_qty = $_POST['per_pallet_qty'];
	}
	if (isset($_GET['per_pallet_qty']))
	{
		$per_pallet_qty = $_GET['per_pallet_qty'];
	}
	if (isset($_POST['outer_qty']))
	{
		$outer_qty = $_POST['outer_qty'];
	}
	if (isset($_GET['outer_qty']))
	{
		$outer_qty = $_GET['outer_qty'];
	}
	if (isset($_POST['per_outer_qty']))
	{
		$per_outer_qty = $_POST['per_outer_qty'];
	}
	if (isset($_GET['per_outer_qty']))
	{
		$per_outer_qty = $_GET['per_outer_qty'];
	}
	if (isset($_POST['alternate']))
	{
		$alternate = $_POST['alternate'];
	}
	if (isset($_GET['alternate']))
	{
		$alternate = $_GET['alternate'];
	}
	if (isset($_POST['dimension_x']))
	{
		$dimension_x = $_POST['dimension_x'];
	}
	if (isset($_GET['dimension_x']))
	{
		$dimension_x = $_GET['dimension_x'];
	}
	if (isset($_POST['dimension_y']))
	{
		$dimension_y = $_POST['dimension_y'];
	}
	if (isset($_GET['dimension_y']))
	{
		$dimension_y = $_GET['dimension_y'];
	}
	if (isset($_POST['dimension_z']))
	{
		$dimension_z = $_POST['dimension_z'];
	}
	if (isset($_GET['dimension_z']))
	{
		$dimension_z = $_GET['dimension_z'];
	}
	if (isset($_POST['dimension_x_uom']))
	{
		$dimension_x_uom = $_POST['dimension_x_uom'];
	}
	if (isset($_GET['dimension_x_uom']))
	{
		$dimension_x_uom = $_GET['dimension_x_uom'];
	}
	if (isset($_POST['dimension_y_uom']))
	{
		$dimension_y_uom = $_POST['dimension_y_uom'];
	}
	if (isset($_GET['dimension_y_uom']))
	{
		$dimension_y_uom = $_GET['dimension_y_uom'];
	}
	if (isset($_POST['dimension_z_uom']))
	{
		$dimension_z_uom = $_POST['dimension_z_uom'];
	}
	if (isset($_GET['dimension_z_uom']))
	{
		$dimension_z_uom = $_GET['dimension_z_uom'];
	}

	if (($inner_qty == "") or ($inner_qty == 0))
	{
		$inner_qty = $order_qty ;
	}
	else
	{
		$inner_qty = $order_qty / $inner_qty;
	}
	if (($per_pallet_qty == "") or ($per_pallet_qty == 0))
	{
		$per_pallet_qty = $order_qty ;
	}
	if (($per_outer_qty == "") or ($per_outer_qty == 0))
	{
		$per_outer_qty = $order_qty ;
	}

	if (isset($_POST['shortdesc']))
	{
		$short_desc = $_POST['shortdesc'];
	}
	if (isset($_GET['shortdesc']))
	{
		$short_desc = $_GET['shortdesc'];

	}
	if (isset($_POST['uom']))
	{
		$uom = $_POST['uom'];
	}
	if (isset($_GET['uom']))
	{
		$uom = $_GET['uom'];
	}
	if (isset($_POST['issue']))
	{
		$issue = $_POST['issue'];
	}
	if (isset($_GET['issue']))
	{
		$issue = $_GET['issue'];
	}
	include "transaction.php";

	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		if (isset($from))
		{
			header("Location: product.php?message=Can+t+connect+to+DATABASE!&product=". urlencode($product) . "&from=" . urlencode($from)  );
		}
		else
		{
			header("Location: product.php?message=Can+t+connect+to+DATABASE!&product=". urlencode($product) );
		}
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	// get the default stock -- for when the stock is null 
	$Query3 = "select default_prod_stock from control  "; 
	//echo($Query);
	$wk_default_stock = "U"; /* undefined */
	if (!($Result3 = ibase_query($Link, $Query3)))
	{
		echo("Unable to Read Control!<BR>\n");
		exit;
	}
	while ( ($Row3 = ibase_fetch_row($Result3)) ) {
		$wk_default_stock = $Row3[0];
	}
	//release memory
	ibase_free_result($Result3);

	// if product is empty then create the next prod_id
	if (trim($product) == "" )
	{
		if (isset($company))
		{
			$Query4 = "select prod_id, error_text from  NEXT_PROD_ID ('" . $company . "' )";
		}
		else
		{
			$Query4 = "select prod_id, error_text from  NEXT_PROD_ID ('' )";
		}
		$wk_next_prod = "";
		if (!($Result4 = ibase_query($Link, $Query4)))
		{
			echo("Unable to Read NEXT_PROD_ID!<BR>\n");
			exit;
		}
		while ( ($Row4 = ibase_fetch_row($Result4)) ) {
			$wk_next_prod = $Row4[0];
		}
		//release memory
		ibase_free_result($Result4);
		if ($wk_next_prod != "")
		{
			$product = $wk_next_prod;
		}
		else
		{
			header("Location: product.php?message=Unable+to+Get+Next+Product!&product=". urlencode($product) );
		}

	}


	if (isset($company))
	{
		if ($company != '')
		{
			$Query = "SELECT SHORT_DESC, PROD_TYPE, UOM, ISSUE_UOM, ORDER_UOM, STOCK, SSN_TRACK, STANDARD_COST, PALLET_UOM , PROD_ID, ALTERNATE_ID FROM PROD_PROFILE WHERE (PROD_ID = '" . $product . "' OR ALTERNATE_ID = '" . $product . "') AND COMPANY_ID = '" . $company . "'";
		} else {
			$Query = "SELECT SHORT_DESC, PROD_TYPE, UOM, ISSUE_UOM, ORDER_UOM, STOCK, SSN_TRACK, STANDARD_COST, PALLET_UOM , PROD_ID, ALTERNATE_ID FROM PROD_PROFILE WHERE (PROD_ID = '" . $product . "' OR ALTERNATE_ID = '" . $product . "') AND COMPANY_ID = 'ALL'";
		}
	} else {
		$Query = "SELECT SHORT_DESC, PROD_TYPE, UOM, ISSUE_UOM, ORDER_UOM, STOCK, SSN_TRACK, STANDARD_COST, PALLET_UOM , PROD_ID, ALTERNATE_ID FROM PROD_PROFILE WHERE PROD_ID = '" . $product . "' OR ALTERNATE_ID = '" . $product . "'";
	}
	//echo $Query;
	if (!($Result = ibase_query($Link, $Query)))
	{
		if (isset($from))
		{
			header("Location: product.php?message=Unable+to+read+prod_profile!&product=". urlencode($product) . "&from=" . urlencode($from)  );
		}
		else
		{
			header("Location: product.php?message=Unable+to+read+prod_profile!&product=". urlencode($product) );
		}
		exit();
	}
	$pp_short_desc =  "";
	$pp_prod_type  =  "";
	$pp_uom =  "";
	$pp_issue_uom =  "";
	$pp_order_uom =  "";
	$pp_stock =  "";
	$pp_track =  "";
	$pp_cost  =  0;
	$pp_pallet_uom =  "";
	$pp_prod_id =  "";
	$pp_alternate =  "";
	if (($Row = ibase_fetch_row($Result)))
	{
		$pp_short_desc =  $Row[0];
		$pp_prod_type  =  $Row[1];
		$pp_uom =  $Row[2];
		$pp_issue_uom =  $Row[3];
		$pp_order_uom =  $Row[4];
		$pp_stock =  $Row[5];
		$pp_track =  $Row[6];
		$pp_cost  =  $Row[7];
		$pp_pallet_uom =  $Row[8];
		$pp_prod_id =  $Row[9];
		$pp_alternate =  $Row[10];
	}
	if ($pp_prod_type == "")
		$pp_prod_type = "ST";
	if ($pp_uom == "")
		$pp_uom  = "EA";
	if ($pp_issue_uom == "")
		$pp_issue_uom  = "EA";
	if ($pp_order_uom == "")
		$pp_order_uom  = "EA";
	if ($pp_pallet_uom == "")
		$pp_pallet_uom  = "EA";
	if ($pp_stock  == "")
	{
		//$pp_stock      = "U";
		$pp_stock      = $wk_default_stock;
	}
	if ($pp_track  == "")
		$pp_track      = "T";
	if (empty($pp_cost))
		$pp_cost = 0;
	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
		unset($Result);
	}
/*
	if ($product == $pp_prod_id) //expected
	and ($alternate == $pp_alternate)  // expected - no PPPD 
		$wk_doPPA1 = False;
	else {
		if ($product == $pp_prod_id) //expected
		and ($alternate != $pp_alternate)  // do PPPD 
			$wk_doPPA1 = True;
		else
		{
			if ($pp_prod_id == '') //do PPPD 
			{
				$wk_doPPA1 = True;
				// ie a new product
			} else {
			   	// product <> pp_prod_id  
				// ie looked up up via alternate id
				$product = $pp_prod_id;
				$wk_doPPA1 = True;
			}
		}
	}
*/
	// if allowed to run pppd
	//if ($pp_short_desc != $short_desc) 
	if (($pp_short_desc != $short_desc) or
	    ($pp_uom        != $uom) or
	    ($pp_stock      != $prod_stock))
	{
		//create the product
		$my_object = $product ;
		//$location = $pp_prod_type . $pp_uom . $pp_issue_uom . $PP_order_uom . $pp_stock . $pp_track;
		//$location = $pp_prod_type . $uom . $pp_issue_uom . $PP_order_uom . $pp_stock . $pp_track;
		$location = $pp_prod_type . $uom . $pp_issue_uom . $order_uom . $prod_stock . $pp_track;
		$my_ref_x = $short_desc;
		//$my_ref_x .= "|" . $company        ;
		$my_sublocn = substr($my_ref_x, 0, 10);
		$my_ref = substr($my_ref_x, 10) ;
		$tran_qty = $pp_cost * 100;
		$my_source = 'SSBSSKSSS';
		$my_message = "";
		//$my_message = dotransaction_response("PPPD", "P", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
		$my_message = dotransaction_response("PPPD", "P", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, $product, $company);
		$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
		if ($my_message > "")
		{
			list($my_mess_field, $my_mess_label) = explode("=", $my_message);
			$my_responsemessage = urldecode($my_mess_label) . " ";
		}
		else
		{
			$my_responsemessage = "";
		}
		if ($my_responsemessage <> "Processed successfully ")
		{
			if (isset($from))
			{
				header("Location: product.php?product=". urlencode($product) . "&from=" . urlencode($from) . "&" .  $my_message  );
			}
			else
			{
			header("Location: product.php?product=". urlencode($product) . "&" . $my_message );
			}
		}
		//$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	}

	// do pppk
	$my_object = $product ;
	// need pallet_cfg 
	// perm_level 
	// tog
	// pallet_cfg_inner
	// issue
	// issue_uom
	// inner_weight
	// inner_weight_uom
        // home_locn_id
	//$Query = "SELECT PALLET_CFG_C, PERM_LEVEL, TOG_C, PALLET_CFG_INNER, ISSUE, ISSUE_UOM, INNER_WEIGHT, INNER_WEIGHT_UOM, PROD_RETRIEVE_STATUS FROM PROD_PROFILE WHERE PROD_ID = '" . $product . "'";
	if (isset($company))
	{
		$Query = "SELECT PALLET_CFG_C, PERM_LEVEL, TOG_C, PALLET_CFG_INNER, ISSUE, ISSUE_UOM, INNER_WEIGHT, INNER_WEIGHT_UOM, PROD_RETRIEVE_STATUS, PROD_ID, ALTERNATE_ID, HOME_LOCN_ID FROM PROD_PROFILE WHERE PROD_ID = '" . $product . "' AND COMPANY_ID = '" . $company . "'";
	} else {
		$Query = "SELECT PALLET_CFG_C, PERM_LEVEL, TOG_C, PALLET_CFG_INNER, ISSUE, ISSUE_UOM, INNER_WEIGHT, INNER_WEIGHT_UOM, PROD_RETRIEVE_STATUS, PROD_ID, ALTERNATE_ID, HOME_LOCN_ID FROM PROD_PROFILE WHERE PROD_ID = '" . $product . "'";
	}
	//echo $Query;
	if (!($Result = ibase_query($Link, $Query)))
	{
		if (isset($from))
		{
			header("Location: product.php?message=Unable+to+read+prod_profile2!&product=". urlencode($product) . "&from=" . urlencode($from)  );
		}
		else
		{
			header("Location: product.php?message=Unable+to+read+prod_profile2!&product=". urlencode($product) );
		}
		exit();
	}
	$wk_prod_id = "";
	$pp_pallet_cfg =  "";
	$pp_perm_level =  "";
	$pp_tog =  "";
	$pp_pallet_cfg_inner =  "";
	$pp_issue =  "";
	$pp_issue_uom =  "";
	$pp_inner_weight =  "";
	$pp_inner_weight_uom =  "";
	$pp_prod_retrieve_status =  "";
	$wk_alternate_id = "";
	$pp_home_locn_id = "";
	if (($Row = ibase_fetch_row($Result)))
	{
		$pp_pallet_cfg =  $Row[0];
		$pp_perm_level =  $Row[1];
		$pp_tog =  $Row[2];
		$pp_pallet_cfg_inner =  $Row[3];
		$pp_issue =  $Row[4];
		$pp_issue_uom =  $Row[5];
		$pp_inner_weight =  $Row[6];
		$pp_inner_weight_uom =  $Row[7];
		$pp_prod_retrieve_status =  $Row[8];
		$wk_prod_id =  $Row[9];
		$wk_alternate_id =  $Row[10];
		$pp_home_locn_id =  $Row[11];
	}
	if ($pp_prod_retrieve_status == "")
		$pp_prod_retrieve_status =  "F";

	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
		unset($Result);
	}
	$my_ref_x = $pp_tog . "|" . $pp_perm_level;
	$my_ref_x .= "|" . $pp_pallet_cfg_inner ;
	//$my_ref_x .= "|" . $pp_pallet_cfg . "|" . $pp_issue ;
	$my_ref_x .= "|" . $pp_pallet_cfg . "|" . $issue ;
	$my_ref_x .= "|" . $pp_issue_uom .  "|" . $net_weight;

	$my_ref_x .= "|" . $net_weight_uom . "|" . $inner_qty;
	$my_ref_x .= "|" . $inner_uom . "|" . $pp_inner_weight;
	$my_ref_x .= "|" . $pp_inner_weight_uom . "|" . $order_qty ;
	$my_ref_x .= "|" . $order_uom . "|" . $order_weight_uom ;
	$my_ref_x .= "|" . $per_pallet_qty    . "|" . $pallet_uom ;
	$my_ref_x .= "|" . $company        ;
	$my_ref_x .= "|" . $per_outer_qty     ;
	$my_ref_x .= "|"  ;
	$my_source = 'SSBSSKSSS';
	$tran_qty = $order_weight;
		
	$location = substr($my_ref_x, 0, 10);
	$my_sublocn = substr($my_ref_x, 10, 10);
	$my_ref = substr($my_ref_x, 20) ;

	$my_message = "";
	//$my_message = dotransaction_response("PPPK", "P", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
	//echo ("$my_message = dotransaction_response('PPPK', 'P', $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device,'Y', $product, $company);");
	$my_message = dotransaction_response("PPPK", "P", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, $product, $company);
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	if ($my_message > "")
	{
		list($my_mess_field, $my_mess_label) = explode("=", $my_message);
		$my_responsemessage = urldecode($my_mess_label) . " ";
	}
	else
	{
		$my_responsemessage = "";
	}
	if ($my_responsemessage <> "Processed successfully ")
	{
		if (isset($from))
		{
			header("Location: product.php?product=". urlencode($product) . "&from=" . urlencode($from) . "&" .  $my_message  );
		}
		else
		{
		header("Location: product.php?product=". urlencode($product) . "&" . $my_message );
		}
		//$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	}

	// do ppcp
	$my_object = $product ;
	$my_source = 'SSBSSKSSS';
	$tran_qty = 0;
	// need prods prod retreive status		
	$location = "";
	$my_sublocn = "";
	$my_ref = $company . "|". $pp_prod_retrieve_status . "|";

	$my_message = "";
	//$my_message = dotransaction_response("PPCP", "P", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, 'Y');
	//echo ("$my_message = dotransaction_response('PPCP', 'P', $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, 'Y', $product, $company);");
	$my_message = dotransaction_response("PPCP", "P", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, $product, $company);
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	if ($my_message > "")
	{
		list($my_mess_field, $my_mess_label) = explode("=", $my_message);
		$my_responsemessage = urldecode($my_mess_label) . " ";
	}
	else
	{
		$my_responsemessage = "";
	}
	if ($my_responsemessage <> "Processed successfully ")
	{
		if (isset($from))
		{
			header("Location: product.php?product=". urlencode($product) . "&from=" . urlencode($from) . "&" .  $my_message  );
		}
		else
		{
			header("Location: product.php?product=". urlencode($product) . "&" . $my_message );
		}
	}

	// do ppai
	$my_object = $product ;
	$my_source = 'SSBSSKSSS';
	$tran_qty = 0;
	$location = $pp_home_locn_id;
	$my_sublocn = "";
	$my_ref = $alternate . "|" . $company .  "|";

	$my_message = "";
	$my_message = dotransaction_response("PPAI", "P", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, $product, $company);
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	if ($my_message > "")
	{
		list($my_mess_field, $my_mess_label) = explode("=", $my_message);
		$my_responsemessage = urldecode($my_mess_label) . " ";
	}
	else
	{
		$my_responsemessage = "";
	}
	if ($my_responsemessage <> "Processed successfully ")
	{
		if (isset($from))
		{
			header("Location: product.php?product=". urlencode($product) . "&from=" . urlencode($from) . "&" .  $my_message  );
		}
		else
		{
			header("Location: product.php?product=". urlencode($product) . "&" . $my_message );
		}
	}

	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
	}
	// do dimensions
		
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	$Query = "update prod_profile set "  .
		" dimension_x  =  '" . $dimension_x . "'," . 
		" dimension_y  =  '" . $dimension_y . "'," . 
		" dimension_z  =  '" . $dimension_z . "'," . 
		" dimension_x_uom  =  '" . $dimension_x_uom . "'," . 
		" dimension_y_uom  =  '" . $dimension_y_uom . "'," . 
		" dimension_z_uom  =  '" . $dimension_z_uom . "'," . 
		" last_update_by   =  '" . $tran_user . "',"  . 
		" last_update_date  =  'NOW' "  . 
		" where prod_id = '" . $product . "'" .
		" and company_id = '" . $company . "'";
	//echo("[$Query]\n");
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Update Product!<BR>\n");
		exit();
	}
	//commit
	ibase_commit($dbTran);
	
	//want to go to 
	if (isset($from) )
	{
		if (isset($product))
		{
			if (isset($grn))
			{
				header("Location: " . $from . "?product=" . urlencode($product) . "&grn=" . urlencode($grn) );
			}
			else
			{
				header("Location: " . $from . "?product=" . urlencode($product) );
			}
		}
		else
			header("Location: " . $from );
	}
	else
	{
		header("Location: getlocn.php" );
	}
}
?>
