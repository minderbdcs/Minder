<?php
session_start();

/* 08/07/2013 in deadlock loop
   and labels produced need to update these to an EX status
       issns need to be rolled back
       
*/

/* 15/07/2013 had to take out the javascript disable of bbuttons to pocomplete
   so use a session variable instead
*/
/*
if (isset($_POST) && !empty($_POST))
{
    if (isset($_SESSION['posttimer']))
    {
        if ( (time() - $_SESSION['posttimer']) <= 2)
        {
            // less then 2 seconds since last post
            // treat this as notok
            $wkTransPONVProcess = "NOTOK";
            header("Location: getPOLine.php?message=Already+Being+Processed" );
            exit();
        }   
        else
        {
            // more than 2 seconds since last post
            // treat this as ok
            $wkTransPONVProcess = "OK";
        }   
    }   
    $_SESSION['posttimer'] = time();
}   
*/
if (isset($_SESSION['token'])) {
    if (isset($_POST['token'])) {
        if ($_POST['token'] != $_SESSION['token']) {
            $wkTransPONVProcess = "NOTOK";
            header("Location: getPOLine.php?message=Already+Being+Processed" );
            exit();
        } else {
            // process form
            // reset session token
            $_SESSION['token'] = md5( $_POST['token'] . time() );
            $wkTransPONVProcess = "OK";
        }
    } else {
        //echo 'post token not set';
        $_SESSION['token'] = md5( 'transPONV.php'  . time() );
        $wkTransPONVProcess = "OK";
    }
}

// ========================  Functions  ===========================================================================
/**
 * get Label Fields for ISSN label
 *
 * @param ibase_link $Link Connection to database
 * @param printer object $p 
 * @return string
 */
function getIssnLabel($Link, $p)
{
    //echo("start:" . __FUNCTION__);

    $printerId = $p->data['printer_id'] ;
    //echo("printer:" . $printerId );
    $issnId = $p->data['ISSN.SSN_ID'];
    //echo("ISSN:" . $issnId );
    $sql = "SELECT WK_LABEL_DATA  FROM ADD_ISSN_LABEL (?, ?) ";
    $query = ibase_prepare($Link, $sql);
    $output = false;
        
    if (false !== ($result = ibase_execute($query, $printerId, $issnId ))) {
        if (false !== ($row = ibase_fetch_row($result))) {
            $output = $row[0];
        } else {
            $output = false;
        }
        ibase_free_result($result);
    }
    if ($output !== FALSE) {
        //echo("\nlabel data:" );
        //echo($output );
        //echo("end of label data\n" );
        $wkParams = explode ("|", $output );
        //echo("Params:");
        //var_dump($wkParams);
	foreach ($wkParams as $k => $v) {
            //echo("\nk:" . $k);
            //echo("\nv:" . $v);
            $wkParams2 = explode("=", $v,2);
            //echo("\nParam:");
            //var_dump($wkParams2 );
            $nameId = $wkParams2[0];
            // need to remove '%' from begin and end of name
            $nameId = substr($nameId,1,strlen($nameId) - 2);
            if (isset($wkParams2[1]) ) {
                $p->data[$nameId] = $wkParams2[1];
            } else {
                $p->data[$nameId] = "";
            } 
	}
    }
    
    //echo("exit " . __FUNCTION__);
    return $p;
}


/**
 * get Label Fields for Pick label
 *
 * @param ibase_link $Link Connection to database
 * @param printer object $p 
 * @return string
 */
function getPickLabel($Link, $p)
{
    //echo("start:" . __FUNCTION__);

    $printerId = $p->data['printer_id'] ;
    //echo("printer:" . $printerId );
    $issnId = $p->data['ISSN.SSN_ID'];
    $pickLabelNo = $p->data['PICK_ITEM.PICK_LABEL_NO'];
    //echo("pickLabel:" . $pickLabelNo  );
    $pickOrder = $p->data['PICK_ITEM.PICK_ORDER'];
    //echo("pickOrder:" . $pickOrder );
    $prodId  = $p->data['ISSN.PROD_ID'];
    //echo("ISSN:" . $issnId );
    $sql = "SELECT WK_LABEL_DATA  FROM ADD_PICK_PRODUCT_LABEL (?, ?, ?, ?) ";
    $query = ibase_prepare($Link, $sql);
    $output = false;
        
    if (false !== ($result = ibase_execute($query, $printerId, $pickLabelNo, $pickOrder, $prodId ))) {
        if (false !== ($row = ibase_fetch_row($result))) {
            $output = $row[0];
        } else {
            $output = false;
        }
        ibase_free_result($result);
    }
    if ($output !== FALSE) {
        //echo("\nlabel data:" );
        //echo($output );
        //echo("end of label data\n" );
        $wkParams = explode ("|", $output );
        //echo("Params:");
        //var_dump($wkParams);
	foreach ($wkParams as $k => $v) {
            //echo("\nk:" . $k);
            //echo("\nv:" . $v);
            $wkParams2 = explode("=", $v,2);
            //echo("\nParam:");
            //var_dump($wkParams2 );
            $nameId = $wkParams2[0];
            // need to remove '%' from begin and end of name
            $nameId = substr($nameId,1,strlen($nameId) - 2);
            if (isset($wkParams2[1]) ) {
                $p->data[$nameId] = $wkParams2[1];
            } else {
                $p->data[$nameId] = "";
            } 
	}
    }
    /* add the ssn and issn fields */
    //echo("ISSN:" . $issnId );
    $sql = "SELECT WK_LABEL_DATA  FROM ADD_ISSN_LABEL (?, ?) ";
    $query = ibase_prepare($Link, $sql);
    $output = false;
        
    if (false !== ($result = ibase_execute($query, $printerId, $issnId ))) {
        if (false !== ($row = ibase_fetch_row($result))) {
            $output = $row[0];
        } else {
            $output = false;
        }
        ibase_free_result($result);
    }
    if ($output !== FALSE) {
        //echo("\nlabel data:" );
        //echo($output );
        //echo("end of label data\n" );
        $wkParams = explode ("|", $output );
        //echo("Params:");
        //var_dump($wkParams);
	foreach ($wkParams as $k => $v) {
            //echo("\nk:" . $k);
            //echo("\nv:" . $v);
            $wkParams2 = explode("=", $v,2);
            //echo("\nParam:");
            //var_dump($wkParams2 );
            $nameId = $wkParams2[0];
            // need to remove '%' from begin and end of name
            $nameId = substr($nameId,1,strlen($nameId) - 2);
            if (isset($wkParams2[1]) ) {
                $p->data[$nameId] = $wkParams2[1];
            } else {
                $p->data[$nameId] = "";
            } 
	}
    }
    
    //echo("exit " . __FUNCTION__);
    return $p;
}

/**
 * getPrinterIp
 *
 * @param $Link
 * @param string $printerId
 * @return string or null
 */
function getPrinterIp($Link, $printerId) {
    $result = '';
    $sql = 'SELECT IP_ADDRESS FROM SYS_EQUIP WHERE DEVICE_TYPE = \'PR\' AND DEVICE_ID = ?';
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        $r = ibase_execute($q, $printerId);
        if ($r) {
            $d = ibase_fetch_row($r);
            if ($d) {
                $result = $d[0];
            }
            ibase_free_result($r);
        }
        ibase_free_query($q);
    }
    return $result;
}


/**
 * getPrinterDir
 *
 * @param $Link
 * @param string $printerId
 * @return string or null
 */
function getPrinterDir($Link, $printerId) {
    $result = '';
    $sql = 'SELECT WORKING_DIRECTORY FROM SYS_EQUIP WHERE DEVICE_TYPE = \'PR\' AND DEVICE_ID = ?';
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        $r = ibase_execute($q, $printerId);
        if ($r) {
            $d = ibase_fetch_row($r);
            if ($d) {
                $result = $d[0];
            }
            ibase_free_result($r);
        }
        ibase_free_query($q);
    }
    return $result;
}


