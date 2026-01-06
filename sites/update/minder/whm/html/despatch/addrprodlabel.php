<?php
if (!isset($_COOKIE['LoginUser'])) {
    header('Location: /whm/');
    exit(0);
}

include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include 'transaction.php';
//echo get_include_path();
//exit();
//****************************************************************/


/**
 * getPrinterOpts
 *
 * @param $Link
 */
function getPrinterOpts($Link) {
    $sql = 'SELECT DEVICE_ID FROM SYS_EQUIP WHERE DEVICE_TYPE = \'PR\' ORDER BY DEVICE_ID';
    if (!($result = ibase_query($Link, $sql))) {
        exit('Unable to read printers!');
    }
    $printerOpts = array();
    while (($row = ibase_fetch_row($result))) {
        $printerOpts[$row[0]] = $row[0];
    }
    ibase_free_result($result);

    return $printerOpts;
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
    $sql = 'SELECT IP_ADDRESS FROM SYS_EQUIP WHERE DEVICE_ID = ?';
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
 * getPrinterDirectory
 *
 * @param $Link
 * @param string $printerId
 * @return string or null
 */
function getPrinterDirectory($Link, $printerId) {
    $result = '';
    $sql = 'SELECT WORKING_DIRECTORY FROM SYS_EQUIP WHERE DEVICE_ID = ?';
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
 * getProductInfo
 *
 * @param $Link
 */
function getProductInfo($Link) {
    $sql = 'SELECT PROD_PROFILE.PROD_ID,
                   PROD_PROFILE.SHORT_DESC,
                   UOM.CODE,
                   UOM.DESCRIPTION,
                   PROD_PROFILE.TEMPERATURE_ZONE
            FROM PROD_PROFILE 
            LEFT OUTER JOIN UOM ON PROD_PROFILE.UOM = UOM.CODE
            ORDER BY PROD_ID';
            /*  AND UOM.UOM_TYPE = \'UT\'*/
    if (!($result = ibase_query($Link, $sql))) {
        exit('Unable to read products!');
    }
    $productInfo = array();
    while (($row = ibase_fetch_row($result))) {
        $productInfo[$row[0]] = array($row[1], $row[2], $row[3], $row[4]);
    }
    ibase_free_result($result);

    return $productInfo;
}


/**
 * getOrderProduct
 *
 * @param $Link
 */
function getOrderProduct($Link, $orderNo) {
    $productInfo = array();
    $sql = 'SELECT DISTINCT PICK_ITEM.PROD_ID
            FROM PICK_ITEM
            WHERE PICK_ITEM.PICK_ORDER = ?
            AND PICK_ITEM.PROD_ID IS NOT NULL
            AND PICK_ITEM.PICK_LINE_STATUS IN (\'AL\',\'PG\',\'PL\',\'DS\',\'OP\',\'UP\',\'DC\')
            ORDER BY PICK_ITEM.PROD_ID';
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        if (!($result = ibase_execute($q, $orderNo))) {
           exit('Unable to read orders products!');
        }
        while (($row = ibase_fetch_row($result))) {
            $productInfo[$row[0]] = array($row[0]);
        }
        ibase_free_result($result);
        ibase_free_query($q);
    }
    $sql = 'SELECT DISTINCT ISSN.PROD_ID
            FROM PICK_ITEM
            JOIN ISSN ON ISSN.SSN_ID = PICK_ITEM.SSN_ID
            WHERE PICK_ITEM.PICK_ORDER = ?
            AND PICK_ITEM.SSN_ID IS NOT NULL
            AND PICK_ITEM.PICK_LINE_STATUS IN (\'AL\',\'PG\',\'PL\',\'DS\',\'OP\',\'UP\',\'DC\')
            ORDER BY ISSN.PROD_ID';
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        if (!($result = ibase_execute($q, $orderNo))) {
           exit('Unable to read orders products!');
        }
        while (($row = ibase_fetch_row($result))) {
            $productInfo[$row[0]] = array($row[0]);
        }
        ibase_free_result($result);
        ibase_free_query($q);
    }


    return $productInfo;
}


/**
 * getDefaultOwnedById
 *
 * @param $Link
 */
function getDefaultOwnedById($Link) {
    $ownedById = null;

    $sql = 'SELECT COMPANY_ID FROM CONTROL';
    if (!($result = ibase_query($Link, $sql))) {
        return null;
    }
    while (($row = ibase_fetch_row($result))) {
        $ownedById = $row[0];
    }
    ibase_free_result($result);

    return $ownedById;
}

/**
 * getDefaultDespatchPrinter
 *
 * @param $Link
 */
function getDefaultDespatchPrinter($Link) {
    $despatchPrinterId = null;

    $sql = 'SELECT DEFAULT_PICK_PRINTER FROM CONTROL';
    if (!($result = ibase_query($Link, $sql))) {
        return null;
    }
    while (($row = ibase_fetch_row($result))) {
        $despatchPrinterId = $row[0];
    }
    ibase_free_result($result);

    return $despatchPrinterId;
}


/**
 * getSentByOpts
 *
 * @param $Link
 */
function getSentByOpts($Link) {
    $sql = 'SELECT PERSON_ID, FIRST_NAME FROM PERSON WHERE SUBSTRING(PERSON_TYPE FROM 1 FOR 2) IN (\'CO\', \'CS\', \'CU\') ORDER BY FIRST_NAME, PERSON_ID';

    if (!($result = ibase_query($Link, $sql))) {
        exit('Unable to read sent by!');
    }
    $sentByOpts = array();
    while (($row = ibase_fetch_row($result))) {
        $sentByOpts[$row[0]] = $row[1];
    }
    ibase_free_result($result);

    return $sentByOpts;
}


/**
 * getPalletOpts
 *
 * @param $Link
 */
function getPalletOpts($Link) {
    $sql = 'SELECT CODE, DESCRIPTION FROM OPTIONS WHERE GROUP_CODE = \'PALL_OWNER\' ORDER BY DESCRIPTION';
    if (!($result = ibase_query($Link, $sql))) {
        exit('Unable to read owners!');
    }
    $palletByOpts = array();
    while (($row = ibase_fetch_row($result))) {
        $palletByOpts[$row[0]] = $row[1];
    }
    ibase_free_result($result);

    return $palletByOpts;
}


/**
 * getPackagingOpts
 *
 * @param $Link
 */
function getPackagingOpts($Link) {
    $sql = 'SELECT CODE, DESCRIPTION FROM OPTIONS WHERE GROUP_CODE = \'PACK_OWNER\' ORDER BY DESCRIPTION';
    if (!($result = ibase_query($Link, $sql))) {
        exit('Unable to read owners!');
    }
    $packagingByOpts = array();
    while (($row = ibase_fetch_row($result))) {
        $packagingByOpts[$row[0]] = $row[1];
    }
    ibase_free_result($result);

    return $packagingByOpts;
}


/**
 * getPackagingTypeOpts
 *
 * @param $Link
 */
function getPackagingTypeOpts($Link) {
    $sql = 'SELECT CODE, DESCRIPTION FROM OPTIONS WHERE GROUP_CODE = \'PACK_TYPE\' ORDER BY DESCRIPTION';
    if (!($result = ibase_query($Link, $sql))) {
        exit('Unable to read owners!');
    }
    $packagingByOpts = array();
    while (($row = ibase_fetch_row($result))) {
        $packagingByOpts[$row[0]] = $row[1];
    }
    ibase_free_result($result);

    return $packagingByOpts;
}


/**
 * getCarrierOpts
 *
 * @param $Link
 */
function getCarrierOpts($Link) {
    $sql = 'SELECT CARRIER_ID FROM CARRIER ORDER BY CARRIER_ID';
    if (!($result = ibase_query($Link, $sql))) {
        exit('Unable to read carrier!');
    }
    $carrierOpts = array();
    while (($row = ibase_fetch_row($result))) {
        $carrierOpts[$row[0]] = $row[0];
    }
    ibase_free_result($result);

    return $carrierOpts;
}


/**
 * getOwnedByOpts
 *
 * @param $Link
 */
function getOwnedByOpts($Link) {
    $sql = 'SELECT COMPANY_ID, NAME FROM COMPANY ORDER BY NAME';
    if (!($result = ibase_query($Link, $sql))) {
        exit('Unable to read owners!');
    }
    $ownedByOpts = array();
    while (($row = ibase_fetch_row($result))) {
        $ownedByOpts[$row[0]] = $row[1];
    }
    ibase_free_result($result);

    return $ownedByOpts;
}



/**
 * getOrderNoDespatch
 *
 * @param $Link
 * @param string $orderNo
 * @return string or null
 */
function getOrderNoDespatch($Link, $orderNo) {
    $result = '';
    $sql = 'SELECT DESPATCH_ID FROM PICK_DESPATCH WHERE AWB_CONSIGNMENT_NO = ?';
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        $r = ibase_execute($q, $orderNo);
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
 * getSalesOrderStatus
 *
 * @param $Link
 * @param string $orderNo
 * @return string or null
 */
function getSalesOrderStatus($Link, $orderNo) {
    $sql = 'SELECT PICK_STATUS FROM PICK_ORDER WHERE PICK_ORDER = ?';
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        $r = ibase_execute($q, $orderNo);
        if ($r) {
            $d = ibase_fetch_row($r);
            if ($d) {
                return $d[0];
            }
            ibase_free_result($r);
        }
        ibase_free_query($q);
    }
    return '';
}


/**
 * getSalesOrderInfo
 *
 * @param $Link
 * @param string $orderNo
 * @return string or null
 */
function getSalesOrderInfo($Link, $orderNo) {
    $result = array();
    $sql = 'SELECT CONTACT_NAME,
            CUSTOMER_PO_WO,
            SPECIAL_INSTRUCTIONS1, 
            P_SAME_AS_INVOICE_TO,
            PERSON_ID,
            P_PERSON_ID,
            P_TITLE,
            P_FIRST_NAME,
            P_LAST_NAME,
            P_ADDRESS_LINE1,
            P_ADDRESS_LINE2,
            P_ADDRESS_LINE3,
            P_ADDRESS_LINE4,
            P_ADDRESS_LINE5,
            D_TITLE,
            D_FIRST_NAME,
            D_LAST_NAME,
            D_ADDRESS_LINE1,
            D_ADDRESS_LINE2,
            D_ADDRESS_LINE3,
            D_ADDRESS_LINE4,
            D_ADDRESS_LINE5,
            S_SAME_AS_SOLD_FROM,
            SUPPLIER_ID,
            S_TITLE,
            S_FIRST_NAME,
            S_LAST_NAME,
            S_ADDRESS_LINE1,
            S_ADDRESS_LINE2,
            S_ADDRESS_LINE3,
            S_PHONE,
            COMPANY_ID,
            P_CITY,
            P_STATE,
            P_POST_CODE,
            P_COUNTRY,
            D_CITY,
            D_STATE,
            D_POST_CODE,
            D_COUNTRY,
            S_CITY,
            S_STATE,
            S_POST_CODE,
            S_COUNTRY
            FROM PICK_ORDER 
            WHERE PICK_ORDER = ?';
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        $r = ibase_execute($q, $orderNo);
        if ($r) {
            while(( $d = ibase_fetch_row($r))) { 
                $result = array($d[0], $d[1], $d[2], $d[3], $d[4],
                                $d[5], $d[6], $d[7], $d[8], $d[9],
                                $d[10], $d[11], $d[12], $d[13], $d[14],
                                $d[15], $d[16], $d[17], $d[18], $d[19],
                                $d[20], $d[21], $d[22], $d[23], $d[24],
                                $d[25], $d[26], $d[27], $d[28], $d[29],
                                $d[30], $d[31], $d[32], $d[33], $d[34],
                                $d[35], $d[36], $d[37], $d[38], $d[39],
                                $d[40], $d[41], $d[42], $d[43] );
            }
            ibase_free_result($r);
        }
        ibase_free_query($q);
    }
    return $result;
}


/**
 * getCompanyInfo
 *
 * @param $Link
 * @param string $companyId
 * @return string or null
 */
function getCompanyInfo($Link, $companyId) {
    $result = array();
    //echo "company is " . $companyId;
    $sql = 'SELECT NAME,
            ADDRESS1,
            ADDRESS2,
            ADDRESS3,
            PHONE_NO
            FROM COMPANY 
            WHERE COMPANY_ID = ?';
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        $r = ibase_execute($q, $companyId);
        if ($r) {
            while(( $d = ibase_fetch_row($r))) { 
                $result = array($d[0], $d[1], $d[2], $d[3], $d[4]);
            }
            ibase_free_result($r);
        }
        ibase_free_query($q);
    }
    return $result;
}


