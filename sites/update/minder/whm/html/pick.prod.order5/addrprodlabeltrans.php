<?php

function sendDSOTTransaction($link, $orderNo, $companyId)
{
    // Set default just in case the value wasn't provided
    $printerId = isset($_POST['printer_id']) ? $_POST['printer_id'] : '';
    $carrier = isset($_POST['carrier']) ? $_POST['carrier'] : '';
    $packType = '';
    $packQty = 0;
    $receivedQty = isset($_POST['recvd']) ? $_POST['recvd'] : '';
    $despatchQty1 = (int)$_POST['qty1'] ;
    $despatchQty3 = (int)$_POST['qty3'] ;
    //$companyId = $orderInfo[31];
    //$companyId = $orderInfo['COMPANY_ID'];
    $companyCarrier = '';
    $systemCarrier = '';
    $orderCarrier = '';
    $carrierTrn = '';
    $carrierService = '';
    $weight = 0;
    $volume = 0;
    //echo "company is " . $companyId;
    //print_r ($orderInfo);

    // get control system wides
    {
        $query = "SELECT DEFAULT_CARRIER_ID, 
                         DEFAULT_CONNOTE_PACK, 
                         DEFAULT_CONNOTE_PACK_QTY,
                         DEFAULT_CONNOTE_WEIGHT
                  FROM CONTROL ";
        if (!($Result = ibase_query($link, $query))) {
            return array(null, null, 'Unable to read control!');
        }
        if (($Row = ibase_fetch_row($Result))) {
            $systemCarrier =  $Row[0];
            $packType =  $Row[1];
            $packQty =  $Row[2];
            $weight =  $Row[3];
            ibase_free_result($Result);
            unset($Result);
        }
    } 
    // get companys carrier
    {
        $query = "SELECT DESCRIPTION 
                  FROM OPTIONS 
                  WHERE GROUP_CODE = 'CMPSHIPVIA' AND CODE = '" . $companyId . "' ";
        if (!($Result = ibase_query($link, $query))) {
            return array(null, null, 'Unable to read options!1');
        }
        if (($Row = ibase_fetch_row($Result))) {
            $companyCarrier =  $Row[0];
            ibase_free_result($Result);
            unset($Result);
        }
    } 
    // get orders  carrier
    {
        $query = "SELECT SHIP_VIA 
                  FROM PICK_ORDER
                  WHERE PICK_ORDER = '" . $companyId . "' ";
        if (!($Result = ibase_query($link, $query))) {
            return array(null, null, 'Unable to read options!2');
        }
        if (($Row = ibase_fetch_row($Result))) {
            $orderCarrier =  $Row[0];
            ibase_free_result($Result);
            unset($Result);
        }
    } 
    // which carrier do i use
    if ($orderCarrier == '')
    {
    	if ($companyCarrier == '')
    	{
        	$carrier = $systemCarrier;
    	}
    	else
    	{
        	$carrier = $companyCarrier;
    	}
    }
    else
    {
        $carrier = $orderCarrier;
    }
    // get carriers transaction
    {
        $query = "SELECT TRN_TYPE 
                  FROM CARRIER 
                  WHERE CARRIER_ID = '" . $carrier . "' ";
        if (!($Result = ibase_query($link, $query))) {
            return array(null, null, 'Unable to read carrier!');
        }
        if (($Row = ibase_fetch_row($Result))) {
            $carrierTrn =  $Row[0];
            ibase_free_result($Result);
            unset($Result);
        }
    } 
    // get carriers service
    {
        $query = "SELECT SERVICE_TYPE 
                  FROM CARRIER_SERVICE 
                  WHERE CARRIER_ID = '" . $carrier . "' ";
        if (!($Result = ibase_query($link, $query))) {
            return array(null, null, 'Unable to read carrier service!');
        }
        if (($Row = ibase_fetch_row($Result))) {
            $carrierService =  $Row[0];
            ibase_free_result($Result);
            unset($Result);
        }
    } 
    // populate the pallet type
    $pallet_type = "NONE";
    // populate the qtys
    if ($packType == 'P')
    {
        $pallet_qty = $packQty;
        $carton_qty = 0;
        $satchel_qty = 0;
    }
    if ($packType == 'C')
    {
        $pallet_qty = 0;
        $carton_qty = $packQty;
        $satchel_qty = 0;
    }
    if ($packType == 'S')
    {
        $pallet_qty = 0;
        $satchel_qty = $packQty;
        $carton_qty = 0;
    }
    // populate the payer
    $payer = 'S'; // value   R requires an account in the object field


    // Get the information for dotransaction
    list($user, $device) = explode("|", $_COOKIE["LoginUser"]);
    if ( $carrierTrn == '')
    {
        $type = 'DSOT';
    }
    else
    {
        $type = $carrierTrn;
    }
    $code = 'S';
    $object = sprintf("%-20.20s%-10.10s", $orderNo , '');
    $location = $orderNo;
    $subLocation = $carrier;
    $reference = sprintf("%04d%-10.10s%04d%04d%05d%05d%s", $pallet_qty , $pallet_type, $carton_qty, $satchel_qty, $weight, $volume, $payer);
	if ($pallet_type == "NONE")
		$reference .= 'S';
	else
		$reference .= 'P';
	$reference .=  $carrierService ;
	$reference .= '|' . $printerId;
    $source = 'SSOSSSSSS';
    $qty = $despatchQty1 + $despatchQty3;

    $mesg = dotransaction_response($type, $code, $object, $location, $subLocation, $reference, $qty, $source, $user, $device);
    list($dummy, $message) = explode( '=', urldecode($mesg));
	list($my_mess_field, $my_mess_label) = explode("=", $mesg);
	$my_responsemessage = urldecode($my_mess_label) ;
	$wk_responsemessage_saved = $my_responsemessage;
       	$wkParams = explode ("|", $my_responsemessage );
        //echo("Params:");
       	//var_dump($wkParams);
	$message = $wkParams[0] ;

/*
    echo '<h1>_POST</h1>';
    echo '<pre>';
    print_r($_POST);
    echo '</pre>';

    echo '<h1>GRND</h1>';
    echo '<pre>';
    echo "type = $type\n";
    echo "code = $code\n";
    echo "object = $object\n";
    echo "location = $location\n";
    echo "subLocation = $subLocation\n";
    echo "reference = $reference\n";
    echo "qty = $qty\n";
    echo "source = $source\n";
    echo "user = $user\n";
    echo "device = $device\n";
    echo "grn = $grn\n";
    echo "orderNo = $orderNo\n";
    echo "message = $message\n";
    echo '</pre>';
*/

    if ($message != 'Processed successfully') {
        return array(null, $message);
    }

    return array( $orderNo, $message);
}


