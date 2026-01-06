<?php
session_start();

/*
have the from issn
and 2 sets of qty of labels * qty per label
    a printer to use
    from screen to go back to

so if ssn current_qty = 1 can only do 1*1 + 0*x or 0*x + 1*1

otherwise this is like grnv p except no ssn's
the company and product come from the original issn
the original_ssn comes from the original issn

afterwards come back to this screen and print the labels

*/
//phpinfo();


// ========================  Functions  ===========================================================================
/**
 * log a message to logfile
 *
 * @param ibase_link $Link Connection to database
 * @param string $message
 */

function logtime2( $Link,  $message)
{
	$Query = "";
	$log = fopen('/data/tmp/transTRSS2.log' , 'a');
		$wk_current_time = "";
		$Query = "select cast(cast('NOW' as timestamp) as char(24)) from control ";
		$Query = "select cast('NOW' as timestamp) from control ";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to query table control<BR>\n");
			exit();
		}
		else
		if (($Row = ibase_fetch_row($Result)))
		{
			$wk_current_time =  $Row[0];
		}
		else
		{
			$wk_current_time = "";
		}
		
		//release memory
		//$Result->free();
		//ibase_free_result($Result);
		$wk_current_micro = microtime();
		$wk_current_time .= " " . $wk_current_micro;
		$wk_message = str_replace("'", "`", $message);
		
		// 1st try ip address of handheld
	if (isset($_COOKIE['LoginUser']))
	{
		list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
		$userline = sprintf("%s  by %s on %s", $wk_current_time, $tran_user, $tran_device);
	} else {
		$userline = sprintf("%s  ", $wk_current_time );
	}

	fwrite($log,"  ");
	fwrite($log, $userline);
	fwrite($log, $message);
	fwrite($log,"  ");
	fwrite($log,"  \n");
	fclose($log);
}


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
 * startsWith
 *
 * @param string $haystack
 * @param string $needle
 * @return boolean
 */
function startsWith($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}


/**
 * endsWith
 *
 * @param string $haystack
 * @param string $needle
 * @return boolean
 */
function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}
/* ================================================================================================ */

if (isset($_COOKIE['LoginUser']))
{
	$tran_type = "GRNV";
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	include 'logme.php';
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	
	
	if (isset($_COOKIE['BDCSData']))
	{
		list($received_qty, $type, $order, $line, $carrier, $vehicle, $container, $pallet_type, $pallet_qty, $consignment, $grn, $problem) = explode("|", $_COOKIE["BDCSData"]);
	}

	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: verifyLP.php?message=Can+t+connect+to+DATABASE!"  );
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	setBDCScookie($Link, $tran_device, "LabelErrorText", "" );

/* ================================================================================================ */
function getVehicleOption($Link, $code)
{
	{
		$Query = "select description, description2 from options where group_code='RECEIVE'  and code = 'OTHER|" . $code . "' "; 
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			//$log = fopen('/tmp/getdelivery.log' , 'a');
			$log = fopen('/data/tmp/getdelivery.log' , 'a');
			fwrite($log, $Query);
			fclose($log);
			//exit();
		}
		$wk_data = "";
		$wk_data2 = "";
		while ( ($Row = ibase_fetch_row($Result)) ) {
			if ($Row[0] > "")
			{
				$wk_data = $Row[0];
			}
			if ($Row[1] > "")
			{
				$wk_data2 = $Row[1];
			}
		}
		//release memory
		ibase_free_result($Result);
	} 
	return array( $wk_data, $wk_data2);
} // end of function
/* ================================================================================================ */
	$wk_error_screen = "split1ssn.php";
	$wk_error_screen = "getlocn.php";

function getReceiveOption($Link, $code)
{
	{
		$Query = "select description, description2 from options where group_code='RECEIVE'  and code = '" . $code . "' "; 
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Options!<BR>\n");
			//$log = fopen('/tmp/getdelivery.log' , 'a');
			$log = fopen('/data/tmp/getdelivery.log' , 'a');
			fwrite($log, $Query);
			fclose($log);
			//exit();
		}
		$wk_data = "";
		$wk_data2 = "";
		while ( ($Row = ibase_fetch_row($Result)) ) {
			if ($Row[0] > "")
			{
				$wk_data = $Row[0];
			}
			if ($Row[1] > "")
			{
				$wk_data2 = $Row[1];
			}
		}
		//release memory
		ibase_free_result($Result);
	} 
	return array( $wk_data, $wk_data2);
} // end of function