/**
 * getNextLabel
 *
 * @param $Link
 * @param string $consignmentNo
 * @return string or null
 */
function getNextLabel($Link, $consignmentNo) {
    $result = array();

    $sql = ' SELECT FIRST 1 PACK_ID.PACK_ID, PACK_ID.DESPATCH_LABEL_NO
           FROM PICK_DESPATCH
           JOIN PACK_ID ON PICK_DESPATCH.DESPATCH_ID = PACK_ID.DESPATCH_ID
           WHERE PICK_DESPATCH.AWB_CONSIGNMENT_NO = ?
             AND PACK_ID.DESPATCH_LABEL_NO IS NOT NULL  
             AND PACK_ID.LABEL_PRINTED_DATE IS NULL   
           ORDER BY PACK_ID.DESPATCH_LABEL_NO ';
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        $r = ibase_execute($q, $consignmentNo);
        if ($r) {
            while(( $d = ibase_fetch_row($r))) { 
                $result = array($d[0], $d[1] );
            }
            ibase_free_result($r);
        }
        ibase_free_query($q);
    }
    return $result;
}


/**
 * updateLabel
 *
 * @param $Link
 * @param string $packId
 * @return string or null
 */
function updateLabel($Link, $packId, $productId, $qty) {

    $sql = ' UPDATE PACK_ID SET LABEL_PRINTED_DATE = \'NOW\',
             PROD_ID = ? ,
             QTY = ?
             WHERE PACK_ID.PACK_ID  = ? ';
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        $r = ibase_execute($q, $productId, $qty, $packId);
        //ibase_free_result($r);
        ibase_free_query($q);
    }
    //return $result;
}