/**
 * get the  Pick Lines ready to be picked for this product
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkProduct the Product to use
 * @param string $wkQty the Qty of the picks to use
 * @param string $wkLocation is receive Location that the stock will be in 
 * @param string $wkCompany the Company to use
 * @param string $wkSaleChannel whether or not to use sale channels T or F
 * @param string $wkOrderSaleChannel SaleChannel for the Purchase Order
 * @param string $wkPRRecord Array of RECORD_IDs of the PROD_RESERVATION record involved 
 * @return array
 * @oldparam string old parameter $wkPRRecordId RECORD_ID of the PROD_RESERVATION record involved 
 */
//function getLines4Picks ($Link, $wkProduct, $wkQty, $wkLocation, $wkCompany )
function getLines4Picks ($Link, 
                         $wkProduct, 
                         $wkQty, 
                         $wkLocation, 
                         $wkCompany ,
                         $wkSaleChannel, 
                         $wkOrderSaleChannel,
                         $wkPRRecords)
{
    //echo("start:" . __FUNCTION__);
    //echo("product:" . $wkProduct);
    //echo("qty:" . $wkQty);
	$wkResult = array();
	$wkQtyUsed = 0;
	$wkWhId = substr($wkLocation,0,2);

	foreach ($wkPRRecords as $wk_prrecord_id => $wk_PRRecord)
	{
		$Query = "select p2.pick_order, p1.pick_label_no , p1.pick_order_qty, p1.picked_qty, p1.partial_pick_allowed, p2.partial_pick_allowed, p1.exported_despatch, p1.parent_label_no "; 
		$Query .= "from pick_item p1 ";
		$Query .= "join pick_order p2 on p1.pick_order = p2.pick_order ";
		$Query .= "where p1.pick_line_status in ('OP','UP') ";
		$Query .= "and   p1.prod_id = '" . $wkProduct . "' ";
		$Query .= "and   p2.pick_status in ('OP','DA') ";
		$Query .= "and   p2.wh_id = '" . $wkWhId . "' ";
		$Query .= "and   p2.company_id = '" . $wkCompany . "' ";
		/* 11/10/12 want to change this so that
	           do the selected channel first then treat as NONE
	           ie do the other channels for the product order by the prod reservation priority then channel code
	           finally do the orders without a channel
	        */
		//if ($wkPRRecordId <> 0 and $wkOrderSaleChannel <> "NONE" and $wkSaleChannel == "T" ) {
		//if ($wk_PRRecord['PR_RECORD_ID'] <> 0 and $wk_PRRecord['PR_SALE_CHANNEL_CODE']  <> "NONE" and $wkSaleChannel == "T" ) {
		if (isset($wk_PRRecord['PR_RECORD_ID']) and isset($wkPRRecord['PR_SALE_CHANNEL_CODE']) and $wkSaleChannel == "T" ) {
			if ($wk_PRRecord['PR_RECORD_ID'] <> 0 and $wk_PRRecord['PR_SALE_CHANNEL_CODE']  <> "NONE" and $wkSaleChannel == "T" ) {
				/* have a prod_reservation so limit the orders to those that use it */
				//$Query .= "and   p2.other2 = '" . $wkOrderSaleChannel . "' ";
				$Query .= "and   p2.other2 = '" . $wk_PRRecord['PR_SALE_CHANNEL_CODE'] . "' ";
			}
		}
		//if ( $wkOrderSaleChannel == "NONE" and $wkSaleChannel == "T" ) {
		//if ( $wk_PRRecord['PR_SALE_CHANNEL_CODE'] == "NONE" and $wkSaleChannel == "T" ) {
		if (isset($wkPRRecord['PR_SALE_CHANNEL_CODE']) and $wkSaleChannel == "T" ) {
			if ( $wk_PRRecord['PR_SALE_CHANNEL_CODE'] == "NONE" and $wkSaleChannel == "T" ) {
				/* have a  NONE Sales Channel so limit the orders to those that use it */
				$Query .= "and   coalesce(p2.other2,'') = '' ";
			}
		}
	/*
		if ( $wkSaleChannel == "F" ) {
			-* have No prod_reservation so no limits  *-
		}
	*/
		$Query .= " and  (p1.pick_order_qty - coalesce(p1.picked_qty,0) ) > 0  "; 
	/* that will give 
	lines for the product that are unpicked
	1 now must ensure that this line or product is the only line or product waiting  for this order */
		$Query .= "and   not exists ( select p3.pick_label_no 
	                           from pick_item p3 
	                           where p3.pick_order = p1.pick_order 
	                           and   p3.pick_line_status in ('OP','UP')
			           and   ( p3.prod_id <> p1.prod_id ) ) ";
	/* 2 if this product is a component line for a kit
	   then must ensure that there are no AS status lines in the order for the kit
	*/
		$Query .= "and   not exists ( select p4.pick_label_no 
	                           from pick_item p4 
	                           where p4.pick_order = p1.pick_order 
	                           and   p4.pick_line_status in ('AS')
			           and   p4.parent_label_no = p1.parent_label_no   
			           and   ( p4.parent_label_no is not null ) ) ";
		//$Query .= " order by p2.pick_priority  "; 
		//$Query .= " , p2.create_date   "; 
		$Query .= " order by p2.pick_priority, p2.pick_due_date, p2.pick_order  ";
	
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Orders Count!<BR>\n");
			exit();
		}
		while (($Row5 = ibase_fetch_row($Result))) {
			$wkIssn = array();
			$wkIssn['PICK_ORDER']  = $Row5[0];
			$wkIssn['PICK_LABEL_NO']  = $Row5[1];
			$wkIssn['PICK_ORDER_QTY']  = $Row5[2];
			//$wkIssn['PR_RECORD_ID']  = $wk_PRRecord['PR_RECORD_ID']  ;
			$wkIssn['PR_RECORD_ID']  = isset($wk_PRRecord['PR_RECORD_ID']) ? $wk_PRRecord['PR_RECORD_ID'] : 0 ;
			//$wkIssn['PR_SALE_CHANNEL_CODE']  = $wk_PRRecord['PR_SALE_CHANNEL_CODE'] ;
			$wkIssn['PR_SALE_CHANNEL_CODE']  = isset($wk_PRRecord['PR_SALE_CHANNEL_CODE']) ? $wk_PRRecord['PR_SALE_CHANNEL_CODE'] : "NONE" ;
			if (is_int($Row5[3]))
			{
				$wkIssn['PICKED_QTY']  = $Row5[3];
			} else {
				$wkIssn['PICKED_QTY']  = 0;
			}
			$wkIssn['PARTIAL_PICK_ALLOWED']  = $Row5[4];
			$wkIssn['PICK_ORDER.PARTIAL_PICK_ALLOWED']  = $Row5[5];
			$wkIssn['EXPORTED_DESPATCH']  = $Row5[6];
			$wkIssn['PARENT_LABEL_NO']  = $Row5[7];
			$wk_reqd_qty = $wkIssn['PICK_ORDER_QTY'] - $wkIssn['PICKED_QTY'];
	/*
	start 0 in used 
	ie reqd <= wkQty
	then at used 10
	reqd  3
	wkQty = 12
	do we part pick this ?
	if pick order partial pick is T
		if exported_despatch is T or F ie a non kit component then yes
		if exported_despatch is K  ie a kit component then no 
	else
		no
	*/
			if (is_null($wkIssn['PICK_ORDER.PARTIAL_PICK_ALLOWED'])) {
				$wkIssn['PICK_ORDER.PARTIAL_PICK_ALLOWED']  = 'F';
			}
			if (is_null($wkIssn['EXPORTED_DESPATCH'])) {
				$wkIssn['EXPORTED_DESPATCH']  = 'F';
			}
			if ($wkIssn['PICK_ORDER.PARTIAL_PICK_ALLOWED'] == 'T') {
				if ($wkIssn['EXPORTED_DESPATCH'] == 'K') {
					$wk_do_partial = 'F';
				} else {
					$wk_do_partial = 'T';
				}
			} else {
				$wk_do_partial = 'F';
			}
			if ($wk_do_partial == 'T') {
				// do the partial pick
				if ($wkQtyUsed  < $wkQty)
				{
					$wk_available_qty = $wkQty - $wkQtyUsed;
					$wkIssn['QTY_TO_PICK'] = min($wk_reqd_qty, $wk_available_qty);
					$wkQtyUsed +=  $wkIssn['QTY_TO_PICK'] ;
					$wkResult[]  = $wkIssn;
				} else {
					//echo "break from pick item loop";
					break;
				}
			} else {		
				// do the full pick
				if (($wkQtyUsed + $wk_reqd_qty) <= $wkQty)
				{
					$wkIssn['QTY_TO_PICK'] = $wk_reqd_qty;
					$wkQtyUsed += $wk_reqd_qty;
					$wkResult[]  = $wkIssn;
				} else {
					//echo "break from pick item loop";
					break;
				}
			}
		}		
	
		//release memory
		ibase_free_result($Result);
	}
	// end of for
	//echo('picklines:' . print_r($wkResult,true));
        //echo("end:" . __FUNCTION__);
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
 * get Deadlock Limit 
 *
 * @param ibase_link $Link Connection to database
 * @return string
 */
