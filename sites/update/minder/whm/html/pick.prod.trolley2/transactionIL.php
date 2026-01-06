<?php
session_start();

/*
18/7/2011 although allocates many orders at once only pick 1 order at a time
so in  	getfromlocn check that only 1 order on the device in AL PG PL
		other wise update all the AL PG PL to Al Pg Pl
		then update the first order to be AL PG PL from Al Pg Pl
   in  	transactionIL check that finished order 
		if finished update the next order to AL PG PL 
		then continue as normal 
23/08/2012 Add PKUF to update the pick orders customer po wo
*/
/**
 * get ISSNs on the Device
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkDevice
 * @return array
 */
function getISSNonDevice ($Link, $wkDevice )
{
	// device  = tran_device
	$wkResult = array();
	//$Query = "select i1.prod_id, p2.pick_order, p2.pick_label_no, p2.prod_id, p2.ssn_id, sum(p1.qty_picked)  
        //          group by i1.prod_id , p2.pick_label_no, p2.pick_order, p2.prod_id, p2.ssn_id ";
	$Query = "select i1.prod_id, p2.pick_order, p2.pick_label_no, p2.prod_id, p2.ssn_id, sum(p1.qty_picked)   
                  from pick_item p2 
                  join pick_item_detail p1 on p2.pick_label_no = p1.pick_label_no
                  join issn i1 on p1.ssn_id = i1.ssn_id
                  where p2.device_id = '" . $wkDevice . "'
                  and   p2.pick_line_status = 'PL' 
                  and   p1.pick_detail_status = 'PL' 
                  and   p1.qty_picked  > 0
                  and   p1.device_id = '" . $wkDevice . "'
                  and   i1.locn_id = '" . $wkDevice . "'
                  and   i1.current_qty > 0
                  group by i1.prod_id , p2.pick_label_no, p2.pick_order, p2.prod_id, p2.ssn_id  ";
	//echo ($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Pick Items!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkIssn = array();
		$wkIssn['ISSN.PROD_ID']  = $Row[0];
		$wkIssn['PICK_ORDER']  = $Row[1];
		$wkIssn['PICK_LABEL_NO']  = $Row[2];
		$wkIssn['PROD_ID']  = $Row[3];
		$wkIssn['SSN_ID']  = $Row[4];
		if (is_int($Row[5]))
		{
			$wkIssn['QTY_PICKED']  = $Row[5];
		} else {
			$wkIssn['QTY_PICKED']  = 0;
		}

		$wkResult[]  = $wkIssn;
	}
	//release memory
	ibase_free_result($Result);
	//echo('issns:' . print_r($wkResult,true));
	return $wkResult;
}

/**
 * get default device for the company that the order is for
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkOrder  Order to use to get Company 
 * @return array
 */
function getZoneDevice ($Link, $wkOrder )
{
	$wkResult = "";
	$Query = "select z1.default_device_id   
                  from pick_order p1
                  join zone z1 on p1.company_id  = z1.company_id
                  where p1.pick_order = '" . $wkOrder . "' ";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Pick Order!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkResult  = $Row[0];
	}
	//release memory
	ibase_free_result($Result);
	if ($wkResult == "") 
	{
		$Query = "select first 1  z1.default_device_id   
                  from zone z1 ";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Pick Order!<BR>\n");
			exit();
		}
		while ( ($Row = ibase_fetch_row($Result)) ) {
			$wkResult  = $Row[0];
		}
		//release memory
		ibase_free_result($Result);
	}
	return $wkResult;
}


/**
 * get last used PO Order in picking this order 
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkOrder  Order to use to get Company 
 * @return array
 */
function getPOOrder ($Link, $wkOrder )
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	$wkResult = "";
	$wkIssn   = "";
	$Query = "select first 1 ssn_id 
                  from pick_item_detail  p1
                  where p1.pick_order = '" . $wkOrder . "' order by last_update_date desc ";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Pick Item Detail !<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkIssn    = $Row[0];
	}
	//release memory
	ibase_free_result($Result);
	if ($wkIssn == "") 
	{
		// have no issn
	} else {
		$Query = "select ssn.po_order    
                  from issn join ssn on ssn.ssn_id = issn.original_ssn where issn.ssn_id = '" . $wkIssn . "' ";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read SSN!<BR>\n");
			exit();
		}
		while ( ($Row = ibase_fetch_row($Result)) ) {
			$wkResult  = $Row[0];
		}
		//release memory
		ibase_free_result($Result);
	}
	return $wkResult;
}