function sendDSOLTransaction($link, $orderNo )
{
    $despatchQty1 = (int)$_POST['qty1'] ;
    $despatchQty3 = (int)$_POST['qty3'] ;
    $printerId = isset($_POST['printer_id']) ? $_POST['printer_id'] : '';
    // Get the information for dotransaction
    list($user, $device) = explode("|", $_COOKIE["LoginUser"]);
    $type = 'DSOL';
    $code = 'O';
    $object = sprintf("%-20.20s%s", $orderNo , '');
    $location = $orderNo;
    $subLocation = '';
    $source = 'SSSSSSSSS';
    $qty = $despatchQty1 + $despatchQty3;
    $reference = sprintf("%-20.20s", '' );
    $reference .=  '|' . $printerId ;

/*
    echo '<h1>DSOL</h1>';
    echo '<pre>';
    echo "type = $type\n";
    echo "code = $code\n";
    echo "object = $object\n";
    echo "location = $location\n";
    echo "subLocation = $subLocation\n";
    echo "reference = $reference\n";
    echo "qty = $qty\n";
    echo "source = $source\n";
    echo "user = $user\n";
    echo "device = $device\n";
*/
    $mesg = dotransaction_response($type, $code, $object, $location, $subLocation, $reference, $qty, $source, $user, $device);
    list($dummy, $message) = explode( '=', urldecode($mesg));
/*
    echo "mesg = $mesg\n";
    echo "message = $message\n";
    echo "\$_POST\n";
    print_r($_POST);
    echo '</pre>';
*/

    if ($message != 'Processed successfully') {
        return array(null, $message);
    }
    return array( $orderNo, $message);
}


function sendDSSSTransaction($link, $orderNo, $ssnId )
{
    // Get the information for dotransaction
    list($user, $device) = explode("|", $_COOKIE["LoginUser"]);
    $type = 'DSSS';
    $code = 'I';
    $object = $ssnId ;
    $location = '  ';
    $subLocation = 'DC'; // issn status
    $source = 'SSSSSSSSS';
    $qty = 0; // qty on the new issn
    $reference = $orderNo;
    $reference .=  '|' . 'Split ISSN for Despatch' ;

    // get current location
    {
        $query = "SELECT WH_ID, LOCN_ID 
                  FROM ISSN 
                  WHERE SSN_ID = '" . $ssnId . "' ";
        if (!($Result = ibase_query($link, $query))) {
            return array(null, null, 'Unable to read issn location!');
        }
        if (($Row = ibase_fetch_row($Result))) {
            $location =  $Row[0] . $Row[1];
            ibase_free_result($Result);
            unset($Result);
        }
    } 
/*
    echo '<h1>DSOL</h1>';
    echo '<pre>';
    echo "type = $type\n";
    echo "code = $code\n";
    echo "object = $object\n";
    echo "location = $location\n";
    echo "subLocation = $subLocation\n";
    echo "reference = $reference\n";
    echo "qty = $qty\n";
    echo "source = $source\n";
    echo "user = $user\n";
    echo "device = $device\n";
*/
    $mesg = dotransaction_response($type, $code, $object, $location, $subLocation, $reference, $qty, $source, $user, $device);
    list($dummy, $message) = explode( '|', urldecode($mesg));
/*
    echo "mesg = $mesg\n";
    echo "message = $message\n";
    echo "\$_POST\n";
    print_r($_POST);
    echo '</pre>';
*/

    if ($message != 'Processed successfully') {
        return array(null, $message);
    }
    list($message2, $Id) = explode( '=', $dummy);
    return array( $Id, $message);
}