function getProductDescription($Link, $code, $company)
{
	{
		$wk_data = "";
		$wk_data2 = "";
		$wk_found = False;
		//$Query = "select short_desc, long_desc from prod_profile where prod_id = '" . $code . "' "; 
		$Query = "select short_desc, long_desc, 1 from prod_profile where prod_id = '" . $code . "' "; 
		$Query .= " and company_id = '" . $company . "' "; 
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Prod Profile!<BR>\n");
			//$log = fopen('/tmp/getdelivery.log' , 'a');
			$log = fopen('/data/tmp/getdelivery.log' , 'a');
			fwrite($log, $Query);
			fclose($log);
			//exit();
		}
		while ( ($Row = ibase_fetch_row($Result)) ) {
			if ($Row[0] > "")
			{
				$wk_data = $Row[0];
			}
			if ($Row[1] > "")
			{
				$wk_data2 = $Row[1];
			}
			if ($Row[2] > "")
			{
				$wk_found = True;
			}
		}
		//release memory
		ibase_free_result($Result);
		if (!$wk_found)
		{
			//try company all
			$Query = "select short_desc, long_desc, 1 from prod_profile where prod_id = '" . $code . "' "; 
			$Query .= " and company_id = 'ALL' "; 
			//echo($Query);
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to Read Prod Profile!<BR>\n");
				//$log = fopen('/tmp/getdelivery.log' , 'a');
				$log = fopen('/data/tmp/getdelivery.log' , 'a');
				fwrite($log, $Query);
				fclose($log);
				//exit();
			}
			while ( ($Row = ibase_fetch_row($Result)) ) {
				if ($Row[0] > "")
				{
					$wk_data = $Row[0];
				}
				if ($Row[1] > "")
				{
					$wk_data2 = $Row[1];
				}
				if ($Row[2] > "")
				{
					$wk_found = True;
				}
			}
			//release memory
			ibase_free_result($Result);
		}
	} 
	return array( $wk_data, $wk_data2, $wk_found);
} // end of function


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
		