/**
 * getSalesSSNOrder
 *
 * @param $Link
 * @param string $ssnId
 * @return string or null
 */
function getSalesSSNOrder($Link, $ssnId) {
    $sql = 'SELECT PI.PICK_ORDER FROM PICK_ITEM_DETAIL PID JOIN PICK_ITEM PI ON PI.PICK_LABEL_NO = PID.PICK_LABEL_NO WHERE PID.SSN_ID = ?';
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        $r = ibase_execute($q, $ssnId);
        if ($r) {
            $d = ibase_fetch_row($r);
            if ($d) {
                return $d[0];
            }
            ibase_free_result($r);
        }
        ibase_free_query($q);
    }
    return '';
}


/**
 * htmlInputText
 *
 * @param string $name
 * @param string $val
 */
function htmlInputText($name, $val, $extra = array()) {
    if (isset($_POST[$name])) $val = $_POST[$name];
    echo '<input type="text" ';
    if (!isset($extra['id'])) echo 'id="' . $name . '" ';
    foreach ($extra as $k => $v) {
        echo $k . '="' . htmlentities($v, ENT_QUOTES) . '" ';
    }
    echo 'name="' . $name . '" value="' . htmlentities($val, ENT_QUOTES) . '" autocomplete="off" />';
}


/**
 * htmlInputHidden
 *
 * @param string $name
 * @param string $val
 */