/**
 * get Orders  on the Device that are WIP
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkDevice
 * @return array
 */
function getOrderWIP ($Link, $wkDevice )
{
	// device  = tran_device
	$wkResult = array();
	$Query = "select p2.pick_order  
                  from pick_item p2 
                  where p2.device_id = '" . $wkDevice . "'
                  and   p2.pick_line_status = 'PL' 
                  group by p2.pick_order
                   ";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Pick Items!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkIssn = array();
		$wkIssn['PICK_ORDER']  = $Row[0];
		$wkIssn['STATUS']  = "PL";
		$wkResult[]  = $wkIssn;
	}
	//release memory
	ibase_free_result($Result);
	//echo('issns:' . print_r($wkResult,true));
	$Query = "select p2.pick_order  
                  from pick_item p2 
                  where p2.device_id = '" . $wkDevice . "'
                  and   p2.pick_line_status IN ('AL','PG') 
                  group by p2.pick_order
                   ";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Pick Items2!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkIssn = array();
		$wkIssn['PICK_ORDER']  = $Row[0];
		$wkIssn['STATUS']  = "AL";
		// does the order already exist in array
		$wk_found = False;
		foreach ($wkResult as $wk_ssn_id => $wk_issn2)
		{
			if ($wk_issn2['PICK_ORDER'] == $wkIssn['PICK_ORDER'])
			{
				$wk_issn2['STATUS']  = "AL";
				$wkResult[$wk_ssn_id] = $wk_issn2 ;
				$wk_found = True;
			}
		}
		// else add it
		if (!$wk_found)
		{
			$wkResult[]  = $wkIssn;
		}
	}
	//release memory
	ibase_free_result($Result);
	//echo('issns:' . print_r($wkResult,true));
	return $wkResult;
}


/**
 * check that location already exists in pick_location for this order
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkOrder
 * @param string $wkLocation
 * @param string $wkDevice
 * @return boolean
 */
function checkPickLocation ($Link, $wkOrder, $wkLocation, $wkDevice )
{
	// device  = tran_device
	$wkResult = False;
	$Query = "select p2.pick_order  
                  from pick_location p2 
                  where p2.device_id = '" . $wkDevice . "'
                  and   p2.pick_location_status in ( 'OP','DS')
                  and   p2.pick_order = '" . $wkOrder . "'
                  and   p2.wh_id  = '" . substr($wkLocation,0,2) . "'
                  and   p2.locn_id  = '" . substr($wkLocation,2,strlen($wkLocation) - 2) . "'
                   ";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Pick Location!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkResult  = True;
	}
	//release memory
	ibase_free_result($Result);
	//echo('issns:' . print_r($wkResult,true));
	return $wkResult;
}


/**
 * check that location already exists in pick_location for this order
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkOrder
 * @param string $wkLocation
 * @param string $wkDevice
 * @return boolean
 */
function checkDespatchLocation ($Link, $wkLocation, $wkDevice )
{
	// device  = tran_device
	$wkResult = False;
	$Query = "select p2.locn_name  
                  from location p2 
                  where p2.move_stat = 'DS'
                  and   p2.wh_id  = '" . substr($wkLocation,0,2) . "'
                  and   p2.locn_id  = '" . substr($wkLocation,2,strlen($wkLocation) - 2) . "'
                   ";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Location!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkResult  = True;
	}
	//release memory
	ibase_free_result($Result);
	//echo('issns:' . print_r($wkResult,true));
	return $wkResult;
}


/**
 * get the product , customer edi and store in house no for this order line
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkOrder
 * @param string $wkLabel
 * @param string $wkLocation
 * @param string $wkDevice
 * @return array
 */