function errorHandler2( $errno, $errstr, $errfile, $errline, $errcontext)
{
	global $dbTran, $wk_db_error, $Link, $wk_db_error_msg ;
	$log = fopen('/data/tmp/splittransLPNV.log' , 'a');
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
	$wk_db_error_msg = $errno . " " . $errstr;
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
logtime2( $Link,  __LINE__ . ":" . __FUNCTION__ . " Start transLPNV");
	$tran_deadlock_limit = 5;
	$other_qty1 = 0;
	$other_qty2 = 0;
	$other_qty3 = 0;
	$other_qty4 = 0;
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

	$retfrom = getBDCScookie($Link, $tran_device, "retfrom" );
	$product = getBDCScookie($Link, $tran_device, "product" );
	$label_qty1 = getBDCScookie($Link, $tran_device, "label_qty1" );
	$ssn_qty1 = getBDCScookie($Link, $tran_device, "ssn_qty1" );
	$label_qty2 = getBDCScookie($Link, $tran_device, "label_qty2" );
	$ssn_qty2 = getBDCScookie($Link, $tran_device, "ssn_qty2" );
	$label_qty3 = getBDCScookie($Link, $tran_device, "label_qty3" );
	$ssn_qty3 = getBDCScookie($Link, $tran_device, "ssn_qty3" );
	$weight_qty1 = getBDCScookie($Link, $tran_device, "weight_qty1" );
	$weight_uom = getBDCScookie($Link, $tran_device, "weight_uom" );
	$printer = getBDCScookie($Link, $tran_device, "printer" );
	$received_ssn_qty = getBDCScookie($Link, $tran_device, "received_ssn_qty" );
	$location = getBDCScookie($Link, $tran_device, "location" );
	$uom = getBDCScookie($Link, $tran_device, "uom" );
	$printed_ssn_qty = getBDCScookie($Link, $tran_device, "printed_ssn_qty" );
	$owner = getBDCScookie($Link, $tran_device, "owner" );
	$other_qty1 = getBDCScookie($Link, $tran_device, "other_qty1" );
	$other_qty3 = getBDCScookie($Link, $tran_device, "other_qty3" );
	$other_qty2 = getBDCScookie($Link, $tran_device, "other_qty2" );
	$other_qty4 = getBDCScookie($Link, $tran_device, "other_qty4" );
	$complete   = getBDCScookie($Link, $tran_device, "complete" );

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
		$product = strtoupper( $_POST['product']);
	}
	if (isset($_GET['product']))
	{
		$product = strtoupper( $_GET['product']);
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
		$class = "A";
	}
	if (isset($_POST['problem']))
	{
		$problem = $_POST['problem'];
	}
	if (isset($_GET['problem']))
	{
		$problem = $_GET['problem'];
	}
	if (isset($_POST['other_qty1']))
	{
		$other_qty1 = $_POST['other_qty1'];
	}
	if (isset($_GET['other_qty1']))
	{
		$other_qty1 = $_GET['other_qty1'];
	}
	if (isset($_POST['other_qty2']))
	{
		$other_qty2 = $_POST['other_qty2'];
	}
	if (isset($_GET['other_qty2']))
	{
		$other_qty2 = $_GET['other_qty2'];
	}
	if (isset($_POST['other_qty3']))
	{
		$other_qty3 = $_POST['other_qty3'];
	}
	if (isset($_GET['other_qty3']))
	{
		$other_qty3 = $_GET['other_qty3'];
	}
	if (isset($_POST['other_qty4']))
	{
		$other_qty4 = $_POST['other_qty4'];
	}
	if (isset($_GET['other_qty4']))
	{
		$other_qty4 = $_GET['other_qty4'];
	}
	if (isset($_POST['owner']))
	{
		$owner = $_POST['owner'];
	}
	if (isset($_GET['owner']))
	{
		$owner = $_GET['owner'];
	}
	if (isset($_GET['cancelproduct']))
	{
		$cancel_product = $_GET['cancelproduct'];
	}
	if (isset($_POST['cancelproduct']))
	{
		$cancel_product = $_POST['cancelproduct'];
	}

	if (isset($_GET['ssn_id']))
	{
		$ssn_id = $_GET['ssn_id'];
	}
	if (isset($_POST['ssn_id']))
	{
		$ssn_id = $_POST['ssn_id'];
	}
	if (isset($ssn_id))
	{
		$Query = "select i1.current_qty, i1.company_id, i1.prod_id, i1.original_ssn, s1.grn  from issn i1 left outer join ssn s1 on i1.original_ssn = s1.ssn_id where i1.ssn_id = '" . $ssn_id . "'  "; 
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read ISSN!<br>\n");
			exit();
		}
		$wkISSNSSNId = $ssn_id;
		$wkISSNCurrenQty = 0;
		$owner = "";
		$company_id = "";
		$product = "";
		$grn = "";
		$wkSSNSSNId  = "";
		$problem = "";

		$class = "A";
		$type = "SS";
		while ( ($Row = ibase_fetch_row($Result)) ) {
			$wkISSNCurrentQty = $Row[0];
			$owner = $Row[1];
			$company_id = $Row[1];
			$product = $Row[2];
			$wkSSNSSNId  = $Row[3];
			$grn = $Row[4];
		}
		//release memory
		ibase_free_result($Result);
	}
	if (isset($grn))
	{

		$Query = "select g1.return_id , g1.order_line_no   from grn g1  where g1.grn = '" . $grn . "'  "; 
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read GRN!<br>\n");
			exit();
		}
		$retfrom = "";
		$line = "";
		while ( ($Row = ibase_fetch_row($Result)) ) {
			$retfrom = $Row[0];
			$line = $Row[1];
		}
		//release memory
		ibase_free_result($Result);
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

	$product_company = "";
	$product_company = $owner;

	$wkOrderSaleChannel = "NONE";
	$wkPRRecordId = 0;
	$wkPRAvailableQty = 0;
	$wkPRReserveQty = 0;

	// add the none entry last
	$wkIssn = array();
	$wkIssn['PR_RECORD_ID']  = 0;
	$wkIssn['PR_AVAILABLE_QTY']  = 0;
	$wkIssn['PR_RESERVED_QTY']  = 0;
	$wkIssn['PR_SALE_CHANNEL_CODE']  =  "NONE" ;
	$wkPRRecord[] = array($wkIssn);

	$useNewProduct  = getReceiveOption($Link,  "PROD_PROFILE.NEW_PROD_ID" );

	$tran_type = "GRNV";
	$my_object = '';
		
	// $my_object = sprintf("%-10.10s%-10.10s", $retfrom, $owner) ;
	$my_object = $product ;