function sendDSUQTransaction($link, $orderNo  )
{
    // Get the information for dotransaction
    list($user, $device) = explode("|", $_COOKIE["LoginUser"]);
    $type = 'DSUQ';
    $code = 'I';
    $object = $orderNo ;
    $location = '  ';
    $subLocation = ''; 
    $source = 'SSSSSSSSS';
    $qty = 0; // qty on the new issn
    $reference = $orderNo;

/*
    echo '<h1>DSOL</h1>';
    echo '<pre>';
    echo "type = $type\n";
    echo "code = $code\n";
    echo "object = $object\n";
    echo "location = $location\n";
    echo "subLocation = $subLocation\n";
    echo "reference = $reference\n";
    echo "qty = $qty\n";
    echo "source = $source\n";
    echo "user = $user\n";
    echo "device = $device\n";
*/
    $mesg = dotransaction_response($type, $code, $object, $location, $subLocation, $reference, $qty, $source, $user, $device);
    //list($dummy, $message) = explode( '|', urldecode($mesg));
    $message = urldecode($mesg);
    //echo "mesg = $mesg\n";
    //echo "message = $message\n";
    //echo "\$_POST\n";
    //print_r($_POST);
    //echo '</pre>';

    if ($message != 'Processed successfully') {
        return array($message);
    }
    return array( $message);
}


function sendGRNXTransaction($link, $grn)
{
    // Get the information for dotransaction
    list($user, $device) = explode("|", $_COOKIE["LoginUser"]);
    $type = 'GRNX';
    $code = 'G';
    $object = $_POST['docket_no'];
    if ($hasShippedDate == 'y') {
        $object = '|' . $shippedDate;
    }
    $location = $carrier;
    $subLocation = $vehReg;
    $source = 'SSBSSKSSS';
    $qty = 0;
    $comment = 'Cancelled at ' . date('Y-m-d H:i:s') . ' by ' . $user;
    $subLocation = $_POST['grn_no'];
    $object = substr($comment,0,30);
    $location = substr($comment,30,10);
    $reference = substr($comment,40,40);
    $mesg = dotransaction_response($type, $code, $object, $location, $subLocation, $reference, $qty, $source, $user, $device);

/*
    echo '<h1>GRNX</h1>';
    echo '<pre>';
    echo "type = $type\n";
    echo "code = $code\n";
    echo "object = $object\n";
    echo "location = $location\n";
    echo "subLocation = $subLocation\n";
    echo "reference = $reference\n";
    echo "qty = $qty\n";
    echo "source = $source\n";
    echo "user = $user\n";
    echo "device = $device\n";
    echo "mesg = $mesg\n";
    echo "\$_POST\n";
    print_r($_POST);
    echo '</pre>';
    exit(0);
*/

    return array($grn, $orderNo, $message);
}