function getPickLine ($Link, $wkOrder, $wkLabel, $wkLocation, $wkDevice )
{
	// device  = tran_device
	$wkResult = array();
	$wkResult['PROD_ID']  = '';
	$wkResult['CUSTOMER_EDI_NO']  = '';
	$wkResult['STORE_IN_HOUSE_NO']  = '';
	$wkResult['COMPANY_ID']  = '';
	$wkResult['CUSTOMER_METHOD']  = '';
	$wkResult['DC_IN_HOUSE_NO']  = '';
	$wk_opt_ps_populate_out_sscc_method = "" ;
	$Query = "select  description from options where group_code='REP_CODE' and code='PACK_CUSTOMER.PC_POPULATE_SSCC_METHOD'  "; 
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Control !<BR>\n");
		exit();
	}
	if  ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_opt_ps_populate_out_sscc_method  = $Row[0];
	}
	//release memory
	ibase_free_result($Result);

	// get the pick item info
	$Query = "select p2.prod_id, p2.ps_customer_edi_no, p2.ps_del_to_dc_in_house_no, p1.company_id, p2.ps_del_to_store_in_house_no
                  from pick_item  p2 
                  join pick_order p1 on p2.pick_order = p1.pick_order
                  where p2.pick_label_no = '" . $wkLabel . "'
                  and   p2.pick_order = '" . $wkOrder . "'
                  and   p2.device_id = '" . $wkDevice . "'
                  and   p2.pick_line_status in ( 'AL','PG','PL')
                   ";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Pick Item!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkResult['PROD_ID']  = $Row[0];
		$wkResult['CUSTOMER_EDI_NO']  = $Row[1];
		$wkResult['DC_IN_HOUSE_NO']  = $Row[2];
		$wkResult['COMPANY_ID']  = $Row[3];
		$wkResult['STORE_IN_HOUSE_NO']  = $Row[4];
	}
	//release memory
	ibase_free_result($Result);
	// now  get the customer method
	$wk_pc_method = ''; 
	if ($wk_opt_ps_populate_out_sscc_method  <> 'F' )
	{
		$Query = "select pc_populate_out_sscc_method 
                	  from pack_customer   
	                  where pc_customer_edi_no = '" . $wkResult['CUSTOMER_EDI_NO'] . "'
        	          and   company_id = '" . $wkResult['COMPANY_ID'] . "'
                	   ";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Pack Customer!<BR>\n");
			exit();
		}
		while ( ($Row = ibase_fetch_row($Result)) ) {
			$wk_pc_method = $Row[0]; 
		}
		//release memory
		ibase_free_result($Result);
	}
	$wkResult['CUSTOMER_METHOD']  = $wk_pc_method;
	// clear out the fields not required for the method
	if ($wk_pc_method == "")
	{
		// not an edi order
		// empty the 3 fields
		$wkResult['PROD_ID']  = '';
		$wkResult['CUSTOMER_EDI_NO']  = '';
		$wkResult['STORE_IN_HOUSE_NO']  = '';
		$wkResult['DC_IN_HOUSE_NO']  = '';
	} elseif ($wk_pc_method == "ORDER DELIVER TO" )
	{
		// empty the product field
		$wkResult['PROD_ID']  = '';
	} else {
		$wk_dummy = 1;
	}
	//echo('line:' . print_r($wkResult,true));
	return $wkResult;
}


/**
 * check that the PICK_LOCATION is ok to use for this line
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkOrder
 * @param string $wkLabel
 * @param string $wkLocation
 * @param string $wkDevice
 * @return boolean
 */
function checkPickLine ($Link, $wkOrder, $wkLabel, $wkLocation, $wkDevice )
{
	// device  = tran_device
	$wkResult = False;
	$wk_pl_store_in_house_no = '';
	$wk_pl_dc_in_house_no = '';
	$wk_pl_customer_edi_no = '';
	$wk_pl_prod_id = '';
	$wk_pl_dc_in_house_no = '';
	// first get the product,customer edi no and store in house no as in the getPickLine
	$wkLine =  getPickLine ($Link, $wkOrder, $wkLabel, $wkLocation, $wkDevice );
	if ($wkLine['CUSTOMER_METHOD'] == '')
	{
		// no method so allow this
		$wkResult  = True;
	} else {
		// an edi order with an customer method
		//now get the fields from that pick location
		$Query = "select p2.ps_del_to_dc_in_house_no , p2.ps_customer_edi_no , p2.ps_product_gtin, p2.ps_del_to_store_in_house_no 
                	  from pick_location p2 
	                  where p2.device_id = '" . $wkDevice . "'
        	          and   p2.pick_location_status in ( 'OP','DS')
                	  and   p2.pick_order = '" . $wkOrder . "'
	                  and   p2.wh_id  = '" . substr($wkLocation,0,2) . "'
        	          and   p2.locn_id  = '" . substr($wkLocation,2,strlen($wkLocation) - 2) . "'
                	   ";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Pick Location!<BR>\n");
			exit();
		}
		while ( ($Row = ibase_fetch_row($Result)) ) {
			$wk_pl_dc_in_house_no = $Row[0];
			$wk_pl_customer_edi_no = $Row[1];
			$wk_pl_prod_id = $Row[2];
			$wk_pl_store_in_house_no = $Row[3];
		}
		//release memory
		ibase_free_result($Result);
		if ($wk_pl_prod_id == $wkLine['PROD_ID'] and
		    $wk_pl_customer_edi_no == $wkLine['CUSTOMER_EDI_NO'] and
		    $wk_pl_dc_in_house_no == $wkLine['DC_IN_HOUSE_NO']
		    //$wk_pl_store_in_house_no == $wkLine['STORE_IN_HOUSE_NO']
                )
		{ 
			$wkResult  = True;
		}
	}
	//echo('line:' . print_r($wkLine,true));
	return $wkResult;
}