/*
	$location = "";
*/
	$my_sublocn = $grn;

	$my_ref = '';
	$my_ref = $type;
	// $my_ref .= '|' . $order;
	$my_ref .= '|' . $retfrom;
	$my_ref .= '|' . $line;
	$my_ref .= '|' . $label_qty1;
	$my_ref .= '|' . $ssn_qty1;
	$my_ref .= '|' . $label_qty2;
	$my_ref .= '|' . $ssn_qty2;
	$my_ref .= '|' . substr($printer,1,1);
	$my_source = 'SSBSSKSSS';
	$tran_tranclass = $class;
	$tran_qty = $received_ssn_qty;

	setBDCScookie($Link, $tran_device, "retfrom", $retfrom);
	setBDCScookie($Link, $tran_device, "product", $product);
	setBDCScookie($Link, $tran_device, "label_qty1", $label_qty1);
	setBDCScookie($Link, $tran_device, "ssn_qty1", $ssn_qty1);
	setBDCScookie($Link, $tran_device, "label_qty2", $label_qty2);
	setBDCScookie($Link, $tran_device, "ssn_qty2", $ssn_qty2 );
	setBDCScookie($Link, $tran_device, "label_qty3", $label_qty3);
	setBDCScookie($Link, $tran_device, "ssn_qty3", $ssn_qty3 );
	setBDCScookie($Link, $tran_device, "printer", $printer);
	setBDCScookie($Link, $tran_device, "received_ssn_qty", $received_ssn_qty);
	setBDCScookie($Link, $tran_device, "location", $location);
	setBDCScookie($Link, $tran_device, "uom", $uom);
	setBDCScookie($Link, $tran_device, "owner", $owner );
	setBDCScookie($Link, $tran_device, "other_qty1", $other_qty1 );
	setBDCScookie($Link, $tran_device, "other_qty3", $other_qty3 );
	setBDCScookie($Link, $tran_device, "other_qty2", $other_qty2 );
	setBDCScookie($Link, $tran_device, "other_qty4", $other_qty4 );
	setBDCScookie($Link, $tran_device, "complete", $complete );

	// get where next screen is going
	$Query = "select default_receive_weights from control";

	//echo($Query);
	$wk_receive_weights =  "F";
	if (!($Result = ibase_query($Link, $Query)))
	{
		$wk_receive_weights =  "F";
	}
	elseif (($Row = ibase_fetch_row($Result)))
	{
		$wk_receive_weights =  $Row[0];
	}
	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
	}
	$my_ref = '';
	include("transaction.php");
	/* now do the GRNV     */
	{
		$wk_db_error = False;
		$wk_db_error_msg = "";
		set_error_handler('errorHandler2');
		$wk_db_error_count = 0;

		$toPickQty = 0;

		$tran_type = "GRNV";
		$tran_type = "TRSS";
		$my_object = '';
		
		// $my_object = sprintf("%-10.10s%-10.10s", $retfrom, $owner) ;
		$my_object = $ssn_id ;
/*
	$location = "";
*/
		$my_sublocn = $grn;

		$my_ref = '';
/*
		$my_ref = $type;
		// $my_ref .= '|' . $order;
		$my_ref .= '|' . $retfrom;
		$my_ref .= '|' . $line;
		$my_ref .= '|' . $label_qty1;
		$my_ref .= '|' . $ssn_qty1;
		$my_ref .= '|' . $label_qty2;
		$my_ref .= '|' . $ssn_qty2;
		$my_ref .= '|' . substr($printer,1,1);
*/
		$my_source = 'SSBSSKSSS';
		$tran_tranclass = $class;
	 	$tran_qty = $received_ssn_qty;

/* 
for x in range 1 to label qty 1
	create split from original for qty ssn qty1
	read the resulting used ssn_id into an array
for x in range 1 to label qty 2
	create split from original for qty ssn qty2
	read the resulting used ssn_id into an array
*/
		$wk_result_list = array();
		for ($wk_label_line = 0; $wk_label_line < $label_qty1; $wk_label_line++) {
	 		$tran_qty = $ssn_qty1;
logtime2( $Link,  __LINE__ . ":" . __FUNCTION__ . " start TRSS " . $tran_qty . " " . $wk_label_qty);
			$my_message = "";
			$my_message = dotransaction_response($tran_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, $product, $owner);
logtime2( $Link,  __LINE__ . ":" . __FUNCTION__ . " done TRSS");
			//echo $my_message;
			if ($my_message > "")
			{
				list($my_mess_field, $my_mess_label) = explode("=", $my_message);
				$my_responsemessage = urldecode($my_mess_label) . " ";
			}
			else
			{
				$my_responsemessage = "";
			}
			$my_result = explode('|', $my_responsemessage);
			if ($my_result[0] <> "Processed successfully")
			{
				if (empty($my_message))
					$my_message="message=";
				$wk_my_db_error_str = preg_replace("/\"/", "", $wk_db_error_msg);
				header("Location: " . $wk_error_screen . "?" . $my_message . urlencode($wk_my_db_error_str));
				exit();
			}
			else
			{
				$wk_result_list[] = array( trim($my_result[2]), $label_qty1, $ssn_qty1);
				
			}
		}
		for ($wk_label_line = 0; $wk_label_line < $label_qty2; $wk_label_line++) {
	 		$tran_qty = $ssn_qty2;
			$my_message = "";
logtime2( $Link,  __LINE__ . ":" . __FUNCTION__ . " start TRSS " . $tran_qty . " " . $wk_label_qty);
			$my_message = dotransaction_response($tran_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, $product, $owner);
logtime2( $Link,  __LINE__ . ":" . __FUNCTION__ . " done TRSS");
			//echo $my_message;
			if ($my_message > "")
			{
				list($my_mess_field, $my_mess_label) = explode("=", $my_message);
				$my_responsemessage = urldecode($my_mess_label) ;
			}
			else
			{
				$my_responsemessage = "";
			}
			$my_result = explode('|', $my_responsemessage);
			if ($my_result[0] <> "Processed successfully")
			{
				if (empty($my_message))
					$my_message="message=";
				$wk_my_db_error_str = preg_replace("/\"/", "", $wk_db_error_msg);
				header("Location: " . $wk_error_screen . "?" . $my_message . urlencode($wk_my_db_error_str));
				exit();
			}
			else
			{
				$wk_result_list[] = array( trim($my_result[2]), $label_qty2, $ssn_qty2);
				
			}
		}

	}