function getDeadlockLimit ($Link  )
{
	$wkResult = "";
	$Query = "select p1.description    
                  from options p1
                  where p1.group_code = 'RECEIVE' AND p1.code='DEADLOCK' ";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Options!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkResult  = $Row[0];
	}
	//release memory
	ibase_free_result($Result);
	if ($wkResult == "") 
	{
		$wkResult  = "5";
	}
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
	global $wk_db_error;
	// transfer order from this device to conveyor device
	// from my device to device for company of order
	// object = order
	// location = from device
	// sub locn = to device
	// ref = comment
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	$my_location = $tran_device . "        ";
	$wk_to_device = getZoneDevice($Link, $wkOrder);
	$wk_sublocn = ($wk_to_device == "") ?  $tran_device : $wk_to_device;
	$my_source = 'SSBSSKSSS';
	$my_message = "";
	$wk_db_error = False;
	//$my_message = dotransaction("TRPK", "o", $wkOrder, $my_location, $wk_sublocn, "transfer to Conveyor:Finished Pick", 0, $my_source, $tran_user, $tran_device, "N");
	$my_message = dotransaction("TRPK", "o", $wkOrder, $my_location, $wk_sublocn, "transfer to Conveyor:Finished Pick:DD", 0, $my_source, $tran_user, $tran_device, "N");
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
	if ($wk_db_error )
	{
		$my_responsemessage = "Deadlock ";
	} else {
		if ($my_responsemessage == "")
		{
			$my_responsemessage = "Processed successfully ";
		}
	}
	//echo($my_responsemessage);
	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
	}
	return $my_responsemessage;
}
		

/**
 * update the customer_po_wo in the pick_order
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkDevice
 * @return array
 */
function doPKUF ($Link, $wkSOOrder, $wkPOOrder )
{
	global $wk_db_error;
	// update customer po wo for this order 
	// object = SO order
	// location = from device
	// ref = field to update | PO Order | comment
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	$my_location = $tran_device . "        ";
	$my_sublocn = "";
	//$wk_sublocn = ($wk_to_device == "") ?  $tran_device : $wk_to_device;
	$my_ref = "CUSTOMER_PO_WO|" . $wkPOOrder . "|";
	$my_source = 'SSBSSKSSS';
	$my_message = "";
	$wk_db_error = False;
	$my_message = dotransaction("PKUF", "O", $wkSOOrder, $my_location, $wk_sublocn, $my_ref, 0, $my_source, $tran_user, $tran_device, "N");
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
	if ($wk_db_error )
	{
		$my_responsemessage = "Deadlock ";
	} else {
		if ($my_responsemessage == "")
		{
			$my_responsemessage = "Processed successfully ";
		}
	}
	//echo($my_responsemessage);
	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
	}
	return $my_responsemessage;
}
		

/**
 * do the picking of the line passed
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkProduct the Product to use
 * @param string $wkIssn the ISSN to use in the pick
 * @param string $wkLine the details of the line from the getLines4Picks function
 * @return array
 */