/**
 * get ISSNs on the Device
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkDevice
 * @return array
 */
function doTRPK ($Link, $wkOrder )
{
	// transfer order from this device to conveyor device
	// from my device to device for company of order
	// object = order
	// location = from device
	// sub locn = to device
	// ref = comment
	$wk_cn_location = "" ;
	$wk_cn_do_pkuf = "" ;
	$Query = "select  pick_direct_delivery_location, send_customer_po_wo_in_export from control  "; 
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Control !<BR>\n");
		exit();
	}
	if  ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_cn_location = $Row[0];
		$wk_cn_do_pkuf = $Row[1];
	}
	//release memory
	ibase_free_result($Result);

	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	$my_location = $tran_device . "        ";
	$wk_to_device = getZoneDevice($Link, $wkOrder);
	$wk_sublocn = ($wk_to_device == "") ?  $tran_device : $wk_to_device;
	$my_source = 'SSBSSKSSS';
	$my_message = "";
	$my_message = dotransaction("TRPK", "o", $wkOrder, $my_location, $wk_sublocn, "transfer to Conveyor:Finished Pick", 0, $my_source, $tran_user, $tran_device, "N");
	//echo($my_message);
	if ($my_message > "")
	{
		list($my_mess_field, $my_mess_label) = explode("=", $my_message);
		$my_responsemessage = urldecode($my_mess_label) . " ";
	}
	else
	{
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
		header("Location: pick_Menu.php?" . $my_message);
		exit();
	}
	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
	}
	if ($wk_cn_do_pkuf == "T") 
	{
		doPKUF ($Link, $wkOrder );
	}
}
		

/**
 * update the orders customer_po_wo to the last purchase order used
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkDevice
 * @return array
 */
function doPKUF ($Link, $wkOrder )
{
	// update the sale orders customer po wo 
	// from my device to device for company of order
	// object = order
	// location = from device
	// ref = field to update | new value | comment
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	$my_location = $tran_device . "        ";
	$wk_to_order = getPOOrder($Link, $wkOrder);
	$wk_sublocn = "";
	$my_source = 'SSBSSKSSS';
	$wk_ref = "CUSTOMER_PO_WO|" . $wk_to_order . "|";
	if ($wk_to_order != "") {
		$my_message = "";
		$my_message = dotransaction("PKUF", "O", $wkOrder, $my_location, $wk_sublocn, $wk_ref, 0, $my_source, $tran_user, $tran_device, "N");
		//echo($my_message);
		if ($my_message > "")
		{
			list($my_mess_field, $my_mess_label) = explode("=", $my_message);
			$my_responsemessage = urldecode($my_mess_label) . " ";
		}
		else
		{
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
			header("Location: pick_Menu.php?" . $my_message);
			exit();
		}
		//release memory
		if (isset($Result))
		{
			ibase_free_result($Result); 
		}
	}
}
		

/**
 * check for More Orders to Pick
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkDevice
 * @return string
 */