logtime2( $Link,  __LINE__ . ":" . __FUNCTION__ . " done all the TRSS");
		
	// do a commit point here
	//ibase_commit($dbTran);
	// now have issn and ssns
	//$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	// if the control.generate_label_text is 'F' then must create the labels
	// for issn's with no label date
	// for this grn
	//echo('my_result:' . print_r($my_result,true));
	$errorText = '';

	$printerIp = getPrinterIp($Link, $printer);
        $printerDir = getPrinterDir($Link, $printer);
        require_once 'Printer.php';
logtime2( $Link,  __LINE__ . ":" . __FUNCTION__ . " start label print loop");
	foreach ($wk_result_list as $wk_cnt => $result)
	{
		//$printerIp = getPrinterIp($Link, $printer);
            	//$printerDir = getPrinterDir($Link, $printer);
		//$result = $my_result;

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
logtime2( $Link,  __LINE__ . ":" . __FUNCTION__ . " do getISSNLabel " . $result[0]);
                	$q = getISSNLabel($Link, $p );
logtime2( $Link,  __LINE__ . ":" . __FUNCTION__ . " done getISSNLabel");
                        {
                                    //$save = fopen($printerDir .
                                    //              $p->data['issn'] . '_ISSN.prn', 'w');
logtime2( $Link,  __LINE__ . ":" . __FUNCTION__ . " do print label " . $result[0]);
                                    //if (!$p->sysLabel($Link, $printer, "ISSN", 1))
                                    if (!$p->sysLabel($Link, $printer, "ISSN_PRODUCT", 1))
                                    {
					
                                        //$p->send($tpl, $save);
                                    }
logtime2( $Link,  __LINE__ . ":" . __FUNCTION__ . " done print label");
                                    $wk_suffix = "1" . $p->data['issnlabelsuffix'];
                                    $wk_suffix++;
                                    $p->data['issnlabelsuffix'] = substr($wk_suffix,1);
                                    $p->data['issn'] = $p->data['issnlabelprefix'] .  $p->data['issnlabelsuffix']; 
                                    $p->data['ISSN.SSN_ID'] = $p->data['issn'];
                                    //fclose($save);
                        }
		}

		$errorText = getBDCScookie($Link, $tran_device, "LabelErrorText" );
		//echo $errorText;
	}
		
	if (!empty($errorText))
	{
		//if ($ErrorText not starting "Print Request Sent")
		if (!startsWith($errorText ,"Print Request Sent"))
		{
logtime2( $Link,  __LINE__ . ":" . __FUNCTION__ . " label error - dont start print Request Sent");
logtime2( $Link,  __LINE__ . ":" . __FUNCTION__ . $errorText);
			//if (empty($my_message))
			//	$my_message="message=";
			$my_message="message=";
			header("Location: " . $wk_error_screen . "?" . $my_message . urlencode($errorText) );
			exit();
		}
	}
	setBDCScookie($Link, $tran_device, "product","" );
	//setBDCScookie($Link, $tran_device, "received_ssn_qty", "0");
	//commit
	ibase_commit($dbTran);
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	
	$_SESSION['TRANSLPNV_PROCESS'] = "FINISHED";
logtime2( $Link,  __LINE__ . ":" . __FUNCTION__ . " done transLPNV");
	//want to go to 
	//header("Location: getlocn.php" );
	header("Location: ssn.php" );
}
?>