function doPick ($Link, $wkProduct, $wkIssn, $wkLine , $wkPrinter, $wkLocation, $wkOrder )
{
	global $wk_db_error;
    //echo("start:" . __FUNCTION__);
    //echo("product:" . $wkProduct);
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	$wkResult = array();
	$wk_isok = "T";
	$wk_mymessage = "" ;
	$wk_PKAL = "";
	$wk_POAL = "";
	$wk_PKOL = "";
	$wk_PKOL2 = "";
	$wk_PKIL = "";
	$wk_TRPK = "";
	$wk_PKUF = "";

	$wk_trolley_device = "" ;
	$Query = "select first 1 device_id from sys_equip where device_type = 'TR' order by device_id  "; 
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read SYS_EQUIP!<BR>\n");
		exit();
	}
	if  ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_trolley_device = $Row[0];
	}
	//release memory
	ibase_free_result($Result);

	$wk_cn_location = "" ;
	$wk_cn_do_pkuf = "" ;
	//$Query = "select  pick_direct_delivery_location from control  "; 
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

	{
		//if ($wk_isok == "T") {
		{
// what is the trolley no
			$transaction_type = "PKAL";
			$my_object = $wkLine['PICK_LABEL_NO'] ;
			$my_source = 'SSSSSSSSS';
			$tran_tranclass = "F";
			$tran_tranclass = "M";
			$tran_qty = 0;
			$my_sublocn = $wk_trolley_device; /* the trolley no */
			$my_location = $tran_device  . 'T|' . $wkPrinter;
			$my_ref = $tran_user . "|" . $wkProduct ;

			$my_message = "";
			$wk_db_error = False;
			$my_message = dotransaction($transaction_type, $tran_tranclass, $my_object, $my_location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device,"N");
			if ($my_message > "") {
				list($my_mess_field, $my_mess_label) = explode("=", $my_message);
				$my_responsemessage = urldecode($my_mess_label) . " ";
			} else {
				$my_responsemessage = " ";
			}
			if ($wk_db_error )
			{
				$my_responsemessage = "Deadlock ";
			} else {
				if (($my_responsemessage == " ") or
		   	         ($my_responsemessage == ""))
				{
					$my_responsemessage = "Processed successfully ";
				}
			}
			if ($my_responsemessage == "Processed successfully ")
			{
				$my_responsemessage = "OK";	
			} else {
				$wk_isok = "F";
			}
			$wk_PKAL = $my_responsemessage;
			$wk_mymessage .= "PKAL:" . $my_responsemessage;
		}

		if ($wk_isok == "T") 
		{
			// poal
			/*
			object = order
			locn = locn to use
			ref = user|alloctate to device
			sub_locn_id = trolley to use
			*/
			$tran_qty = 0 ;
			$my_object = $wkLine['PICK_ORDER'];
			$my_sublocn = $wk_trolley_device ;
	
			$my_ref = $tran_user . "|" . $tran_device . "| assign order to location" ;
			$my_source = 'SSBSSKSSS';
			$transaction_type = "POAL";
			$tran_tranclass = "D";
			$my_location = '0000000000';

			$my_message = "";
			$wk_db_error = False;
			$my_message = dotransaction($transaction_type, $tran_tranclass, $my_object, $my_location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device,"N");
			if ($my_message > "") {
				list($my_mess_field, $my_mess_label) = explode("=", $my_message);
				$my_responsemessage = urldecode($my_mess_label) . " ";
			} else {
				$my_responsemessage = " ";
			}
			if ($wk_db_error )
			{
				$my_responsemessage = "Deadlock ";
			} else {
				if (($my_responsemessage == " ") or
			            ($my_responsemessage == ""))
				{
					$my_responsemessage = "Processed successfully ";
				}
			}
			if ($my_responsemessage == "Processed successfully ")
			{
				$my_responsemessage = "OK";	
			} else {
				$wk_isok = "F";
			}
			$wk_POAL = $my_responsemessage;
			$wk_mymessage .= "POAL:" . $my_responsemessage;
		}

		if ($wk_isok == "T") 
		{
			// pkol
			$tran_qty = $wkLine['QTY_TO_PICK'];
			$my_object = $wkIssn;
			$my_sublocn = $wkLine['PICK_LABEL_NO'] ;
	
			$my_ref = '';
			$my_source = 'SSBSSKSSS';
			$transaction_type = "PKOL";
			$tran_tranclass = "B";
			// what is the location - should be the location for GRNV
			$my_location = $wkLocation;

			$my_message = "";
			$wk_db_error = False;
			$my_message = dotransaction($transaction_type, $tran_tranclass, $my_object, $my_location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device,"N");
			if ($my_message > "") {
				list($my_mess_field, $my_mess_label) = explode("=", $my_message);
				$my_responsemessage = urldecode($my_mess_label) . " ";
			} else {
				$my_responsemessage = " ";
			}
			if ($wk_db_error )
			{
				$my_responsemessage = "Deadlock ";
			} else {
				if (($my_responsemessage == " ") or
			            ($my_responsemessage == ""))
				{
					$my_responsemessage = "Processed successfully ";
				}
			}
			if ($my_responsemessage == "Processed successfully ")
			{
				$my_responsemessage = "OK";	
			} else {
				$wk_isok = "F";
			}
			$wk_PKOL = $my_responsemessage;
			$wk_mymessage .= "PKOL:" . $my_responsemessage;
		}
		// if not all picked then the status will be PG 
		// need a zero qty PKOL with a reason for zero picked
		// in order to get the status to PL
		$wk_pi_status = "" ;
		$Query = "select  pick_line_status from pick_item where pick_label_no = '" . $wkLine['PICK_LABEL_NO'] . "' "; 
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Pick Item !<BR>\n");
			//exit();
		}
		if  ( ($Row = ibase_fetch_row($Result)) ) {
			$wk_pi_status = $Row[0];
		}
		//release memory
		ibase_free_result($Result);
		if ($wk_isok == "T") 
		{
			if ($wk_pi_status == "PG") {
				// pkol
				$tran_qty = 0;
				$my_object = $wkIssn;
				$my_sublocn = $wkLine['PICK_LABEL_NO'] ;
		
				$my_ref = ' No Stock left to Pick this';
				$my_source = 'SSBSSKSSS';
				$transaction_type = "PKOL";
				$tran_tranclass = "B";
				$my_location = "          ";
	
				$my_message = "";
				$wk_db_error = False;
				$my_message = dotransaction($transaction_type, $tran_tranclass, $my_object, $my_location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device,"N");
				if ($my_message > "") {
					list($my_mess_field, $my_mess_label) = explode("=", $my_message);
					$my_responsemessage = urldecode($my_mess_label) . " ";
				} else {
					$my_responsemessage = " ";
				}
				if ($wk_db_error )
				{
					$my_responsemessage = "Deadlock ";
				} else {
					if (($my_responsemessage == " ") or
				            ($my_responsemessage == ""))
					{
						$my_responsemessage = "Processed successfully ";
					}
				}
				if ($my_responsemessage == "Processed successfully ")
				{
					$my_responsemessage = "OK";	
				} else {
					$wk_isok = "F";
				}
				$wk_PKOL2 = $my_responsemessage;
				$wk_mymessage .= "PKOL:" . $my_responsemessage;
			}

		}

		if ($wk_isok == "T") 
		{
			// pkil
			$tran_qty = 0; 
			$my_object  = $wkLine['PICK_LABEL_NO'] ;
			$my_sublocn = $wkLine['PICK_LABEL_NO'] ;
	
			$my_ref = $tran_user . "|" . $tran_device . "| pick to locn";
			$my_source = 'SSBSSKSSS';
			$transaction_type = "PKIL";
			$tran_tranclass = "D";
			$my_location = $wk_cn_location;

			$my_message = "";
			$wk_db_error = False;
			$my_message = dotransaction($transaction_type, $tran_tranclass, $my_object, $my_location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device,"N");
			if ($my_message > "") {
				list($my_mess_field, $my_mess_label) = explode("=", $my_message);
				$my_responsemessage = urldecode($my_mess_label) . " ";
			} else {
				$my_responsemessage = " ";
			}
			if ($wk_db_error )
			{
				$my_responsemessage = "Deadlock ";
			} else {
				if (($my_responsemessage == " ") or
			            ($my_responsemessage == ""))
				{
					$my_responsemessage = "Processed successfully ";
				}
			}
			if ($my_responsemessage == "Processed successfully ")
			{
				$my_responsemessage = "OK";	
			} else {
				$wk_isok = "F";
			}
			$wk_PKIL = $my_responsemessage;
			$wk_mymessage .= "PKIL:" . $my_responsemessage;
		}
		// do trpk
		if ($wk_isok == "T") 
		{
			$my_responsemessage =  doTRPK ($Link, $wkLine['PICK_ORDER'] );
			if ($my_responsemessage == "Processed successfully ")
			{
				$my_responsemessage = "OK";	
			} else {
				$wk_isok = "F";
			}
			$wk_TRPK = $my_responsemessage;
			$wk_mymessage .= "TRPK:" . $my_responsemessage;
		}
		// do pkuf
		//if ($wk_isok == "T")  
		if (($wk_isok == "T") and ($wk_cn_do_pkuf == "T"))
		{
			$my_responsemessage =  doPKUF ($Link, $wkLine['PICK_ORDER'], $wkOrder );
			if ($my_responsemessage == "Processed successfully ")
			{
				$my_responsemessage = "OK";	
			} else {
				$wk_isok = "F";
			}
			$wk_PKUF = $my_responsemessage;
			$wk_mymessage .= "PKUF:" . $my_responsemessage;
		}
	}
	$wkResult['PKAL'] = $wk_PKAL ;
	$wkResult['POAL'] = $wk_POAL ;
	$wkResult['PKOL'] = $wk_PKOL ;
	$wkResult['PKOL2'] = $wk_PKOL2 ;
	$wkResult['PKIL'] = $wk_PKIL ;
	$wkResult['TRPK'] = $wk_TRPK ;
	$wkResult['PKUF'] = $wk_PKUF ;
	//echo('picklines:' . print_r($wkResult,true));
        //echo("end:" . __FUNCTION__);
	return $wkResult;
}


function errorHandler2( $errno, $errstr, $errfile, $errline, $errcontext)
{
	global $dbTran, $wk_db_error, $Link ;
	//$log = fopen('/tmp/transPONV.log' , 'a');
	$log = fopen('/data/tmp/transPONV.log' , 'a');
	$datetime  = strftime("%D %T "); 
	if (isset($_COOKIE['LoginUser']))
	{
		list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
		$userline = sprintf("%s  by %s on %s\n", $datetime, $tran_user, $tran_device);
	} else {
		$userline = sprintf("%s  \n", $datetime );
	}

	fwrite($log, $userline);
	fwrite($log, $errno);
	fwrite($log,"  ");
	fwrite($log, $errstr);
	fwrite($log,"  \n");
	fwrite($log, $errfile);
	fwrite($log," line ");
	fwrite($log, $errline);
	fwrite($log,"  \n");
	fwrite($log, print_r($errcontext, true));
	fwrite($log,"\n");
	fclose($log);
/*
	if  (($errstr like ' deadlock ')
	{
		need to rollback and try again
		then resume at the start of the pkal loop
	}
*/
	$wk_pos1 = strpos($errstr, ' deadlock ');
	if ($wk_pos1 !== FALSE)
	{
		$wk_db_error = True;
		//rollback
		ibase_rollback($dbTran);
		$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	}
}