function check4MoreOrders ($Link, $wkDevice )
{
	// device  = tran_device
	$wkNextOrder = "";
	$wkLastOrder = "";

	$Query = "select first 1  p2.pick_order   
                  from pick_item p2 
                  where p2.device_id = '" . $wkDevice . "'
                  and   p2.pick_line_status in ('AL','PG','PL') ";
	//echo ($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Pick Items!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkLastOrder = $Row[0];
	}
	//release memory
	ibase_free_result($Result);

	$Query = "select first 1  p2.pick_order   
                  from pick_item p2 
                  where p2.device_id = '" . $wkDevice . "'
                  and   p2.pick_line_status in ('Al','Pg','Pl') ";
	//echo ($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Pick Items!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkNextOrder = $Row[0];
	}
	//release memory
	ibase_free_result($Result);
	if (($wkNextOrder != "") and ($wkLastOrder == ""))
	{
		$Query = "update pick_item 
                          set pick_line_status = upper(pick_line_status)
                          where pick_order = '" . $wkNextOrder . "'
                          and device_id = '" . $wkDevice . "' ";
		//echo ($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Update Pick Items!<BR>\n");
			exit();
		}
	}

	if ($wkLastOrder != "")
	{
		$wkSaveNextOrder =  $wkNextOrder  ;
		$wkNextOrder = "";
	}

	return $wkNextOrder;
}
// ================================================================================================================

if (isset($_COOKIE['LoginUser']))
{
	$tran_type = "PKIL";
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	

	$qty = 1;
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: pick_Menu.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	require_once('logme.php');
	require_once('transaction.php');
	$wk_ok_reason = "";
	// allow for param entry for location 
	include "checkdata.php";
	$my_object = '';
	$my_source = 'SSBSSKSSS';
	$my_ref = '';
	$type='';
	$order='';
	$order_no='';
	$label='';
	$qty = 0;
	if (isset($_POST['ttype']))
	{
		$type = $_POST['ttype'];
	}
	if (isset($_GET['ttype']))
	{
		$type = $_GET['ttype'];
	}
	$tran_tranclass = $type;
	if (isset($_POST['order']))
	{
		$order = $_POST['order'];
	}
	if (isset($_GET['order']))
	{
		$order = $_GET['order'];
	}
	$my_object = $order;
	if (isset($_POST['order_no']))
	{
		$order_no = $_POST['order_no'];
	}
	if (isset($_GET['order_no']))
	{
		$order_no = $_GET['order_no'];
	}
	if (isset($_POST['location']))
	{
		$location = $_POST['location'];
	}
	if (isset($_GET['location']))
	{
		$location = $_GET['location'];
	}
	if (isset($_POST['label']))
	{
		$label = $_POST['label'];
	}
	if (isset($_GET['label']))
	{
		$label = $_GET['label'];
	}
	if ($type == 'M')
	{
		$my_sublocn = $tran_device;
	}
	else
	{
		$my_sublocn = $label;
	}
	if (isset($_POST['qty']))
	{
		$qty = $_POST['qty'];
	}
	if (isset($_GET['qty']))
	{
		$qty = $_GET['qty'];
	}
	if (is_int ($qty))
	{
		$tran_qty = $qty;
	} else {
		$tran_qty = 0;
	}


	// check that the passed location is a location
	{
		// pick by location
		$field_type = checkForTypein($location, 'LOCATION' ); 
		if ($field_type != "none")
		{
			// a location
			if ($startposn > 0)
			{
				$wk_realdata = substr($location,$startposn);
				$location = $wk_realdata;
				if (strlen($location) > 10)
				{
					$location = substr($location,0,10);
				}
			}
		} else {
			$location = "";
			$wk_ok_reason .= "Invalid Location";
			//$message .= $my_responsemessage;
			header("Location: gettolocn.php?order=" . urlencode($order) . "&ttype=I&message=" . urlencode($wk_ok_reason));
			exit();
		}
	}
	// check that location is a trolley location for this order and device
	$wk_do_poal = False;
	$wk_do_poal =  checkPickLocation ($Link, $order_no, $location, $tran_device );
	// otherwise add it
	//
	if (!$wk_do_poal)
	{
		// if the location is a despatch location  then dont do this
		$wk_is_despatch = False;
		$wk_is_despatch =  checkDespatchLocation ($Link, $location, $tran_device );
		// otherwise add it
		if (!$wk_is_despatch)
		{
			$my_ref = $tran_user . "|" . $tran_device . "| assign order to order";
			// if an edi order
			// 	get the customer pc_populate_out_sscc_method
			// 	if PRODUCT ORDER DELIVER TO
			//		populate product into reference
			//	populate customer edi no and del 2 store no into reference
			$wk_line_info =  getPickLine ($Link, $order_no, $label, $location, $tran_device );
			$my_ref = $my_ref . "|" . $wk_line_info['PROD_ID'] . "|" . $wk_line_info['CUSTOMER_EDI_NO'] . "|" . $wk_line_info['DC_IN_HOUSE_NO'];
			$my_message = "";
			//echo(" dotransaction('POAL','O',  $order_no, $location, 'T1', $my_ref, $tran_qty, $my_source, $tran_user, $tran_device )");
			$my_message = dotransaction("POAL", "O", $order_no, $location, "T1", $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, "N");
			//echo($my_message);
			if ($my_message > "")
			{
				list($my_mess_field, $my_mess_label) = explode("=", $my_message);
				$my_responsemessage = urldecode($my_mess_label) . " ";
			}
			else
			{
				$my_responsemessage = "";
			}
			if ($my_responsemessage == "")
			{
				$my_responsemessage = "Processed successfully ";
			}
			//echo($my_responsemessage);
			if ($my_responsemessage <> "Processed successfully ")
			{
				//commit
				ibase_commit($dbTran);
				$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
				if (isset($order))
				{
					header("Location: gettolocn.php?order=" . urlencode($order) . "&ttype=I&" . $my_message);
				}
				else
				{
					header("Location: gettolocn.php?ttype=I&" . $my_message);
				}
				exit();
			}
		}
	} else {
		// the location is already in use on this order
		//    want check that the location is ok to use for this line
		//    ie 
		// 	if an edi order
		// 		get the customer pc_populate_out_sscc_method
		// 		if PRODUCT ORDER DELIVER TO
		//		then the existing pick_location must have the same product as this label no
		//		the existing pick_location must have the same customer_edi_no
		//		the existing pick_location must have the same del 2 store no
		$wk_location_ok =  checkPickLine ($Link, $order_no, $label, $location, $tran_device );
		if (!$wk_location_ok)
		{
			//commit
			ibase_commit($dbTran);
			$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
			$my_message = "Scan another Location : Cannot use that Location for this Item"; 
			$my_message = "message=" . urlencode($my_message);
			if (isset($order))
			{
				header("Location: gettolocn.php?order=" . urlencode($order) . "&ttype=I&" . $my_message);
			}
			else
			{
				header("Location: gettolocn.php?ttype=I&" . $my_message);
			}
			exit();
		}
		
	}
	//commit
	ibase_commit($dbTran);
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	/* write transaction */
	$my_message = "";
	//$my_message = dotransaction($tran_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, "N");
	//echo(" dotransaction($tran_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, 'N' )");
	$my_message = dotransaction($tran_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, "N");
	echo($my_message);
	if ($my_message > "")
	{
		list($my_mess_field, $my_mess_label) = explode("=", $my_message);
		$my_responsemessage = urldecode($my_mess_label) . " ";
	}
	else
	{
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
		header("Location: pick_Menu.php?" . $my_message);
		exit();
	}

	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
	}
	//commit
	ibase_commit($dbTran);
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
		
