<?php
session_start();

if (isset($_COOKIE['LoginUser']))
{
	$tran_type = "PKOL";
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: pick_Menu.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	require_once('logme.php');
	require_once('transaction.php');
	
// ================================================================================================================
// functions

/**
 * get Label Fields for Product label
 *
 * @param ibase_link $Link Connection to database
 * @param printer object $p 
 * @return string
 */
function getProductLabel($Link, $p )
{
    //echo("start:" . __FUNCTION__);

    $printerId = $p->data['printer_id'] ;
    //echo("printer:" . $printerId );
    $prodId = $p->data['PROD_PROFILE.PROD_ID'];
    $ssnId = $p->data['ISSN.SSN_ID'];
    $pickLabelNo = $p->data['PICK_ITEM.PICK_LABEL_NO'];
    $labelQty = $p->data['labelQty'];
    $sql = "SELECT WK_LABEL_DATA  FROM ADD_PRODUCT_LABEL (?, ?, ?, ?, ?) ";
    $query = ibase_prepare($Link, $sql);
    $output = false;
        
    if (false !== ($result = ibase_execute($query, $printerId, $prodId, $pickLabelNo, $ssnId, $labelQty ))) {
        if (false !== ($row = ibase_fetch_row($result))) {
            $output = $row[0];
        } else {
            $output = false;
        }
        ibase_free_result($result);
    }
    if ($output !== FALSE) {
        $wkParams = explode ("|", $output );
	foreach ($wkParams as $k => $v) {
            $wkParams2 = explode("=", $v,2);
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
    $sql = 'SELECT IP_ADDRESS FROM SYS_EQUIP WHERE  DEVICE_ID = ?';
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
    $sql = 'SELECT WORKING_DIRECTORY FROM SYS_EQUIP WHERE  DEVICE_ID = ?';
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
 * get Pick Lines  on the Device for a specific product
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkDevice
 * @return array
 */
function getLineonDevice ($Link, $wkDevice, $wkProduct )
{
	// device  = tran_device
	$wkResult = array();
	$Query = "select p2.pick_order, p2.pick_label_no, sum(p1.qty_picked), p2.pick_order_qty, p2.picked_qty   
                  from pick_item p2 
                  join pick_order p3 on p2.pick_order = p3.pick_order
                  left outer join pick_item_detail p1 on p2.pick_label_no = p1.pick_label_no
                  and   p1.pick_detail_status in ( 'PL','PG','AL') 
                  and   p1.device_id = '" . $wkDevice . "'
                  where p2.device_id = '" . $wkDevice . "'
                  and   p2.pick_line_status in ('PL','PG','AL') 
                  and   p2.prod_id  = '" . $wkProduct . "'
                  group by p2.pick_order, p2.pick_label_no, p2.pick_order_qty, p2.picked_qty ";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Pick Items!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkLine = array();
		$wkLine['PICK_ORDER']  = $Row[0];
		$wkLine['PICK_LABEL_NO']  = $Row[1];
		$wkLine['QTY_PICKED']  = $Row[2];
		$wkLine['PICK_ORDER_QTY']  = $Row[3];
		$wkLine['PICKED_QTY']  = $Row[4];
		$wkResult[]  = $wkLine;
	}
	//release memory
	ibase_free_result($Result);
	//echo('lines:' . print_r($wkResult,true));
	return $wkResult;
}


/**
 * check that have picked at least 1 none zero qty issn for this order
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkOrder
 * @param string $wkLocation
 * @param string $wkDevice
 * @return bootlean
for
zero qty to pick
if there are on the device any non zero qty issn's attached to pick item details
then they have done PKOLs but no PKIL yet since haven't picked then all
 */
function checkHaveItems ($Link, $wkOrder, $wkDevice )
{
	//echo __FUNCTION__;
	//echo "order:". $wkOrder;
	//echo "device:". $wkDevice;
	// device  = tran_device
	$wkResult = False;
	$Query = "select p2.pick_order ,i1.ssn_id 
                  from issn i1 
                  join pick_item_detail p2 on p2.ssn_id = i1.ssn_id
                  where i1.locn_id = '" . $wkDevice . "'
                  and   i1.current_qty > 0 

                  and   p2.pick_detail_status in ( 'AL','PG')
                  and   p2.pick_order = '" . $wkOrder . "'
                   ";
	//echo $Query;
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read ISSNs!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkResult  = True;
	}
	//release memory
	ibase_free_result($Result);
	if ($wkResult)
	{
		//echo('result: True' );
	} else {
		//echo('result: False' );
	}
	return $wkResult;
}


// ====================================================================================================================

$wk_system_product_by = "NONE";
$Query = "select pick_trolley_product_by from control";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read from Control!<BR>\n");
	exit();
}
if ( ($Row = ibase_fetch_row($Result)) ) {
	$wk_system_product_by = $Row[0];
}
//release memory
ibase_free_result($Result);

// ====================================================================================================================
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	$ssn = '';
	$label_no = '';
	$order = '';
	$prod_no = '';
	$description = '';
	$uom = '';
	$order_qty = 0;
	$picked_qty = 0;
	$required_qty = 0;
	$scanned_ssn = '';
	
	$my_object = '';
	if (isset($_COOKIE['BDCSData']))
	{
		//list($label_no, $order, $ssn, $prod_no, $uom, $description, $required_qty, $location, $scanned_ssn, $picked_qty) = explode("|", $_COOKIE["BDCSData"] . "|||||||||||");
	}
	{
		$wk_cookie = getBDCScookie($Link, $tran_device, "BDCSData" );
		list($label_no, $order, $ssn, $prod_no, $uom, $description, $required_qty, $location, $scanned_ssn, $picked_qty) = explode("|", $wk_cookie);
	}
		
	$wk_pi_ssn_id  = "";
	$wk_pi_prod_id  = "";
	$Query = "select ssn_id, prod_id from pick_item where pick_label_no = '" . $label_no . "'";
	if (!($Result = ibase_query($Link, $Query)))
	{
	}
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_pi_ssn_id  = $Row[0];
		$wk_pi_prod_id  = $Row[1];
	}
	//release memory
	ibase_free_result($Result);

	if (($ssn <> "") and ($wk_pi_ssn_id == ""))
	{
		// ssn should be empty
		$ssn = "";
	}
	if (($prod_no <> "") and ($wk_pi_prod_id == ""))
	{
		// prod should be empty
		$prod_no = "";
	}
	$qty = $picked_qty;
	$remaining_qty = $picked_qty;
	if ($ssn <> "")
	{
		$my_object = $scanned_ssn;
		$my_sublocn = $label_no;
	}
	else
	{
		// a product
		if ( $wk_system_product_by == 'ISSN') {
			$my_object = $scanned_ssn;
			$my_sublocn = $label_no;
		} else {
			$my_object = $prod_no;
			$my_sublocn = "";
		}
	}

	
	//$location is the device
	$my_ref = '';
	if (isset($_POST['reference']))
	{
		$my_ref = $_POST['reference'];
	}
	if (isset($_GET['reference']))
	{
		$my_ref = $_GET['reference'];
	}
	if ($qty == 0 or $qty == '')
	{
		if ($my_ref == '')
		{
			$qty = 0;
			$remaining_qty = 0;
			$my_ref = "No Stock";
			/* no nostock reason was given 
			if picking multiple lines for this product but only have taken so much product
			need to be able to go get some more
			unless there is no more
			here the pick line status will be AL or PG or PL
			and picked_qty < pick_order_qty
			so change the status to be AL and go to getfromlocn
			*/
			if ($ssn <> "")
			{
				$wk_dummy = 1;
			}
			else
			{
				if ( $wk_system_product_by == 'ISSN') {
					$wk_dummy = 2;
				} else {
					$Query = "update pick_item set pick_line_status='AL' where prod_id='" . $prod_no . "' and device_id = '" . $tran_device . "' and pick_line_status = 'PL' and coalesce(picked_qty,0) <  pick_order_qty"; 
					if (!($Result = ibase_query($Link, $Query)))
					{
						// update failed
					}
					//release memory
					ibase_free_result($Result);
					// commit this
					ibase_commit($dbTran);
					header("Location: getfromlocn.php");
					exit;
				}
			}
		}
	}
	$my_source = 'SSBSSKSSS';
	$do_prod_by_issn = "F";
	if ($ssn <> "")
	{
		$tran_tranclass = "B";
	}
	else
	{
		if ( $wk_system_product_by == 'ISSN') {
			$tran_tranclass = "B";
			$do_prod_by_issn = "T";
		} else {
			$tran_tranclass = "P";
		}
	}

	$wk_HaveItems = checkHaveItems($Link, $order ,$tran_device) ;
	//echo "qty:" . $qty;
	if   ( ($qty > 0) or 
	     ( ($qty == 0) and ($wk_HaveItems == False) )  )
	{
		//echo "ok to process";
		if ($do_prod_by_issn == "F")
		{
			$tran_qty = $qty;
	
			/* write transaction */
			$my_message = "";
			//echo "do prod by issn is false";
			//echo ("dotransaction($tran_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, 'N')");
			$my_message = dotransaction($tran_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, "Y");
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
	
		} // end of do_prod_by_issn is false
		else {
			$message = "";
			// if do_prod_by_issn is true
			// create a list of pick_labels and orders and qtys that this issn could be for
			$wkLines =  getLineonDevice ($Link, $tran_device , $prod_no );
			// ie product lines 
			foreach ($wkLines as $wk_line_id => $wk_line)
			{
				//echo("<br>lineId" . $wk_line_id);
				//echo("line" . print_r($wk_line,true));
				//echo("<br>");
				$this_order_no = $wk_line['PICK_ORDER'];
				$this_label_no = $wk_line['PICK_LABEL_NO'];
				$qtyreqd = $wk_line['QTY_PICKED'];
				$pickQty = $wk_line['PICK_ORDER_QTY'];
				$pickedQty = $wk_line['PICKED_QTY'];
				if (is_null($pickedQty)) {
					$pickedQty = 0;
				}
				if (is_null($pickQty)) {
					$pickQty = 0;
				}
				$wk_qty_required = $pickQty - $pickedQty;
				$wk_this_qty = min($remaining_qty, $wk_qty_required );
				if ($wk_this_qty < 0) {
					$wk_this_qty = 0;
				}
	
				$tran_qty = $wk_this_qty;
				$my_sublocn = $this_label_no;
				if ($tran_qty > 0 or $my_ref <> "")
				{
					// have a qty or a no stock reason 
					/* write transaction */
					$my_message = "";
					//echo("dotransaction($tran_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, 'N')");
					$my_message = dotransaction($tran_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, "N");
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
						$message .= $my_responsemessage;
						//header("Location: pick_Menu.php?" . $my_message);
						//exit();
					} else {
						$remaining_qty = $remaining_qty - $wk_this_qty;
						if ($remaining_qty < 0) {
							$remaining_qty = 0;
						}
					}
				}
				//release memory
				//if (isset($Result))
				//{
					//ibase_free_result($Result); 
				//}
			}
			//commit
			ibase_commit($dbTran);
		}

	}
	if   ( ($qty == 0) and ($wk_HaveItems == True) )  
	{
		// ok have a no stock reason and a previously picked issn
		// so need the status to go to PL in the issn, pick_item and pick_item_detail
		// so that the gettolocn will run
			$tran_qty = $qty;
			// use null has the location
			$wk_nostock_location = null;
	
			/* write transaction */
			$my_message = "";
			$wk_nostock_location = '0000000000';
			// need to use the current wh_id here
			$wk_my_wh_id = getBDCScookie($Link, $tran_device, "CURRENT_WH_ID" );
			$wk_nostock_location = $wk_my_wh_id . '00000000';
			$wk_nostock_object  = '0000000000';
			if ($ssn <> "")
			{
				$my_sublocn = $label_no;
			}
			else
			{
				// a product
				if ( $wk_system_product_by == 'ISSN') {
					$my_sublocn = $label_no;
				} else {
					$my_nostock_object = $prod_no;
					$my_sublocn = "";
					$my_sublocn = $label_no;
				}
			}

			//echo ("dotransaction($tran_type, $tran_tranclass, $my_nostock_object, $wk_nostock_location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, 'Y')");
			$my_message = dotransaction($tran_type, $tran_tranclass, $wk_nostock_object, $wk_nostock_location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, "Y");
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

	}
		
		
	/*
	if product was NOPROD
	must to a PKIL using the default desp locn from the control

        if doing a no stock reason and have already picked an issn then do not do this
	however if doing  a no stock reason without a non zero issn then must allow this
	*/
	/* if (($my_object == "NOPROD") or
	   ($qty == 0)) -* must include zero picked lines here */
	if (($my_object == "NOPROD") or 
	   ( ($qty == 0) and ($wk_HaveItems == False) )  )
	{
		// now must do a pick despatch
		$Query = "select default_despatch_location from control";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Total!<BR>\n");
			exit();
		}
		else
		if (($Row = ibase_fetch_row($Result)))
		{
			$wk_default_desp =  $Row[0];
		}
	
		//release memory
		ibase_free_result($Result);
		//commit
		ibase_commit($dbTran);

		header("Location: transactionIL.php?order=" . urlencode($prod_no) . "&ttype=P" . "&location=" . urlencode($wk_default_desp));
		exit();
		
	}
	// dont save locn for a no stock reason since the location will be empty
	// dont save if there is no locn_seq for that location
	if ($my_ref == "")
	{
		$wk_lo_sequence =  "";
		$Query = "select l3.locn_seq from location l3 where l3.wh_id = '" ; 
		$Query .= substr($location,0,2)."' and l3.locn_id = '";
		$Query .= substr($location,2,strlen($location) - 2)."'";
		//echo($Query);
	
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read SSNs!<BR>\n");
			exit();
		}
		else
		while (($Row = ibase_fetch_row($Result)))
		{
			$wk_lo_sequence =  $Row[0];
		}
		//release memory
		ibase_free_result($Result);
		if (!is_null($wk_lo_sequence ))
		{
			// ok a valid next sequence
			$cookiedata = $location;
			setBDCScookie($Link, $tran_device, "picklocation", $cookiedata);
		}
	}