function htmlInputHidden($name, $val, $extra = array()) {
    echo '<input type="hidden" ';
    if (!isset($extra['id'])) echo 'id="' . $name . '" ';
    foreach ($extra as $k => $v) {
        echo $k . '="' . htmlentities($v, ENT_QUOTES) . '" ';
    }
    echo 'name="' . $name . '" value="' . htmlentities($val, ENT_QUOTES) . '" />';
}


/**
 * htmlInputCheckbox
 *
 * @param string $name
 * @param string $val
 */
function htmlInputCheckbox($name, $val, $extra = array()) {
    echo '<input type="checkbox" ';
    if (!isset($extra['id'])) echo 'id="' . $name . '" ';
    foreach ($extra as $k => $v) {
        echo $k . '="' . htmlentities($v, ENT_QUOTES) . '" ';
    }
    echo 'name="' . $name . '" value="' . htmlentities($val, ENT_QUOTES) . '"';
    if (isset($_POST[$name])) {
        if ($_POST[$name] == $val) {
            echo ' checked="checked"';
        }
    }
    echo ' />';
}


/**
 * htmlInputRadio
 * @param string $name
 * @param string $val
 */
function htmlInputRadio($name, $val, $extra = array()) {
    echo '<input type="radio" ';
    if (!isset($extra['id'])) echo 'id="' . $name . '" ';
    foreach ($extra as $k => $v) {
        echo $k . '="' . htmlentities($v, ENT_QUOTES) . '" ';
    }
    echo 'name="' . $name . '" value="' . htmlentities($val, ENT_QUOTES) . '"';
    if (isset($_POST[$name])) {
        if ($_POST[$name] == $val) {
            echo ' checked="checked"';
        }
    }
    echo ' />';
}


/**
 * htmlSelect
 *
 * @param string $name
 * @param string $opts
 */
function htmlSelect($name, $val, $opts, $extra = array()) {
    echo '<select ';
    if (!isset($extra['id'])) echo 'id="' . $name . '" ';
    foreach ($extra as $k => $v) {
        echo $k . '="' . htmlentities($v, ENT_QUOTES) . '" ';
    }
    echo 'name="' . $name . '">';
    foreach ($opts as $k => $v) {
        echo '<option value="' . htmlentities($k, ENT_QUOTES) . '"';
        if ($val != null) {
            if (is_array($val)) {
                if (in_array($k, $val)) {
                    echo ' selected="selected"';
                }
            } else {
                if ($val == $k) {
                    echo ' selected="selected"';
                }
            }
        } elseif (isset($_POST[$name])) {
            if (is_array($_POST[$name])) {
                if (in_array($k, $_POST[$name])) {
                    echo ' selected="selected"';
                }
            } else {
                if ($_POST[$name] == $k) {
                    echo ' selected="selected"';
                }
            }
        }
        echo '>' . htmlentities($v, ENT_QUOTES) . '</option>';
    }
    echo '</select>';
}


