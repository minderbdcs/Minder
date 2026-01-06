<?php

function sendGRNDTransaction($link)
{
    // Set default just in case the value wasn't provided
    $printerId = isset($_POST['printer_id']) ? $_POST['printer_id'] : '';
    $docketNo = isset($_POST['docket_no']) ? $_POST['docket_no'] : '';
    $orderNo = isset($_POST['order_no']) ? $_POST['order_no'] : '';
    $carrier = isset($_POST['carrier']) ? $_POST['carrier'] : '';
    $vehReg = isset($_POST['veh_reg']) ? $_POST['veh_reg'] : '';
    $ownedBy = isset($_POST['owned_by']) ? $_POST['owned_by'] : '';
    $sentBy = isset($_POST['sent_by']) ? $_POST['sent_by'] : '';
    $pkgs = isset($_POST['pkgs']) ? $_POST['pkgs'] : '';
    $container = (isset($_POST['container']) && ($_POST['container'] == 'y')) ? 'Y' : 'N';
    $containerNo = isset($_POST['container_no']) ? $_POST['container_no'] : '';
    $containerType = isset($_POST['container_type']) ? $_POST['container_type'] : '';
    $palletType = isset($_POST['pallet_type']) ? $_POST['pallet_type'] : 'NONE';
    $palletQty = isset($_POST['pallet_qty']) ? $_POST['pallet_qty'] : '';
    $receivedQty = isset($_POST['recvd']) ? $_POST['recvd'] : '';
    $shippedDate = $_POST['shipped_date'] ? $_POST['shipped_date'] : '';
    $problem = isset($_POST['problem']) ? $_POST['problem'] : '';
    $hirePallets = isset($_POST['hire_pallets']) ? $_POST['hire_pallets'] : '';
    $hireQty = (isset($_POST['hire_qty']) && $_POST['hire_qty'] != '') ? $_POST['hire_qty'] : '0';
    $hirePackaging = isset($_POST['hire_packaging']) ? $_POST['hire_packaging'] : '';
    $hirePackagingType = isset($_POST['hire_packaging_type']) ? $_POST['hire_packaging_type'] : '';
    $packagingCrateQty = isset($_POST['packaging_crate_qty']) ? $_POST['packaging_crate_qty'] : '';
    $hasShippedDate = 'n';
    $shippedDate = '';
    if (isset($_POST['shipped_date']) && $_POST['shipped_date'] == 'y') {
        $hasShippedDate = 'y';
        $shippedDate = $_POST['shipped_year'] . $_POST['shipped_month'] . $_POST['shipped_day'];
    }

    // If this is a load without an order number then generate the next one and set the line number
    if (($_POST['type'] == "LD" || $_POST['type'] == "LP") && ($orderNo == "")) {
        $query = "SELECT LOAD_ID FROM GET_LOAD_NO ";
        if (!($Result = ibase_query($link, $query))) {
            return array(null, null, 'Unable to read order!');
        }
        if (($Row = ibase_fetch_row($Result))) {
            $orderNo =  $Row[0];
            ibase_free_result($Result);
            unset($Result);
        }

        //calc the next load
        $lineNo = "1";
    } else {
        // Check to see if we already have a line no
        $lineNo = 0;
        $sql = 'SELECT DISTINCT SUBSTRING(SSN_ID FROM 5 FOR 2) FROM ISSN WHERE SSN_ID LIKE \'' . $grn . '%\'';
        $q = ibase_prepare($Link, $sql);
        if ($q) {
            $r = ibase_execute($q, $orderNo);
            if ($r) {
                $d = ibase_fetch_row($r);
                if ($d) {
                    $lineNo = $d[0] + 1;
                }
                ibase_free_result($r);
            }
            ibase_free_query($q);
        }
        // If we don't then get the next line no
        if ($lineNo == 0) {
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
        }
    }


    // Get the information for dotransaction
    list($user, $device) = explode("|", $_COOKIE["LoginUser"]);
    $type = 'GRND';
    $code = 'B';
    $object = $_POST['docket_no'] . '|';
    if ($hasShippedDate == 'y') {
        $object = $object .  $shippedDate;
    }
    $location = $carrier;
    $subLocation = $vehReg;
    $reference = ($_POST['type']) . '|'
               . $orderNo . '|'
               . $lineNo . '|'
               . $container . '|'
               . $hirePallets. '|'
               . $hireQty . '|'
               . $hirePackaging . '|'
               . $hirePackagingType . '|'
               . $packagingCrateQty ;
    $source = 'SSBSSKSSS';
    $qty = $pkgs;

    $mesg = dotransaction_response($type, $code, $object, $location, $subLocation, $reference, $qty, $source, $user, $device);
    list($dummy1, $grn, $dummy2, $orderNo, $dummy3, $message) = explode(':', urldecode($mesg));

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
        return array(null, null, $message);
    }


    $type = 'GRND';
    $code = 'L';
    $object = $grn . '|';
    $location = $ownedBy;
    $subLocation = $sentBy;
/*
    $reference = $_POST['type'] . '|'
               . $orderNo . '||'
               . $printerId;
*/
    $reference = $_POST['type'] . '|'
               . $orderNo . '|'
               . $containerNo . '|'
               . $containerType . '|'
               . $printerId;
    $source = 'SSBSSKSSS';
    $qty = 0;
    $sql = 'SELECT DEFAULT_GRN_LABELS FROM CONTROL';
    if (!($Result = ibase_query($link, $query))) {
        return array(null, null, 'Unable to read order!');
    }
    if (($Row = ibase_fetch_row($Result))) {
        if ($Row[0] > 0) {
            $qty = $_POST['pkgs'];
        }
        ibase_free_result($Result);
        unset($Result);
    }

    $mesg = dotransaction_response($type, $code, $object, $location, $subLocation, $reference, $qty, $source, $user, $device);

/*
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
    echo "mesg = $mesg\n";
    echo '</pre>';
*/

    return array($grn, $orderNo, $message);
}


function sendGRNCTransaction($link, $grn)
{
    $comment = isset($_POST['comment']) ? $_POST['comment'] : '';
    // Get the information for dotransaction
    list($user, $device) = explode("|", $_COOKIE["LoginUser"]);
    $type = 'GRNC';
    $code = 'C';
    $object = $_POST['docket_no'];
    if ($hasShippedDate == 'y') {
        $object = '|' . $shippedDate;
    }
    $location = $carrier;
    $subLocation = $vehReg;
    $source = 'SSBSSKSSS';
    $qty = 0;
    $comment = $comment;
    $subLocation = $grn;
    $object = substr($comment,0,30);
    $location = substr($comment,30,10);
    $reference = substr($comment,40,40);

    $mesg = dotransaction_response($type, $code, $object, $location, $subLocation, $reference, $qty, $source, $user, $device);

/*
    echo '<h1>GRNC</h1>';
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
    echo "dummy1 = $dummy1\n";
    echo "grn = $grn\n";
    echo "dummy2 = $dummy2\n";
    echo "tranLoad = $tranLoad\n";
    echo "dummy3 = $dummy3\n";
    echo "message = $message\n";
    echo "\$_POST\n";
    print_r($_POST);
    echo '</pre>';
    exit(0);
*/

    return array($grn, $orderNo, $message);
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