function sendGRNVTransaction($Link)
{
    // Check to see if we already have a line no
    $lineNo = 0;
    $sql = 'SELECT DISTINCT SUBSTRING(SSN_ID FROM 5 FOR 2) FROM ISSN WHERE SSN_ID LIKE \'' . $_POST['real_grn_no'] . '%\' AND PROD_ID = ?';
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        $r = ibase_execute($q, $_POST['product_id']);
        if ($r) {
            $d = ibase_fetch_row($r);
            if ($d) {
                $lineNo = $d[0];
            }
            ibase_free_result($r);
        }
        ibase_free_query($q);
    }
    // If we don't then get the next line no
    if ($lineNo == 0) {
        $lineNo = 1;
        $sql = 'SELECT DISTINCT SUBSTRING(SSN_ID FROM 5 FOR 2) FROM ISSN WHERE SSN_ID LIKE \'' . $_POST['real_grn_no'] . '%\'';
        $q = ibase_prepare($Link, $sql);
        if ($q) {
            $r = ibase_execute($q);
            if ($r) {
                $d = ibase_fetch_row($r);
                $data = array();
                while ($d) {
                    $data[] = $d[0];
                    $d = ibase_fetch_row($r);
                }
                if (!empty($data)) {
                    $lineNo = max($data) + 1;
                }
                ibase_free_result($r);
            }
            ibase_free_query($q);
        }
/*
        $sql = 'SELECT LAST_LINE_NO FROM GRN WHERE GRN = ? AND ORDER_NO = ?';
        $q = ibase_prepare($Link, $sql);
        if ($q) {
            $r = ibase_execute($q, $grn, $orderNo);
            if ($r) {
                $d = ibase_fetch_row($r);
                if ($d) {
                    $lineNo = $d[0] + 1;
                }
                ibase_free_result($r);
            }
            ibase_free_query($q);
        }
*/
    }

    // Get the information for dotransaction
    list($user, $device) = explode("|", $_COOKIE["LoginUser"]);
    $type = 'GRNV';
    $code = 'P';
    $object = $_POST['product_id'];
    $location = $_POST['receive_location'];;
    $subLocation = $_POST['real_grn_no'];
    $source = 'SSBSSKSSS';
    $qty = $_POST['recvd'];
    //$comment = 'Cancelled at ' . date('Y-m-d H:i:s') . ' by ' . $user;
    $reference = $_POST['type'] . '|'
               . $_POST['real_order_no'] . '|'
               . $lineNo . '|'
               . (int)$_POST['qty1'] . '|'
               . (int)$_POST['qty2'] . '|'
               . max((int)$_POST['qty3'],1) . '|'
               . (int)$_POST['qty4'] . '|'
               . substr($_POST['printer_id'], -1, 1);
    $mesg = urldecode(dotransaction_response($type, $code, $object, $location, $subLocation, $reference, $qty, $source, $user, $device));
    if (substr($mesg, 0, 8) != "message=") {
        return array('Unknown error');
    }
    $mesg = substr($mesg, 8);
    $result = explode('|', $mesg);

/*
    echo '<h1>GRNV</h1>';
    echo '<pre>';
    echo "type = $type\n";
    echo "code = $code\n";
    echo "object = $object\n";
    echo "location = $location\n";
    echo "subLocation = $subLocation\n";
    echo "reference = $reference\n";
    echo "qty = $qty\n";
    echo "source = $source\n";
    echo "user = $user\n";
    echo "device = $device\n";
    echo "mesg = $mesg\n";
print_r($result);
    echo "\$_POST\n";
    print_r($_POST);
    echo '</pre>';
    exit(0);
*/
    return $result;
}

function sendNIOBTransaction($Link, $ssn)
{
    list($user, $device) = explode("|", $_COOKIE["LoginUser"]);
    $type = 'NIOB';
    $code = 'A';
    $object = $ssn;
    $location = $_POST['receive_location'];;
    $subLocation = $_POST['real_grn_no'];
    $source = 'SSBSSKSSS';
    $qty = 1;
    $reference = $_POST['variety_id'];
    $mesg = urldecode(dotransaction_response($type,
                                             $code,
                                             $object,
                                             $location,
                                             $subLocation,
                                             $reference,
                                             $qty,
                                             $source,
                                             $user,
                                             $device));
    if (substr($mesg, 0, 8) != "message=") {
        return array('Unknown error');
    }
    $mesg = substr($mesg, 8);
    $result = explode('|', $mesg);
    return $result;
}

function sendNIBCTransaction($Link, $ssn)
{
    list($user, $device) = explode("|", $_COOKIE["LoginUser"]);
    $type = 'NIBC';
    $code = 'A';
    $object = $ssn;
    $location = $_POST['receive_location'];
    $subLocation = $_POST['real_grn_no'];
    $source = 'SSBSSKSSS';
    $qty = 1;
    $reference = $_POST['brand_id'];
    $mesg = urldecode(dotransaction_response($type,
                                             $code,
                                             $object,
                                             $location,
                                             $subLocation,
                                             $reference,
                                             $qty,
                                             $source,
                                             $user,
                                             $device));
    if (substr($mesg, 0, 8) != "message=") {
        return array('Unknown error');
    }
    $mesg = substr($mesg, 8);
    $result = explode('|', $mesg);
    return $result;
}

function sendUIO1Transaction($Link, $ssn, $third_no)
{
    list($user, $device) = explode("|", $_COOKIE["LoginUser"]);
    $type = 'UIO1';
    $code = 'A';
    $object = $ssn;
    $location = $_POST['receive_location'];;
    $subLocation = $_POST['real_grn_no'];
    $source = 'SSBSSKSSS';
    $qty = $_POST['recvd'];
    $reference = $third_no;
    $mesg = urldecode(dotransaction_response($type,
                                             $code,
                                             $object,
                                             $location,
                                             $subLocation,
                                             $reference,
                                             $qty,
                                             $source,
                                             $user,
                                             $device));
    if (substr($mesg, 0, 8) != "message=") {
        return array('Unknown error');
    }
    $mesg = substr($mesg, 8);
    $result = explode('|', $mesg);
    return $result;
}