/**
 * htmlTextArea
 *
 * @param string $name
 * @param string $val
 */
function htmlTextArea($name, $val, $extra = array()) {
    if (isset($_POST[$name])) $val = $_POST[$name];
    echo '<textarea ';
    if (!isset($extra['id'])) echo 'id="' . $name . '" ';
    foreach ($extra as $k => $v) {
        echo $k . '="' . htmlentities($v, ENT_QUOTES) . '" ';
    }
    echo 'name="' . $name . '">' . htmlentities($val, ENT_QUOTES) . '</textarea>';
}


/**
 * validatePageDelivery
 *
 * @TODO Date validation isn't working - allows 31/2/2007
 * @param $Link
 */
function validatePageDelivery($Link) {
    $errors = array();
    {
        if ($_POST['order_no'] != '') {
            if ((getSalesOrderStatus($Link, $_POST['order_no']) != 'OP') and
                (getSalesOrderStatus($Link, $_POST['order_no']) != 'DA')) {
                $errors['order_no'] = 'Invalid or closed order no';
            } else {
                $orderProduct =  getOrderProduct($Link, $_POST['order_no']);
                if (count($orderProduct) == 0)
                {
                    $errors['order_no'] = 'No available Lines on order';
                }
            }
        } else {
                $errors['order_no'] = 'Invalid order no';
        }

        if (!array_key_exists($_POST['printer_id'], getPrinterOpts($Link))) {
            $errors['printer_id'] = 'Invalid printer_id';
        }

    }

    return $errors;
}



/**
 * valdiatePageVerify
 *
 * @param $Link
 */
function validatePageVerify($Link) {
    $errors = array();
    if ($_POST['recvd'] == '') {
        $errors['recvd'] = 'Please enter a received quantity';
    }
    $t = (int)$_POST['qty1'] * (int)$_POST['qty2']
       + (int)$_POST['qty3'] * (int)$_POST['qty4'];
    if ($t != $_POST['recvd']) {
        if ($t < $_POST['recvd']) {
            $errors['recvd'] = 'You aren\'t printing enough labels';
        } else {
            $errors['recvd'] = 'You are printing too many labels';
        }
    }
    if (!isset($_POST['complete']) || ($_POST['complete'] != 'y' && $_POST['complete'] != 'n')) {
        $errors['complete'] = 'Please indicate if the order is complete';
    }
    return $errors;
}


/**
 * validatePageSSN
 *
 * @TODO Date validation isn't working - allows 31/2/2007
 * @param $Link
 */
function validatePageSSN($Link) {
    $errors = array();
    {
        if ($_POST['ssn_id'] != '') {
            if (getSalesSSNOrder($Link, $_POST['ssn_id']) == '') {
                $errors['ssn_id'] = 'Invalid or Not Picked ISSN';
            }
/*
            else {
                $errors['ssn_id'] = getSalesSSNOrder($Link, $_POST['ssn_id']);
            }
*/

        } else {
                $errors['ssn_id'] = 'No ISSN';
        }

    }

    return $errors;
}


/**
 * showPageDelivery
 *
 * @param $Link
 */