// ============================================================================================================

if (isset($_COOKIE['LoginUser']))
{
	$tran_type = "GRNV";
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	include 'logme.php';
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	$tran_deadlock_limit = 5;
	
/*	
	if (isset($_COOKIE['BDCSData']))
	{
		list($received_qty, $type, $order, $line, $carrier, $vehicle, $container, $pallet_type, $pallet_qty, $consignment, $grn, $problem) = explode("|", $_COOKIE["BDCSData"]);
	}
*/

	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: verifyPO.php?message=Can+t+connect+to+DATABASE!"  );
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	$tran_deadlock_limit = getDeadlockLimit ($Link ) + 0;
	$received_qty = getBDCScookie($Link, $tran_device, "received_qty" );
	$type = getBDCScookie($Link, $tran_device, "type" );
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

	$toPickQty = getBDCScookie($Link, $tran_device, "pick_qty"  );

	if (isset($_POST['type']))
	{
		$type = $_POST['type'];
	}
	if (isset($_GET['type']))
	{
		$type = $_GET['type'];
	}
	if (isset($_POST['order']))
	{
		$order = $_POST['order'];
	}
	if (isset($_GET['order']))
	{
		$order = $_GET['order'];
	}
	if (isset($_POST['line']))
	{
		$line = $_POST['line'];
	}
	if (isset($_GET['line']))
	{
		$line = $_GET['line'];
	}
	if (isset($_POST['carrier']))
	{
		$carrier = $_POST['carrier'];
	}
	if (isset($_GET['carrier']))
	{
		$carrier = $_GET['carrier'];
	}
	if (isset($_POST['vehicle']))
	{
		$vehicle = $_POST['vehicle'];
	}
	if (isset($_GET['vehicle']))
	{
		$vehicle = $_GET['vehicle'];
	}
	if (isset($_POST['container']))
	{
		$container = $_POST['container'];
	}
	if (isset($_GET['container']))
	{
		$container = $_GET['container'];
	}
	if (isset($_POST['pallet_type']))
	{
		$pallet_type = $_POST['pallet_type'];
	}
	if (isset($_GET['pallet_type']))
	{
		$pallet_type = $_GET['pallet_type'];
	}
	if (isset($_POST['pallet_qty']))
	{
		$pallet_qty = $_POST['pallet_qty'];
	}
	if (isset($_GET['pallet_qty']))
	{
		$pallet_qty = $_GET['pallet_qty'];
	}
	if (isset($_POST['received_qty']))
	{
		$received_qty = $_POST['received_qty'];
	}
	if (isset($_GET['received_qty']))
	{
		$received_qty = $_GET['received_qty'];
	}
	if (isset($_POST['consignment']))
	{
		$consignment = $_POST['consignment'];
	}
	if (isset($_GET['consignment']))
	{
		$consignment = $_GET['consignment'];
	}
	if (isset($_POST['grn']))
	{
		$grn = $_POST['grn'];
	}
	if (isset($_GET['grn']))
	{
		$grn = $_GET['grn'];
	}
	if (isset($_POST['printer']))
	{
		$printer = $_POST['printer'];
	}
	if (isset($_GET['printer']))
	{
		$printer = $_GET['printer'];
	}
	if (!isset($printer))
	{
		$printer = "PA";
	}

	if (isset($_POST['location']))
	{
		$location = $_POST['location'];
	}
	if (isset($_GET['location']))
	{
		$location = $_GET['location'];
	}
	if (isset($_POST['received_ssn_qty']))
	{
		$received_ssn_qty = $_POST['received_ssn_qty'];
	}
	if (isset($_GET['received_ssn_qty']))
	{
		$received_ssn_qty = $_GET['received_ssn_qty'];
	}
	if (!isset($received_ssn_qty))
	{
		$received_ssn_qty = 1;
	}
	if (isset($_POST['uom']))
	{
		$uom = $_POST['uom'];
	}
	if (isset($_GET['uom']))
	{
		$uom = $_GET['uom'];
	}
	if (!isset($uom))
	{
		$uom = "EA";
	}
	if (isset($_POST['product']))
	{
		$product = $_POST['product'];
	}
	if (isset($_GET['product']))
	{
		$product = $_GET['product'];
	}
	if (isset($_POST['retfrom']))
	{
		$retfrom = $_POST['retfrom'];
	}
	if (isset($_GET['retfrom']))
	{
		$retfrom = $_GET['retfrom'];
	}
	if (!isset($retfrom))
	{
		$retfrom = "";
	}
	if (isset($_POST['complete']))
	{
		$complete = $_POST['complete'];
	}
	if (isset($_GET['complete']))
	{
		$complete = $_GET['complete'];
	}
	if (!isset($complete))
	{
		$complete = "";
	}

	if (isset($_POST['completeno']))
	{
		$complete = "N";
	}
	if (isset($_GET['completeno']))
	{
		$complete = "N";
	}
	if (isset($_POST['completeyes']))
	{
		$complete = "Y";
	}
	if (isset($_GET['completeyes']))
	{
		$complete = "Y";
	}

	if (isset($_POST['label_qty1']))
	{
		$label_qty1 = $_POST['label_qty1'];
	}
	if (isset($_GET['label_qty1']))
	{
		$label_qty1 = $_GET['label_qty1'];
	}
	if (isset($_POST['label_qty2']))
	{
		$label_qty2 = $_POST['label_qty2'];
	}
	if (isset($_GET['label_qty2']))
	{
		$label_qty2 = $_GET['label_qty2'];
	}
	if (isset($_POST['label_qty3']))
	{
		$label_qty3 = $_POST['label_qty3'];
	}
	if (isset($_GET['label_qty3']))
	{
		$label_qty3 = $_GET['label_qty3'];
	}
	if (isset($_POST['ssn_qty1']))
	{
		$ssn_qty1 = $_POST['ssn_qty1'];
	}
	if (isset($_GET['ssn_qty1']))
	{
		$ssn_qty1 = $_GET['ssn_qty1'];
	}
	if (isset($_POST['ssn_qty2']))
	{
		$ssn_qty2 = $_POST['ssn_qty2'];
	}
	if (isset($_GET['ssn_qty2']))
	{
		$ssn_qty2 = $_GET['ssn_qty2'];
	}
	if (isset($_POST['ssn_qty3']))
	{
		$ssn_qty3 = $_POST['ssn_qty3'];
	}
	if (isset($_GET['ssn_qty3']))
	{
		$ssn_qty3 = $_GET['ssn_qty3'];
	}
	if (isset($_POST['class']))
	{
		$class = $_POST['class'];
	}
	if (isset($_GET['class']))
	{
		$class = $_GET['class'];
	}
	if (!isset($class))
	{
		$class = "P";
	}
	if (isset($_POST['problem']))
	{
		$problem = $_POST['problem'];
	}
	if (isset($_GET['problem']))
	{
		$problem = $_GET['problem'];
	}

	//$Query = "select receive_direct_delivery, receive_issn_original_qty  from control  "; 
	$Query = "select receive_direct_delivery, receive_issn_original_qty, use_sale_channel  from control  "; 
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Control!<BR>\n");
		exit();
	}
	$wkDirectDelivery = "F";
	$wkDirectDeliveryDefaultIssnQty = null;
	$wkSaleChannel = "F";
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkDirectDelivery = $Row[0];
		$wkDirectDeliveryDefIssnQty = $Row[1];
		$wkSaleChannel = $Row[2];
	}
	//release memory
	ibase_free_result($Result);
	if (is_null($wkDirectDelivery )) {
		$wkDirectDelivery = "F";
	}
	if (is_null($wkSaleChannel )) {
		$wkSaleChannel = "F";
	}

	$Query = "select purchase_order.company_id from purchase_order  where purchase_order.purchase_order = '$order'  "; 
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read PO !<BR>\n");
	}
	$product_company = "";
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$product_company = $Row[0];
	}
	//release memory
	ibase_free_result($Result);

	$wkOrderSaleChannel = "NONE";
	if ($wkSaleChannel == "T") {
		$Query = "select purchase_order.po_sale_channel_code  from purchase_order  where purchase_order.purchase_order = '" . $order . "' "; 
		$Query = "select purchase_order_line.po_sale_channel_code  from purchase_order_line  where purchase_order_line.purchase_order = '" . $order . "' and purchase_order_line.po_line = '" . $line . "' "; 
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read PO Order!<BR>\n");
			exit();
		}
		while ( ($Row = ibase_fetch_row($Result)) ) {
			$wkOrderSaleChannel = $Row[0];
		}
		//release memory
		ibase_free_result($Result);
		if (is_null($wkOrderSaleChannel )) {
			$wkOrderSaleChannel = "NONE";
		}
		if ($wkOrderSaleChannel == "" ) {
			$wkOrderSaleChannel = "NONE";
		}
	} 

	$wkPRRecordId = 0;
	$wkPRAvailableQty = 0;
	$wkPRReserveQty = 0;
	$wkPRRecord = array();
	if ($wkSaleChannel == "T" and $wkOrderSaleChannel <> "NONE") {
		$Query = "select record_id,available_qty,reserved_qty  from prod_reservation where prod_id='" . $product . "' and sale_channel_code='" . $wkOrderSaleChannel . "' "; 
		$Query = "select record_id,pr_available_qty,pr_reserved_qty  from prod_reservation where pr_prod_id='" . $product . "' and pr_sale_channel_code='" . $wkOrderSaleChannel . "' "; 
		$Query = "select record_id,pr_available_qty,pr_reserved_qty  from prod_reservation where pr_prod_id='" . $product . "' and pr_sale_channel_code='" . $wkOrderSaleChannel . "' and pr_reservation_status='OP' "; 
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read PO Order!<BR>\n");
			exit();
		}
		while ( ($Row = ibase_fetch_row($Result)) ) {
			$wkPRRecordId = $Row[0];
			$wkPRAvailableQty = $Row[1];
			$wkPRReserveQty = $Row[2];
		}
		//release memory
		ibase_free_result($Result);
		if (is_null($wkPRRecordID )) {
			$wkPRRecordID = 0 ;
		}
		if (is_null($wkPRAvailableQty )) {
			$wkPRAvailableQty = 0 ;
		}
		if (is_null($wkPRReserveQty )) {
			$wkPRReserveQty = 0 ;
		}
		$wkIssn = array();
		$wkIssn['PR_RECORD_ID']  = $wkPRRecordId;
		$wkIssn['PR_AVAILABLE_QTY']  = $wkPRAvailableQty;
		$wkIssn['PR_RESERVED_QTY']  = $wkPRReserveQty;
		$wkIssn['PR_SALE_CHANNEL_CODE']  =  $wkOrderSaleChannel  ;
		/* $wkPRRecord[] = array(
			$wkPRRecordId  ,
			$wkPRAvailableQty  ,
			$wkPRReserveQty ); */
		$wkPRRecord[] = array($wkIssn);
	}
	if ($wkSaleChannel == "T" ) {
		$Query = "select record_id,pr_available_qty,pr_reserved_qty, pr_sale_channel_code  from prod_reservation where pr_prod_id='" . $product . "' and pr_sale_channel_code<>'" . $wkOrderSaleChannel . "' and pr_reservation_status = 'OP' order by pr_reservation_priority, pr_sale_channel_code "; 
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read PO Order Reservation Others!<BR>\n");
			exit();
		}
		while ( ($Row = ibase_fetch_row($Result)) ) {
			$wkPRRecordId2 = 0;
			$wkPRAvailableQty2 = 0;
			$wkPRReserveQty2 = 0;
			$wkPRSaleChannelCode2 = "NONE";
			$wkPRRecordId2 = $Row[0];
			$wkPRAvailableQty2 = $Row[1];
			$wkPRReserveQty2 = $Row[2];
			$wkPRSaleChannelCode2 = $Row[3];
			if (is_null($wkPRRecordID2 )) {
				$wkPRRecordID2 = 0 ;
			}
			if (is_null($wkPRAvailableQty2 )) {
				$wkPRAvailableQty2 = 0 ;
			}
			if (is_null($wkPRReserveQty2 )) {
				$wkPRReserveQty2 = 0 ;
			}
			$wkIssn = array();
			$wkIssn['PR_RECORD_ID']  = $wkPRRecordId2;
			$wkIssn['PR_AVAILABLE_QTY']  = $wkPRAvailableQty2;
			$wkIssn['PR_RESERVED_QTY']  = $wkPRReserveQty2;
			$wkIssn['PR_SALE_CHANNEL_CODE']  =  $wkPRSaleChannelCode2  ;
			/* $wkPRRecord[] = array(
				$wkPRRecordID2  ,
				$wkPRAvailableQty2  ,
				$wkPRReserveQty2 ); */
			$wkPRRecord[] = array($wkIssn);
		}
		//release memory
		ibase_free_result($Result);
	}
	// add the none entry last
	$wkIssn = array();
	$wkIssn['PR_RECORD_ID']  = 0;
	$wkIssn['PR_AVAILABLE_QTY']  = 0;
	$wkIssn['PR_RESERVED_QTY']  = 0;
	$wkIssn['PR_SALE_CHANNEL_CODE']  =  "NONE" ;
	$wkPRRecord[] = array($wkIssn);

	$wk_db_error = False;
	set_error_handler('errorHandler2');
	$wk_db_error_count = 0;

	if ($wkDirectDelivery == "T") {
		if (is_null($toPickQty ))
		{
			$toPickQty = 0;
		}
  		$label_qty3 = 1;
  		$ssn_qty3 = min($toPickQty, $received_ssn_qty); /* qty required for picks */ 
  		$label_qty1 =  floor(( $received_ssn_qty - $ssn_qty3 ) / $wkDirectDeliveryDefIssnQty) ;  /* qty remaining for issn labels */
  		$ssn_qty1 = $wkDirectDeliveryDefIssnQty ;
		$label_qty2 = ($received_ssn_qty - $ssn_qty3 - ($label_qty1 * $ssn_qty1));
		$ssn_qty2 = 1;

/*
  		$label_qty1 = 1;
  		$ssn_qty1 = min($toPickQty, $received_ssn_qty); -* qty required for picks *- 
  		$label_qty2 =  ( $received_ssn_qty - $ssn_qty1 ) / $wkDirectDeliveryDefIssnQty ;  -* qty remaining for issn labels *-
  		$ssn_qty2 = $wkDirectDeliveryDefIssnQty ;
*/

	}

	$my_object = '';
		
	// $my_object = sprintf("%-10.10s%-10.10s", $retfrom, $owner) ;
	$my_object = $product ;