/*
	$Query = "select 1 from pick_item ";
	$Query .= " where pick_line_status in ('PL')";
	$Query .= " and device_id = '".$tran_device."'";
	//print($Query);
	
	if (!($Result = ibase_query($Link, $Query)))
	{
		print("Unable to Read Total!<BR>\n");
		exit();
	}
	$despatch_cnt = 0;
	if (($Row = ibase_fetch_row($Result)))
	{
		$despatch_cnt =  $Row[0];
	}
	
	//release memory
	ibase_free_result($Result);
*/
	
	$Query = "select 1 from pick_item ";
	$Query .= " where pick_line_status in ('AL','PG')";
	$Query .= " and device_id = '".$tran_device."'";
	//print($Query);
	
	if (!($Result = ibase_query($Link, $Query)))
	{
		print("Unable to Read Total!<BR>\n");
		exit();
	}
	$continue_cnt = 0;
	if (($Row = ibase_fetch_row($Result)))
	{
		$continue_cnt =  $Row[0];
	}
	
	//release memory
	ibase_free_result($Result);
	
	$wkOnDevice =  getISSNonDevice ($Link, $tran_device );

	// if all lines on this device for this order
	// are not ( AL or PG )
	// then do transfer to default device
	$Query = "select 1 from pick_item ";
	$Query .= " where pick_line_status in ('AL','PG')";
	$Query .= " and device_id = '".$tran_device."'";
	if ($order_no <> "")
	{
		$Query .= " and pick_order = '".$order_no."'";
	}
	//print($Query);
	
	if (!($Result = ibase_query($Link, $Query)))
	{
		print("Unable to Read Total!<BR>\n");
		exit();
	}
	$continue_order_cnt = 0;
	if (($Row = ibase_fetch_row($Result)))
	{
		$continue_order_cnt =  $Row[0];
	}
	
	//release memory
	ibase_free_result($Result);
	echo("continue_order_cnt:" . $continue_order_cnt);
	echo("order_no:" . $order_no );
	if ($continue_order_cnt == 0)
	{
		if ($order_no == "")
		{
			// no order no
			// so get list of orders on device with PL status (not AL or PG)
			// want pick item detail with status PL and issn placed into location - not just PL status
			$wkOrders = getOrderWIP ($Link, $tran_device );
			$wkOnDevice =  getISSNonDevice ($Link, $tran_device );
			foreach ($wkOrders as $wk_ssn_id => $wk_issn2)
			{
				if ($wk_issn2['STATUS'] == 'PL')
				{
					//echo(print_r($wk_issn2,true));
					$wk_ok = True ;
					foreach ($wkOnDevice as $wk_ondevice_id => $wk_ondevice2)
					{
						$wkPOOrder = $wk_ondevice['PO_ORDER'];
						//echo(print_r($wk_ondevice2,true));
						if ($wk_ondevice2['PICK_ORDER'] == $wk_issn2['PICK_ORDER'] )
						{
							$wk_ok = False;
						}
					}

					if ($wk_ok)
					{
						$order_no = $wk_issn2['PICK_ORDER'];
						echo("would do trpk 1 " . $order_no);
						doTRPK ($Link, $order_no  );
						// check whether have other orders
						$wkNextOrder = check4MoreOrders ($Link, $tran_device );
						if ($wkNextOrder != "")
						{
							$continue_cnt = 1;
						}
					}
				}
			}
		}
		else {
			$wkOnDevice =  getISSNonDevice ($Link, $tran_device );
			//echo(print_r($wkOnDevice,true));
			$wk_ok = True ;
			foreach ($wkOnDevice as $wk_ondevice_id => $wk_ondevice2)
			{
				//echo(print_r($wk_ondevice2,true));
				if ($wk_ondevice2['PICK_ORDER'] == $order_no  )
				{
					$wk_ok = False;
				}
			}

			if ($wk_ok)
			{
				echo("would do trpk 2 ". $order_no);
				// do trpk for this order
				doTRPK ($Link, $order_no  );
				// check whether have other orders
				$wkNextOrder = check4MoreOrders ($Link, $tran_device );
				if ($wkNextOrder != "")
				{
					$continue_cnt = 1;
				}
			}
			// so get list of orders on device with PL status (not AL or PG)
			// want pick item detail with status PL and issn placed into location - not just PL status
			$wkOrders = getOrderWIP ($Link, $tran_device );
			//echo(print_r($wkOrders,true));
			$wkOnDevice =  getISSNonDevice ($Link, $tran_device );
			//echo(print_r($wkOnDevice,true));
			foreach ($wkOrders as $wk_ssn_id => $wk_issn2)
			{
				if ($wk_issn2['STATUS'] == 'PL')
				{
					//echo(print_r($wk_issn2,true));
					$wk_ok = True ;
					foreach ($wkOnDevice as $wk_ondevice_id => $wk_ondevice2)
					{
						//echo(print_r($wk_ondevice2,true));
						if ($wk_ondevice2['PICK_ORDER'] == $wk_issn2['PICK_ORDER'] )
						{
							$wk_ok = False;
						}
					}

					if ($wk_ok)
					{
						$order_no = $wk_issn2['PICK_ORDER'];
						echo("would do trpk 3 " . $order_no);
						doTRPK ($Link, $order_no  );
					}
				}
			}
		}
	}

	//commit
	ibase_commit($dbTran);
	
	//close
	//ibase_close($Link);

	//want to go to pick screen
	// if anymore product on the device  (not in a temporary location)
	// then go back to gettolocn

	$wkMode = count($wkOnDevice) > 0 ? 'Place' : 'Move';
	echo("last mode:"  . $wkMode);
	if ($wkMode == "Place")
	{
		header("Location: gettolocn.php");
	}
	else
	{
		if ($continue_cnt > 0)
		{
			header("Location: getfromlocn.php");
		}
		else
		{
			header("Location: pick_Menu.php");
		}
	}
}
?>