/*
	dont print split labels now
	// =========================================================================================================================
	// check for issn splits

	$Query = "select s2.prod_id, sum(s2.current_qty), c3.default_pick_printer,  w1.default_pick_printer, p2.pick_label_no from pick_item_detail p2 ";
	$Query .= "join issn s2 on s2.ssn_id = p2.ssn_id ";
	$Query .= "join  control c3 on c3.record_id = 1 ";
	$Query .= "join  pick_item p3 on p2.pick_label_no = p3.pick_label_no ";
	$Query .= "join  pick_order p4 on p3.pick_order  =  p4.pick_order ";
	$Query .= "left outer join  warehouse w1 on p4.wh_id =  w1.wh_id  ";
	//$Query .= " where p2.pick_label_no = '" . $label_no . "'";
	$Query .= " where p2.device_id = '" . $tran_device . "'";
	$Query .= " and s2.prod_id = '" . $prod_no . "'" ;
	$Query .= " and s2.other2 starting 'Split'";
	$Query .= " and s2.label_date is null";
	$Query .= " group by p2.pick_label_no, s2.prod_id, c3.default_pick_printer, w1.default_pick_printer ";
	//print($Query);
	
	$wk_split_ssn = "";

	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read SSNs!<BR>\n");
		exit();
	}
	else
	while (($Row = ibase_fetch_row($Result)))
	{
		$wk_sp_prod =  $Row[0];
		$wk_sp_qty =  $Row[1];
		$wk_sp_printer =  $Row[2];
		$wk_sp_wh_printer  =  $Row[3];
		$wk_sp_label_no  =  $Row[4];

		// ok print a label now - probably only one
		if (($wk_sp_wh_printer <> '') and ($wk_sp_wh_printer <> ' '))
		{
			// have a default printer for the orders wh
			$wk_sp_printer = $wk_sp_wh_printer;
		}
		// need printers IP
		// printer directory
                $printerIp = getPrinterIp($Link, $wk_sp_printer);
                $printerDir = getPrinterDir($Link, $wk_sp_printer);
		if ($wk_sp_prod <> "")
		{
                	require_once 'Printer.php';
			$p = new Printer($printerIp);
                	$p->data['printer_id'] = $wk_sp_printer;
                	$p->data['PROD_PROFILE.PROD_ID'] = $wk_sp_prod;
                	$p->data['PICK_ITEM.PICK_LABEL_NO'] = $wk_sp_label_no ;
                	$p->data['ISSN.SSN_ID'] = '';
                	//$p->data['labelQty'] = $wk_sp_qty;
                	$p->data['labelQty'] = 1;
                	$p->data['PACK_QTY'] = $wk_sp_qty;
			getProductLabel($Link, $p, $wk_sp_prod, $label_no ) ;
        	        $save = fopen(	$printerDir .
                			$wk_sp_prod . '_PROD_' . $wk_sp_qty . '.prn', 'w');
                    	$tpl = "";
			//if (!$p->sysLabel($Link, $wk_sp_printer, "PRODUCT", $wk_sp_qty))
			if (!$p->sysLabel($Link, $wk_sp_printer, "PRODUCT", $p->data['labelQty']))
        	        {
                		$p->send($tpl, $save);
	                }
        	        fclose($save);
			// then update the label printed date
			$Query1 = "update issn set label_date = 'NOW' ";
			$Query1 .= " where ssn_id in (select s2.ssn_id from pick_item_detail p2 ";
			$Query1 .= "join issn s2 on s2.ssn_id = p2.ssn_id ";
			$Query1 .= " where p2.pick_label_no = '" . $label_no . "'";
			$Query1 .= " and s2.other2 starting 'Split'";
			$Query1 .= " and s2.label_date is null)";
			if (!($Result1 = ibase_query($Link, $Query1)))
			{
				echo("Unable to Update SSN!<BR>\n");
			}
		}
	}

	// =========================================================================================================================
	*/
	// ok by here have done the PKOL
	// 
        /* if doing pick by product of location wont have a label no  - have to use product !!!!!*/
	$Query = "select 1 from pick_item ";
	//$Query .= " where pick_label_no = '" . $label_no . "'";
	$Query .= " where ";
	if ($ssn <> "")
	{
		$Query .= " pick_label_no = '" . $label_no . "'";
	}
	else
	{
		// a product
		if ( $wk_system_product_by == 'ISSN') {
			$Query .= " pick_label_no = '" . $label_no . "'";
		} else {
			// dont have a label so use the product
			$Query .= " prod_id = '" . $prod_no . "'";
		}
	}
	$Query .= " and pick_line_status = 'PG'";
	$Query .= " and device_id = '".$tran_device."'";
	//print($Query);
	
	$continue_this_line = 0;
	$continue_cnt = 0;

	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Total!<BR>\n");
		exit();
	}
	else
	if (($Row = ibase_fetch_row($Result)))
	{
		$continue_this_line =  $Row[0];
		$continue_cnt = 1;
	}
	
	//release memory
	ibase_free_result($Result);
	//commit
	ibase_commit($dbTran);
	if ($continue_cnt == 0)
	{
		$Query = "select first 1 1, pick_label_no from pick_item ";
		$Query .= " where pick_line_status in ('AL', 'PG')";
		$Query .= " and device_id = '".$tran_device."'";
		//print($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Pick Item!<BR>\n");
			exit();
		}
		else
		if (($Row = ibase_fetch_row($Result)))
		{
			$continue_cnt = 1;
			$continue_label = $Row[1];
		}
	
		//release memory
		ibase_free_result($Result);
	}

        /* if partial pick is true need not only PL but PG without stock as well 
           then must status PL in pick_item pick_item_detail and issn */
	$Query = "select first 1 1, pick_label_no from pick_item ";
	$Query .= " where pick_line_status in ('PL')";
	if ($ssn <> "")
	{
		$Query .= " and ssn_id = '" . $ssn . "'";
	}
	else
	{
		$Query .= " and prod_id = '" . $prod_no . "'";
	}
	$Query .= " and device_id = '".$tran_device."'";
	//print($Query);
	
	$despatch_cnt = 0;
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Total!<BR>\n");
		exit();
	}
	else
	if (($Row = ibase_fetch_row($Result)))
	{
		$despatch_cnt =  1;
		$despatch_label = $Row[1];
	}
	
	//release memory
	ibase_free_result($Result);
	
	//commit
	ibase_commit($dbTran);
	//
	// if picked any product (ie to my device)
	// want to assign it to a location in pick_location for an order that I have for it
	// otherwise 
	//   if more prods to do
	//    if there are other products in the current location that I want
	//    then change to that product and go to getfromqty
	//    else  go to getfromlocn
	// 
	{
		if ($despatch_cnt > 0)
		{
			if ($ssn <> "")
			{
				header("Location: gettolocn.php?order=" . urlencode($ssn) . "&ttype=I");
			}
			else
			{
				header("Location: gettolocn.php?order=" . urlencode($prod_no) . "&ttype=I");
			}
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
}
?>