/*
	$location = "";
*/
	$my_sublocn = $grn;

	$my_ref = '';
	$my_ref = $type;
	$my_ref .= '|' . $order;
	// $my_ref .= '|' . $retfrom;
	$my_ref .= '|' . $line;
	$my_ref .= '|' . $label_qty1;
	$my_ref .= '|' . $ssn_qty1;
	$my_ref .= '|' . $label_qty2;
	$my_ref .= '|' . $ssn_qty2;
	$my_ref .= '|' . substr($printer,1,1);
	if ($wkDirectDelivery == "T") {
		$my_ref .= '|' . $label_qty3;
		$my_ref .= '|' . $ssn_qty3;
	}
	$my_source = 'SSBSSKSSS';
	$tran_tranclass = $class;
	$tran_qty = $received_ssn_qty;

	setBDCScookie($Link, $tran_device, "retfrom", $retfrom);
	setBDCScookie($Link, $tran_device, "product", $product);
	setBDCScookie($Link, $tran_device, "label_qty1", $label_qty1);
	setBDCScookie($Link, $tran_device, "ssn_qty1", $ssn_qty1);
	setBDCScookie($Link, $tran_device, "label_qty2", $label_qty2);
	setBDCScookie($Link, $tran_device, "label_qty2", $ssn_qty2 );
	setBDCScookie($Link, $tran_device, "printer", $printer);
	setBDCScookie($Link, $tran_device, "received_ssn_qty", $received_ssn_qty);
	setBDCScookie($Link, $tran_device, "location", $location);
	setBDCScookie($Link, $tran_device, "uom", $uom);
	setBDCScookie($Link, $tran_device, "receivecomplete", $complete);
	include("transaction.php");
	$my_message = "";
	/* include("transaction.php"); */
	//$my_message = dotransaction_response($tran_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
	$my_message = dotransaction_response($tran_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, $product, $product_company);
	if ($my_message > "")
	{
		list($my_mess_field, $my_mess_label) = explode("=", $my_message);
		$my_responsemessage = urldecode($my_mess_label) . " ";
	}
	else
	{
		$my_responsemessage = "";
	}
	//if control.generate_label_text = 'F' error text is 10001011|1|5||||PA|Processed successfully  
	//if control.generate_label_text = 'T' error text is Processed successfully  
	$my_result = explode('|', $my_responsemessage);
	//if ($my_responsemessage <> "Processed successfully ")
	if ($my_result[sizeof($my_result) - 1] <> "Processed successfully ")
	{
		//$message .= $my_responsemessage;
		//echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
		header("Location: verifyPO.php?" . $my_message );
		exit();
	}

	if ($problem > "")
	{
		$tran_qty = 0;
		$tran_ref_x = "|" . $problem;
		if (strlen($tran_ref_x) > 80)
		{
			$tran_ref_x = substr($tran_ref_x, 0, 80);
		}
		$my_object = substr($tran_ref_x,0,30);
		//$location = substr($tran_ref_x,30,10);
		$my_location = substr($tran_ref_x,30,10);
		$my_sublocn = $grn;
		$my_ref = substr($tran_ref_x,40,40) ;
	
		$my_message = "";
		/* include("transaction.php"); */
		$my_message = dotransaction_response("GRNC", "C", $my_object, $my_location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
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
			//$message .= $my_responsemessage;
			//echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
			header("Location: verifyPO.php?" . $my_message );
			exit();
		}
	}
	// do a commit point here
	ibase_commit($dbTran);
	// now have issn and ssns
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	// if the control.generate_label_text is 'F' then must create the labels
	// for issn's with no label date
	// for this grn
	//echo('my_result:' . print_r($my_result,true));
	$errorText = '';
		/* now the result array has
		1st set start issn
		1st set issn qty 
		1st set qty of labels
		2nd set start issn
		2nd set issn qty 
		2nd set qty of labels
		3rd set start issn
		3rd set issn qty 
		3rd set qty of labels
		printer
		message response
		*/
	if (sizeof($my_result) > 1)
	{
		$printerIp = getPrinterIp($Link, $printer);
            	$printerDir = getPrinterDir($Link, $printer);
		$result = $my_result;
                require_once 'Printer.php';

		// need printer ip and printer id and working directory
                $p = new Printer($printerIp);

                $p->data['printer_id'] = $printer;
                $p->data['title_1'] = "";
                $p->data['version'] = "";
		// need  a start issn
                $p->data['issn'] = $result[0];
                $p->data['ISSN.SSN_ID'] = $p->data['issn'];
                $p->data['issnlabelprefix'] = substr($result[0],0,2);
                $p->data['issnlabelsuffix'] = substr($result[0],2,strlen($result[0]) - 2);
                $p->data['qty'] = $result[2];
                $p->data['userid'] = $tran_user;
                $p->data['now'] = date('d/m/y H:i:s');
                $tpl = "";
		if ($result[1] != '')
		{
                	$q = getISSNLabel($Link, $p );
                        for ($i = 0; $i < $result[1]; $i++) {
			            {
                                    	$save = fopen($printerDir .
                                                  $p->data['issn'] . '_ISSN.prn', 'w');
                                    	//if (!$p->sysLabel($Link, $printer, "ISSN", 1))
                                    	if (!$p->sysLabel($Link, $printer, "ISSN_PRODUCT", 1))
                                    	{
                                        	$p->send($tpl, $save);
                                    	}
                                    	$wk_suffix = "1" . $p->data['issnlabelsuffix'];
                                    	$wk_suffix++;
                                    	$p->data['issnlabelsuffix'] = substr($wk_suffix,1);
                                    	$p->data['issn'] = $p->data['issnlabelprefix'] .  $p->data['issnlabelsuffix']; 
                                    	$p->data['ISSN.SSN_ID'] = $p->data['issn'];
                                    	fclose($save);
				    }
                        }
		}
		if ($result[4] != '')
		{
                         $p->data['issn'] = $result[3];
                         $p->data['ISSN.SSN_ID'] = $p->data['issn'];
                         $p->data['issnlabelprefix'] = substr($result[3],0,2);
                         $p->data['issnlabelsuffix'] = substr($result[3],2,strlen($result[3]) - 2);
                         $p->data['qty'] = $result[5];
                         $p->data['ISSN.CURRENT_QTY'] = $p->data['qty'] ;
			if ($result[1] == '')
			{
                		$q = getISSNLabel($Link, $p );
			}
                         for ($i = 0; $i < $result[4]; $i++) {
                               $save = fopen($printerDir .
                                              $p->data['issn'] . '_ISSN-new.prn', 'w');
                                //if (!$p->sysLabel($Link, $printer, "ISSN", 1))
                                if (!$p->sysLabel($Link, $printer, "ISSN_PRODUCT", 1))
				{
                                	$p->send($tpl, $save);
				}
                                $wk_suffix = "1" . $p->data['issnlabelsuffix'];
                                $wk_suffix++;
                                $p->data['issnlabelsuffix'] = substr($wk_suffix,1);
                                $p->data['issn'] = $p->data['issnlabelprefix'] .  $p->data['issnlabelsuffix']; 
                                $p->data['ISSN.SSN_ID'] = $p->data['issn'];
                                fclose($save);
                         }
		}

		if ($result[7] != '')
		{
                         $p->data['issn'] = $result[6];
                         $p->data['ISSN.SSN_ID'] = $p->data['issn'];
                         $p->data['issnlabelprefix'] = substr($result[6],0,2);
                         $p->data['issnlabelsuffix'] = substr($result[6],2,strlen($result[3]) - 2);
                         $p->data['qty'] = $result[8];
                         $p->data['ISSN.CURRENT_QTY'] = $p->data['qty'] ;
			if (($result[1] == '') and ($result[4] == ''))
			{
                		$q = getISSNLabel($Link, $p );
			}
                        for ($i = 0; $i < $result[7]; $i++) {
			            if ($wkDirectDelivery == "T")
			            {
                			$l = new Printer($printerIp);
                			$l->data['printer_id'] = $printer;
                			$l->data['issn'] = $p->data['issn'];
                			$l->data['ISSN.SSN_ID'] = $p->data['issn'];
                			$l->data['issnlabelprefix'] =  $p->data['issnlabelprefix'] ;
                			$l->data['issnlabelsuffix'] = $p->data['issnlabelsuffix'] ;
                			$l->data['qty'] =  $p->data['qty'] ;
                			$l->data['userid'] = $tran_user;
                			$l->data['now'] = date('d/m/y H:i:s');
                			$l->data['ISSN.PROD_ID'] = $product;
			            	// prev screen has decided that there are picked to do
					// then pass the qty and product to the orders to get
					// then at the end print the pick_labels used
					// have issn to use in $p->data['issn']
	 				$wk_picks_qty = $p->data['qty'] ;
					// start while 
					// no  dead locks
					// do a commit point here
					ibase_commit($dbTran);
					$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
					$wk_db_error_count = 0;
					$wk_loop = 1;
					$wk_picks_qty_notdone = 0;
					$wkPicksQtyNotDone = array();
					//while ( ($wk_loop <= 1 ) and ($wk_db_error_count <= 5) ) :
					while ( ($wk_loop <= 1 ) and ($wk_db_error_count <= $tran_deadlock_limit ) ) :
						//$wkPicks = getLines4Picks($Link,  $product, $wk_picks_qty, $location, $product_company) ;
				/*		$wkPicks = getLines4Picks($Link,  
                                                                          $product, 
                                                                          $wk_picks_qty, 
                                                                          $location, 
                                                                          $product_company, 
                                                                          $wkSaleChannel, 
                                                                          $wkOrderSaleChannel,
                                                                          $wkPRRecordId); */
						$wkPicks = getLines4Picks($Link,  
                                                                          $product, 
                                                                          $wk_picks_qty, 
                                                                          $location, 
                                                                          $product_company, 
                                                                          $wkSaleChannel, 
                                                                          $wkOrderSaleChannel,
                                                                          $wkPRRecord);
						$wk_onpick2_DEADLOCK = False;
						foreach ($wkPicks as $wk_onpick_id => $wk_onpick2)
						{
							//echo(print_r($wk_onpick2,true));
							// pick this line
							$wkPickResult = doPick($Link,  $product, $p->data['issn'], $wk_onpick2 , $printer, $location, $order ) ;
							if ( ($wkPickResult['PKAL'] == "Deadlock") || 
							     ($wkPickResult['POAL'] == "Deadlock") ||
							     ($wkPickResult['PKOL'] == "Deadlock") ||
							     ($wkPickResult['PKIL'] == "Deadlock") ||
							     ($wkPickResult['TRPK'] == "Deadlock") ) {
								// if any of the responces were deadlock then must try to redo it from getting the lines onwards
								// increment error count
								$wk_db_error_count++;
								$wk_onpick2_DEADLOCK = True;
								//if ($wk_db_error_count <= 5) {
								//if ($wk_db_error_count <= $tran_deadlock_limit) {
								// do the rollback - since we had a deadlock
									//rollback;
									ibase_rollback($dbTran);
									// use new transaction
									$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
								//}
								if ($wk_db_error_count <= $tran_deadlock_limit) {
									break;
								}
							}
						} // end of for picks
						// the label print
						// if no deadlocks do labels
						// if a deadlock and <= limit 
						//      then weve done a rollback - so get next lot of picks
						// if a deadlock and > limit 
						//      then weve run out  - so print a standard label
						if (($wk_onpick2_DEADLOCK == False) or 
						    ($wk_db_error_count > $tran_deadlock_limit)) {
							foreach ($wkPicks as $wk_onpick_id => $wk_onpick2)
							{
								// the label print
								$l->data['PICK_ITEM.PICK_ORDER'] = $wk_onpick2['PICK_ORDER'] ;
								$l->data['PICK_ITEM.PICK_LABEL_NO'] = $wk_onpick2['PICK_LABEL_NO'] ;
	        	        				$m = getPickLabel($Link, $l );
								//echo(print_r($m,true));
	                        	            		$save = fopen($printerDir .
	                                	                  $m->data['PICK_ITEM.PICK_LABEL_NO'] . '_PICK.prn', 'w');
								//if ($wk_db_error_count > 5) {
								if ($wk_db_error_count > $tran_deadlock_limit) {
		               	                     			if (!$m->sysLabel($Link, $printer, "ISSN_PRODUCT", 1))
	        	                  	          		{
										$wk_picks_qty_notdone += $wk_onpick2['QTY_TO_PICK'];
										if (isset($wkPicksQtyNotDone[$wk_onpick2['PR_RECORD_ID']]))
										{
											$wkPicksQtyNotDone[$wk_onpick2['PR_RECORD_ID']] += $wk_onpick2['QTY_TO_PICK'];
										} else {
											$wkPicksQtyNotDone[$wk_onpick2['PR_RECORD_ID']] = $wk_onpick2['QTY_TO_PICK'];
										} 
	                	                	        		$m->send($tpl, $save);
	                        	            			}
								} else {
	                                    				if (!$m->sysLabel($Link, $printer, "PICK_LABEL", 1))
	                          	          			{
	                                	        			$m->send($tpl, $save);
	                    	                			}
	                        	            		}
	                                	    		fclose($save);
							} // end for
						} // end if 
						//if ($wk_db_error_count <= 5) {
						if ($wk_db_error_count <= $tran_deadlock_limit) {
							$wk_loop = $wk_loop + 1;
							$wk_db_error_count = 0;
						}
					// end while
					endwhile;
                                    	$wk_suffix = "1" . $p->data['issnlabelsuffix'];
                                    	$wk_suffix++;
                                    	$p->data['issnlabelsuffix'] = substr($wk_suffix,1);
                                    	$p->data['issn'] = $p->data['issnlabelprefix'] .  $p->data['issnlabelsuffix']; 
                                    	$p->data['ISSN.SSN_ID'] = $p->data['issn'];
					// the pkal would reduce the available_qty by the expected pick amount 
					// now qty picked  is now $wk_picks_qty - $wk_picks_qty_notdone
					// so now increase the prod_reservation record available_qty by the qty notdone 
					if ($wkSaleChannel == "T") {
/*
						if ($wkPRRecordId <> 0) {
							if ($wk_picks_qty_notdone <> 0) {
								$Query = "update prod_reservation set available_qty=available_qty + " . $wk_picks_qty_notdone . ", pr_reservation_status='OP' where record_id = '" . $wkPRRecordID . "'"; 
								$Query = "update prod_reservation set pr_available_qty=pr_available_qty + " . $wk_picks_qty_notdone . ", pr_reservation_status='OP' where record_id = '" . $wkPRRecordId . "'"; 
								//echo($Query);
								if (!($Result = ibase_query($Link, $Query)))
								{
									echo("Unable to Update PROD_RESERVATION!<BR>\n");
								}
							}
						}
*/
						foreach ($wkPicksQtyNotDone as $wkNotDone_Id => $wkNotDone_qty)
						{
							if ($wkNotDone_Id <> 0) {
								if ($wkNotDone_qty <> 0) {
									$Query = "update prod_reservation set pr_available_qty=pr_available_qty + " . $wkNotdone_qty . ", pr_reservation_status='OP' where record_id = '" . $wkNotDone_Id . "'"; 
									//echo($Query);
									if (!($Result = ibase_query($Link, $Query)))
									{
										echo("Unable to Update PROD_RESERVATION!<BR>\n");
									}
								}
							}
						}
					}
			            } else {
                                    	$save = fopen($printerDir .
                                                  $p->data['issn'] . '_ISSN.prn', 'w');
                                    	//if (!$p->sysLabel($Link, $printer, "ISSN", 1))
                                    	if (!$p->sysLabel($Link, $printer, "ISSN_PRODUCT", 1))
                                    	{
                                        	$p->send($tpl, $save);
                                    	}
                                    	$wk_suffix = "1" . $p->data['issnlabelsuffix'];
                                    	$wk_suffix++;
                                    	$p->data['issnlabelsuffix'] = substr($wk_suffix,1);
                                    	$p->data['issn'] = $p->data['issnlabelprefix'] .  $p->data['issnlabelsuffix']; 
                                    	$p->data['ISSN.SSN_ID'] = $p->data['issn'];
                                    	fclose($save);
				    }
                        }
		}
		$errorText = getBDCScookie($Link, $tran_device, "LabelErrorText" );
		//echo $errorText;
	}
		
	//commit
	ibase_commit($dbTran);
	
	$_SESSION['TRANSPONV_PROCESS'] = "FINISHED";
	//phpinfo();
	//die;
	//want to go to 
	if ($complete == "Y" )
	{
		header("Location: receive_menu.php" );
		// include "receive_menu.php" ;
	}
	else
	{
		//header("Location: getdelivery.php" );
		// include "getdelivery.php" ;
		header("Location: getPOLine.php" );
		// choose the next line to work on
	}
}
?>