function showPageDelivery($Link, $errors = array()) {
    $printerOpts = getPrinterOpts($Link);
    $dayOpts = array();
    for ($i = 1; $i <= 31; $i++) {
        if ($i < 10) {
            $dayOpts['0' . $i] = $i;
        } else {
            $dayOpts[$i] = $i;
        }
    }
    $monthOpts = array('01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Aug', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec');
    $year = date('Y');
    $yearOpts = array($year - 2 => substr($year - 2, -2), $year - 1 => substr($year - 1, -2), $year => substr($year,-2));
    $message = isset($_GET['message']) ? $_GET['message'] : '';
    include 'addrprodlabelorder.php';
}


/**
 * showPageVerify
 *
 * @param Resource $Link
 * @param Array $errors
 */
function showPageVerify($Link, $orderNo, $errors = array()) {

    $printerOpts = getPrinterOpts($Link);
    $productInfo = getProductInfo($Link);
    $orderProduct = getOrderProduct($Link, $orderNo);
    //-- $productNames not defined before
    //-- use $productInfo for testing purposes

    //-- $t = array_keys($productNames);
    //$t = array_keys($productInfo);
    $productIdOpts = array();
    foreach ($orderProduct as $Key_product => $Value_product) 
    {
        $productIdOpts[$Key_product] = array($productInfo[ $Key_product ]);
    }

    include 'addrprodlabelqtys.php';
}


/**
 * showPageSSN
 *
 * @param $Link
 */
function showPageSSN($Link, $errors = array()) {
    $dayOpts = array();
    for ($i = 1; $i <= 31; $i++) {
        if ($i < 10) {
            $dayOpts['0' . $i] = $i;
        } else {
            $dayOpts[$i] = $i;
        }
    }
    $monthOpts = array('01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Aug', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec');
    $year = date('Y');
    $yearOpts = array($year - 2 => substr($year - 2, -2), $year - 1 => substr($year - 1, -2), $year => substr($year,-2));
    $message = isset($_GET['message']) ? $_GET['message'] : '';
    include 'addrprodlabelssn.php';
}


if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
        echo("Unable to Connect!<BR>\n");
        exit();
}

$page = 0;
if (isset($_POST['page'])) {
    $page = $_POST['page'];
}
switch ($page) {
case 5:  // VERIFY submitted
    if (isset($_POST['action_back_x'])) {
        header('Location: despatch_menu.php');
        echo '<html><head><title>Redirect</title></head><body><p>You are being redirected to <a href="despatch_menu.php">despatch_menu.php"</a></p></body></html>';
    } else {
        if (isset($_POST['action_continue_x'])) {
            $errors = validatePageVerify($Link);
            $printerIp = getPrinterIp($Link, $_POST['printer_id']);
            if ($printerIp == '') {
                $errors['printer_id'] = 'No IP address for printer';
            }
            $printerPath = getPrinterDirectory($Link, $_POST['printer_id']);
            $formatPrinter =  getDefaultDespatchPrinter($Link) ;
            $formatPath = getPrinterDirectory($Link, $formatPrinter);
            $formatName = "despatch.fmt";
            if (empty($errors)) {
                $orderInfo = getSalesOrderInfo($Link, $_POST['real_order_no']);
                include 'addrprodlabeltrans.php';
                //$despatchId = getOrderNoDespatch($Link, $orderNo);
                $dbTran = ibase_trans(IBASE_DEFAULT, $Link);
                //if ($despatchId == '') {
                    // must get a despatch for this label set
                    // and create labels for this despatch
                    // if qty1 and qty2 <> 0 qty to print += qty1
                    // if qty3 and qty4 <> 0 qty to print += qty3
                    $result = sendDSOTTransaction($Link, $_POST['real_order_no'], $orderInfo);
                    //print_r ($result);
                //}
                // generate the label ids
                $result = sendDSOLTransaction($Link, $_POST['real_order_no']);
                //print_r ($result);
                if (isset($_POST['recorded'])) {
                    $_POST['recorded']++;
                }
                $result = array();
                $result[0] = '1' ;// the starting label to print
                $result[1] = (int)$_POST['qty1']; // qty of labels 1
                $result[2] = (int)$_POST['qty2']; // qty on labels 1
                $result[3] = '2' ;// the starting label to print of 2nd set
                $result[4] = (int)$_POST['qty3']; // qty of labels 2
                $result[5] = (int)$_POST['qty4']; // qty on labels 2

                {

                    {
                        //-- otherwise print labels
                        $productInfo = getProductInfo($Link);
                        $supplierInfo = getCompanyInfo($Link, $orderInfo[23]);
                        $companyInfo = getCompanyInfo($Link, $orderInfo[31]);
                        if (count($supplierInfo) == 0)
                        {
                            $orderInfo[23] = ''; // supplier is not a company
                        }
                        include 'Printer.php';
                        $p = new Printer($printerIp);

/*
' despatch barcode %despatchlabel%
' despatch barcode 1st char %despatchlabelprefix%
' despatch barcode after the 1st char %despatchlabelsuffix%
'
' deliver to name %deliverto%
' supplied by company name %suppliedby%
*/

                        //$loginUser = split('\|', $_COOKIE['LoginUser']);
			$loginUser = explode("|", $_COOKIE["LoginUser"]);
                        $p->data['userid'] = $loginUser[0];
                        $p->data['now'] = date('d/m/y H:i:s');
                        $p->data['orderno'] = $_POST['real_order_no'];
                        $p->data['prodid'] = $_POST['product_id'];
                        if (isset($productInfo[$_POST['product_id']][0])) {
                            $p->data['proddesc'] = $productInfo[$_POST['product_id']][0];
                        } else {
                            $p->data['proddesc'] = '';
                        }
                        if (isset($productInfo[$_POST['product_id']][2])) {
                            $p->data['uom'] = $productInfo[$_POST['product_id']][2];
                        } else {
                            $p->data['uom'] = '';
                        }
                        $p->data['contact'] = $orderInfo[0];
                        $p->data['customerpowo'] = $orderInfo[1];
                        $p->data['specialinstructions1'] = $orderInfo[2];
                        if ($orderInfo[17] == '' and $orderInfo[18] == '') // no delivery address line 1 or 2
                        {
                            $p->data['deliverto'] = $orderInfo[6] . ' ' . $orderInfo[7] . ' ' . $orderInfo[8];
                            $p->data['delivertoline1'] = $orderInfo[9];
                            $p->data['delivertoline2'] = $orderInfo[10];
                            if ($orderInfo[11] == '' and $orderInfo[12] == '' and $orderInfo[13] == '') // no person address line 3 or 4 or 5
                            {
                                $p->data['delivertoline3'] = $orderInfo[32] . ', ' . $orderInfo[33] . ' ' . $orderInfo[34];
                                $p->data['delivertoline4'] = $orderInfo[35];
                                $p->data['delivertoline5'] = '';
                            } else {
                                $p->data['delivertoline3'] = $orderInfo[11];
                                $p->data['delivertoline4'] = $orderInfo[12];
                                $p->data['delivertoline5'] = $orderInfo[13];
    $companyId = $orderInfo[31];
                            }
                        }
                        else
                        {
                            $p->data['deliverto'] = $orderInfo[14] . ' ' . $orderInfo[15] . ' ' . $orderInfo[16];
                            $p->data['delivertoline1'] = $orderInfo[17];
                            $p->data['delivertoline2'] = $orderInfo[18];
                            if ($orderInfo[19] == '' and $orderInfo[20] == '' and $orderInfo[21] == '') // no delivery address line 3 or 4 or 5
                            {
                                $p->data['delivertoline3'] = $orderInfo[36] . ', ' . $orderInfo[37] . ' ' . $orderInfo[38];
                                $p->data['delivertoline4'] = $orderInfo[39];
                                $p->data['delivertoline5'] = '';
                            } else {
                                $p->data['delivertoline3'] = $orderInfo[19];
                                $p->data['delivertoline4'] = $orderInfo[20];
                                $p->data['delivertoline5'] = $orderInfo[21];
                            }
                        }
                        if ($orderInfo[23] == '') // no supplier
                        {
                            if ($orderInfo[25] == '' and $orderInfo[26] == '') // no supplier name 
                            {
                                $p->data['suppliedby'] = $companyInfo[0] ;
                            } else {
                                $p->data['suppliedby'] = $orderInfo[25] . ' ' . $orderInfo[26];
                            }
                            $p->data['suppliedbyline1'] = $orderInfo[27];
                            if ($orderInfo[28] == '' and $orderInfo[29] == '') // no supplier address line 2 or 3 
                            {
                                $p->data['suppliedbyline2'] = $orderInfo[40] . ', ' . $orderInfo[41] . ' ' . $orderInfo[42];
                                $p->data['suppliedbyline3'] = $orderInfo[43];
                            } else {
                                $p->data['suppliedbyline2'] = $orderInfo[28];
                                $p->data['suppliedbyline3'] = $orderInfo[29];
                            }
                            $p->data['suppliedbyphone'] = $orderInfo[30];
                        }
                        else
                        {
                            $p->data['suppliedby'] = $supplierInfo[0] ;
                            $p->data['suppliedbyline1'] = $supplierInfo[1];
                            $p->data['suppliedbyline2'] = $supplierInfo[2];
                            $p->data['suppliedbyline3'] = $supplierInfo[3];
                            $p->data['suppliedbyphone'] = $supplierInfo[4];
                        }
         

                        $p->data['qty'] = $result[2];
                        $p->data['labelqty'] = $result[1];

                        $labelNo = getNextLabel($Link, $_POST['real_order_no']);
                        $p->data['despatchlabel'] = $labelNo[1];
                        $p->data['despatchlabelprefix'] = substr($labelNo[1],0,1);
                        $p->data['despatchlabelsuffix'] = substr($labelNo[1],1,strlen($labelNo[1]) - 1);
                        //print_r($p->data);

                        //echo $formatPath. $formatName;
                        $tpl = file_get_contents($formatPath . $formatName);
                        //echo strlen($tpl);
                        // first label set 
                        for ($i = 0; $i < $result[1]; $i++) {
                                    $save = fopen($printerPath .
                                                  $p->data['despatchlabel'] .
                                                  '_' . $p->data['orderno'] .
                                                  '_DESPATCH.prn', 'w');
                                    $p->data['ofqty'] = $i + 1;
                                    $p->send($tpl, $save);
                                    /* update this despatch label to printed */
                                    /* save the product and qty used */
                                    updateLabel($Link, $labelNo[0], $_POST['product_id'] , $result[2]) ;
                                    /* get next labels data */
                                    $labelNo = getNextLabel($Link, $_POST['real_order_no']);
                                    $p->data['despatchlabel'] = $labelNo[1];
                                    $p->data['despatchlabelprefix'] = substr($labelNo[1],0,1);
                                    $p->data['despatchlabelsuffix'] = substr($labelNo[1],1,strlen($labelNo[1]) - 1);
                                    fclose($save);
                        }
                        // second label set 
                        if ($result[5] > 0 or $result[4] > 0 ) {
                            // get starting next label
                            $labelNo = getNextLabel($Link, $_POST['real_order_no']);
                            $p->data['despatchlabel'] = $labelNo[1];
                            $p->data['despatchlabelprefix'] = substr($labelNo[1],0,1);
                            $p->data['despatchlabelsuffix'] = substr($labelNo[1],1,strlen($labelNo[1]) - 1);
                            $p->data['qty'] = $result[5];
                            $p->data['labelqty'] = $result[4];
                            for ($i = 0; $i < $result[4]; $i++) {
                                $save = fopen($printerPath .
                                              $p->data['despatchlabel'] .
                                              '_' . $p->data['orderno'] .
                                              '_DESPATCH.prn', 'w');
                                $p->data['ofqty'] = $i + 1;
                                $p->send($tpl, $save);
                                /* update this despatch label to printed */
                                /* save the product and 2 qtys used */
                                updateLabel($Link, $labelNo[0], $_POST['product_id'] , $result[5]) ;
                                $labelNo = getNextLabel($Link, $_POST['real_order_no']);
                                $p->data['despatchlabel'] = $labelNo[1];
                                $p->data['despatchlabelprefix'] = substr($labelNo[1],0,1);
                                $p->data['despatchlabelsuffix'] = substr($labelNo[1],1,strlen($labelNo[1]) - 1);
                                fclose($save);
                            }
                        }
                        ibase_commit($dbTran);
                        {
                            if ($_POST['complete'] == 'y') {
                                header('Location: despatch_menu.php');
                                echo '<html><head><title>Redirect</title></head><body><p>You are being redirected to <a href="despatch_menu.php">despatch_menu.php"</a></p></body></html>';
                                exit(0);
                            }
                        }
                        showPageVerify($Link, $_POST['real_order_no'], null);
                    }
                }
            } else {
                showPageVerify($Link, $_POST['real_order_no'], $errors);
            }
        }
    }
    break;

case 2:  // SSN scanned
    if (isset($_POST['action_back_x'])) {
	if ($_POST['action_back_x'] > 0) {
            header('Location: despatch_menu.php');
            echo '<html><head><title>Redirect</title></head><body><p>You are being redirected to <a href="despatch_menu.php">despatch_menu.php"</a></p></body></html>';
            exit;
        }
    } 
    // ok have an ssn
    // calculate the order from this
    // then go of to the delivery page
    {
        $errors = validatePageSSN($Link);
        if (empty($errors)) {
            if ($_POST['ssn_id'] != '') {
                $orderNo = getSalesSSNOrder($Link, $_POST['ssn_id']);
                //echo "order is " . $orderNo;
                showPageVerify($Link, $orderNo, array());
            } else {
		// go get the 1st order no for the device
		// then go to the verify page
                showPageDelivery($Link, $errors);
            }
        } else {
            //showPageSSN($Link, $errors);
            showPageDelivery($Link, $errors);
        }
    }
    break;


case 1:  // DELIVERY submitted
    if (isset($_POST['action_back_x'])) {
	if ($_POST['action_back_x'] > 0) {
            header('Location: despatch_menu.php');
            echo '<html><head><title>Redirect</title></head><body><p>You are being redirected to <a href="despatch_menu.php">despatch_menu.php"</a></p></body></html>';
            exit;
        }
    } 
    {
        $errors = validatePageDelivery($Link);
        if (empty($errors)) {
            if ($_POST['order_no'] != '') {
                showPageVerify($Link, $_POST['order_no'], array());
            } else {
		// go get the 1st order no for the device
		// then go to the verify page
            }
        } else {
            showPageDelivery($Link, $errors);
        }
    }
    break;

default:
    showPageSSN($Link);
}

ibase_close($Link);
