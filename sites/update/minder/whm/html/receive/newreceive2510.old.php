<?php


if (!isset($_COOKIE['LoginUser'])) {
    header('Location: /whm/');
    exit(0);
}

include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include 'transaction.php';



/**
 * getTypeName
 *
 * @param $type
 */
function getTypeName($type) {
    switch($type) {
        case 'PO': return 'Purchase Order';
        case 'LD': return 'Load';
        case 'LP': return 'Load Product';
        case 'WO': return 'Work Order';
        case 'RA': return 'Return Order';
        case 'TR': return 'Transfer Order';
    }
    exit('Unknown type');
}


/**
 * getTypeInfo
 */
function getTypeInfo() {
    $type = null;
    if (isset($_GET['type'])) $type = $_GET['type'];
    if (isset($_POST['type'])) $type = $_POST['type'];

    return $type;
}


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
 * getReceiveLocationOpts
 *
 * @param $Link
 */
function getReceiveLocationOpts($Link) {
    $sql = 'SELECT LOCATION.WH_ID || LOCATION.LOCN_ID, LOCATION.LOCN_NAME FROM LOCATION, SESSION WHERE STORE_AREA = \'RC\' AND LOCATION.WH_ID = SESSION.DESCRIPTION AND SESSION.CODE = \'CURRENT_WH_ID\' AND SESSION.DEVICE_ID = \'MQ\' ORDER BY LOCN_NAME';
    if (!($result = ibase_query($Link, $sql))) {
        exit('Unable to read receive locations');
    }
    $printerOpts = array();
    while (($row = ibase_fetch_row($result))) {
        $printerOpts[$row[0]] = $row[0];
    }
    ibase_free_result($result);

    return $printerOpts;
}


/**
 * getUmOpts
 *
 * @param $Link
 */
function getUmOpts($Link) {
    $umOpts = array();
    $sql = 'SELECT CODE, DESCRIPTION FROM UOM, PROD_PROFILE WHERE UOM.CODE = PROD_PROFILE.UOM';
    if (!($result = ibase_query($Link, $sql))) {
        exit('Unable to read receive UMs');
    }
    $printerOpts = array();
    while (($row = ibase_fetch_row($result))) {
        $umOpts[$row[0]] = $row[0];
    }
    ibase_free_result($result);

    return $umOpts;
}


/**
 * getProductInfo
 *
 * @param $Link
 */
function getProductInfo($Link) {
//    $sql = 'SELECT PROD_PROFILE.PROD_ID, PROD_PROFILE.SHORT_DESC, UOM.CODE, UOM.DESCRIPTION, PROD_PROFILE.TEMPERATURE_ZONE FROM PROD_PROFILE, UOM WHERE PROD_PROFILE.UOM = UOM.CODE AND UOM.UOM_TYPE = \'UT\' ORDER BY PROD_ID';
    $sql = 'SELECT PROD_PROFILE.PROD_ID, PROD_PROFILE.SHORT_DESC, UOM.CODE, UOM.DESCRIPTION, PROD_PROFILE.TEMPERATURE_ZONE FROM PROD_PROFILE, UOM WHERE PROD_PROFILE.UOM = UOM.CODE ORDER BY PROD_ID';
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
 * getContainerTypeOpts
 *
 * @param $Link
 */
function getContainerTypeOpts($Link) {
    $sql = 'SELECT CODE, DESCRIPTION FROM OPTIONS WHERE GROUP_CODE = \'SHIP_CONTR\' ORDER BY DESCRIPTION';
    if (!($result = ibase_query($Link, $sql))) {
        exit('Unable to read container types!');
    }
    $containerTypeOpts = array();
    while (($row = ibase_fetch_row($result))) {
        $containerTypeOpts[$row[0]] = $row[1];
    }
    ibase_free_result($result);

    return $containerTypeOpts;
}


/**
 * getGrnOrderNo
 *
 * @param $Link
 * @param string $orderNo
 * @return string or null
 */
function getGrnOrderNo($Link, $grnNo) {
    $result = '';
    $sql = 'SELECT ORDER_NO FROM GRN WHERE GRN.GRN = ?';
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        $r = ibase_execute($q, $grnNo);
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
 * getPurchaseOrderStatus
 *
 * @param $Link
 * @param string $orderNo
 * @return string or null
 */
function getPurchaseOrderStatus($Link, $orderNo) {
    $sql = 'SELECT PO_STATUS FROM PURCHASE_ORDER WHERE PURCHASE_ORDER = ?';
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
 * getNextLineNo
 *
 * @param $Link
 * @param strign $grn;
 * @param string $orderNo
 * @return string or null
 */
function getNextLineNo($Link, $grn, $orderNo) {
    $nextLineNo = 1;
    $sql = 'SELECT LAST_LINE_NO FROM GRN WHERE GRN = ? AND ORDER_NO = ?';
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        $r = ibase_execute($q, $grn, $orderNo);
        if ($r) {
            $d = ibase_fetch_row($r);
            if ($d) {
                $nextLineNo = $d[0] + 1;
            }
            ibase_free_result($r);
        }
        ibase_free_query($q);
    }
    return $nextLineNo;
}


/**
 * getLastLineNo
 *
 * @param $Link
 * @param $orderNo
 * @return int
 */
function getLastLineNo($Link, $grnNo) {
    $sql = 'SELECT LAST_LINE_NO FROM GRN WHERE GRN = ?';
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        $r = ibase_execute($q, $orderNo);
        if ($r) {
            $d = ibase_fetch_row($r);
            if ($d) {
                return $d[0];
            }
        }
    }
    return null;
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
    if ($_POST['grn_no'] != '') {
        if (getGrnOrderNo($Link, $_POST['grn_no']) == '') {
            $errors['grn_no'] = 'Invalid or closed GRN';
        }
    } else {
        if ($_POST['order_no'] != '') {
            if (getPurchaseOrderStatus($Link, $_POST['order_no']) != 'OP') {
                $errors['order_no'] = 'Invalid or closed order no';
            }
        }
        if (!array_key_exists($_POST['printer_id'], getPrinterOpts($Link))) {
            $errors['printer_id'] = 'Invalid printer_id';
        }
        if (!array_key_exists($_POST['sent_by'], getSentByOpts($Link))) {
            $errors['sent_by'] = 'Invalid sent by';
        }
        if (!array_key_exists($_POST['carrier'], getCarrierOpts($Link))) {
            $errors['carrier'] = 'Invalid carrier';
        }
        if ($_POST['veh_reg'] == '') {
            $errors['veh_reg'] = 'Veh Reg must not be empty';
        }
        if (isset($_POST['shipped_date']) && $_POST['shipped_date'] == 'y') {
            if (!checkdate($_POST['shipped_month'], $_POST['shipped_day'], $_POST['shipped_year'])) {
                $errors['shipped_date'] = 'Invalid date';
            }
        }

    }

    return $errors;
}


/**
 * validatePageConnote
 *
 * @param $Link
 */
function validatePageConnote($Link) {
    $errors = array();
    if (!in_array($_POST['container'], array('y', 'n'))) {
       $errors['container'] = 'In a shipping container?';
    }
    if ($_POST['container'] == 'y') {
        if ($_POST['container_no'] == '') {
            $errors['container_no'] = 'You must provide a container number';
        }
        if (!array_key_exists($_POST['container_type'], getContainerTypeOpts($Link))) {
            $errors['container_type'] = 'Invalid container type';
        }
    }
    if ($_POST['docket_no'] == '') {
        $errors['docket_no'] = 'You must provide a docket number or NA';
    }
    if ((int)$_POST['pkgs'] == 0) {
        $errors['pkgs'] = 'Enter the number of packages';
    }
    return $errors;
}


/**
 * validatePageHire
 *
 * @param $Link
 */
function validatePageHire($Link) {
    $errors = array();
    if (!array_key_exists($_POST['hire_pallets'], getPalletOpts($Link))) {
        $errors['hire_pallets'] = 'Invalid hire pallets';
    } else {
        if ($_POST['hire_pallets'] != 'N') {
            if ((int)$_POST['hire_qty'] < 1) {
                $errors['hire_qty'] = 'Enter the number of hired pallets';
            }
        } else {
            if (isset($_POST['hire_qty']) && $_POST['hire_qty'] != '') {
                $errors['hire_qty'] = 'Pallet quantity must be blank';
            }
        }
    }
    if (!array_key_exists($_POST['hire_packaging'], getPackagingOpts($Link))) {
        $errors['hire_packaging'] = 'Invalid hire packaging';
    }
    if (!array_key_exists($_POST['hire_packaging_type'], getPackagingTypeOpts($Link))) {
        $errors['hire_packaging_type'] = 'Invalid hire packaging type';
    }
    return $errors;
}


/**
 * validatePageComment
 *
 * @param $Link
 */
function validatePageComment($Link) {
    $errors = array();
    if (strlen($_POST['comment']) > 80) {
        $errors['comment'] = 'The maxmimum comment size is 80 characters';
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
    if (!array_key_exists($_POST['receive_location'], getReceiveLocationOpts($Link))) {
        $errors['receive_location'] = 'Please select a valid receive location';
    }
    if (!isset($_POST['complete']) || ($_POST['complete'] != 'y' && $_POST['complete'] != 'n')) {
        $errors['complete'] = 'Please indicate if the order is complete';
    }
    return $errors;
}


/**
 * showPageDelivery
 *
 * @param $Link
 */
function showPageDelivery($Link, $errors = array()) {
    $type = getTypeInfo();
    $typeName = getTypeName($type);
    $printerOpts = getPrinterOpts($Link);
    $sentByOpts = getSentByOpts($Link);
    $carrierOpts = getCarrierOpts($Link);
    $ownedByOpts = getOwnedByOpts($Link);
    $ownedById = getDefaultOwnedById($Link);
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
    include 'nr_delivery.php';
}


/**
 * showPageConnote
 *
 * @param $Link
 */
function showPageConnote($Link, $errors = array()) {
    $grn_no = $_POST['grn_no'];
    $printer_id = $_POST['printer_id'];
    $order_no = $_POST['order_no'];
    $sent_by = $_POST['sent_by'];
    $carrier = $_POST['carrier'];
    $veh_reg = $_POST['veh_reg'];
    $shipped_date = isset($_POST['shipped_date']) ? $_POST['shipped_date'] : '';
    $shipped_day = isset($_POST['shipped_day']) ? $_POST['shipped_day'] : '';
    $shipped_month = isset($_POST['shipped_month']) ? $_POST['shipped_month'] : '';
    $shipped_year = isset($_POST['shipped_year']) ? $_POST['shipped_year'] : '';
    $owned_by = $_POST['owned_by'];
/*
    $hire_pallets = isset($_POST['hire_pallets'];
    $hire_qty = isset($_POST['hire_qty'];
    $hire_packaging = isset($_POST['hire_packaging'];
    $hire_packaging_type = isset($_POST['hire_packaging_type'];
    $packaging_crate_qty = isset($_POST['packaging_crate_qty'];
*/
    $type = getTypeInfo();
    $typeName = getTypeName($type);
    $containerTypeOpts = getContainerTypeOpts($Link);
    $message = isset($_GET['message']) ? $_GET['message'] : '';
    include 'nr_connote.php';
}


/**
 * showPageHire
 *
 * @param $Link
 */
function showPageHire($Link, $errors = array()) {
    $type = getTypeInfo();
    $typeName = getTypeName($type);

    $grn_no = $_POST['grn_no'];
    $printer_id = $_POST['printer_id'];
    $order_no = $_POST['order_no'];
    $sent_by = $_POST['sent_by'];
    $carrier = $_POST['carrier'];
    $veh_reg = $_POST['veh_reg'];
    $shipped_date = $_POST['shipped_date'];
    $shipped_day = $_POST['shipped_day'];
    $shipped_month = $_POST['shipped_month'];
    $shipped_year = $_POST['shipped_year'];
    $owned_by = $_POST['owned_by'];
    $container = isset($_POST['container']) ? $_POST['container'] : '';
    $container_no = isset($_POST['container_no']) ? $_POST['container_no'] : '';
    $container_type = isset($_POST['container_type']) ? $_POST['container_type'] : '';
    $docket_no = $_POST['docket_no'];
    $pkgs = $_POST['pkgs'];
    $damaged = isset($_POST['damaged']) ? 'y' : 'n';

    $hirePalletOpts = getPalletOpts($Link);
    $hirePackagingOpts = getPackagingOpts($Link);
    $hirePackagingTypeOpts = getPackagingTypeOpts($Link);
    $message = isset($_GET['message']) ? $_GET['message'] : '';
    include 'nr_hire.php';
}


/**
 * showPageComment
 *
 * @param $Link
 */
function showPageComment($Link, $errors = array()) {
    $type = getTypeInfo();
    $typeName = getTypeName($type);

    $grn_no = $_POST['grn_no'];
    $printer_id = $_POST['printer_id'];
    $order_no = $_POST['order_no'];
    $sent_by = $_POST['sent_by'];
    $carrier = $_POST['carrier'];
    $veh_reg = $_POST['veh_reg'];
    $shipped_date = $_POST['shipped_date'];
    $shipped_day = $_POST['shipped_day'];
    $shipped_month = $_POST['shipped_month'];
    $shipped_year = $_POST['shipped_year'];
    $owned_by = $_POST['owned_by'];
    $container = $_POST['container'];
    $container_no = $_POST['container_no'];
    $container_type = $_POST['container_type'];
    $docket_no = $_POST['docket_no'];
    $pkgs = $_POST['pkgs'];
    $damaged = $_POST['damaged'];
    $hire_pallets = $_POST['hire_pallets'];
    $hire_qty = isset($_POST['hire_qty']) ? $_POST['hire_qty'] : '';
    $hire_packaging = $_POST['hire_packaging'];
    $hire_packaging_type = $_POST['hire_packaging_type'];
    $packaging_crate_qty = $_POST['packaging_crate_qty'];

    $message = isset($_GET['message']) ? $_GET['message'] : '';
    include 'nr_comment.php';
}


/**
 * showPageVerify
 *
 * @param Resource $Link
 * @param Array $errors
 */
function showPageVerify($Link, $grnNo, $orderNo, $errors = array()) {
    $type = getTypeInfo();
    $typeName = getTypeName($type);

    $printerOpts = getPrinterOpts($Link);
    $lineNo = getNextLineNo($Link, $grnNo, $orderNo);
    $productInfo = getProductInfo($Link);
    $t = array_keys($productNames);
    $productIdOpts = array_combine($t, $t);
    $umOpts = getUmOpts($Link);
    $receiveLocationOpts = getReceiveLocationOpts($Link);

    include 'nr_verify.php';
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
        include 'yy.php';
        sendGRNXTransaction($Link, $_POST['grn_no']);
        header('Location: /whm/receive/receive_menu.php');
        echo '<html><head><title>Redirect</title></head><body><p>You are being redirected to <a href="/whm/receive/receive_menu.php">/whm/receive/receive_menu.php"</a></p></body></html>';
    } else {
        if (isset($_POST['action_accept_x'])) {
            $errors = validatePageVerify($Link);
            $printerIp = getPrinterIp($Link, $_POST['printer_id']);
            if ($printerIp == '') {
                $errors['printer_id'] = 'No IP address for printer';
            }
            if (empty($errors)) {
                include 'yy.php';
                $dbTran = ibase_trans(IBASE_DEFAULT, $Link);
                $result = sendGRNVTransaction($Link);
                ibase_commit($dbTran);
                if (count($result) == 8 && $result[7] == 'Processed successfully') {
                    $productInfo = getProductInfo($Link);
                    $umOpts = getUmOpts($Link);
                    include 'Printer.php';

                    $p = new Printer($printerIp);
                    $p->data['issn'] = $result[0];
                    $p->data['qty'] = $result[2];
                    $p->data['palletno'] = '';
                    $loginUser = split('\|', $_COOKIE['LoginUser']);
                    $p->data['userid'] = $loginUser[0];
                    if (isset($productInfo[$_POST['product_id']][0])) {
                        $p->data['description'] = $productInfo[$_POST['product_id']][0];
                    } else {
                        $p->data['description'] = '';
                    }
                    $p->data['product_id'] = $_POST['product_id'];
                    if (isset($productInfo[$_POST['product_id']][2])) {
                        $p->data['um'] = $productInfo[$_POST['product_id']][2];
                    } else {
                        $p->data['um'] = '';
                    }
                    $p->data['now'] = date('d/m/y H:i:s');
                    if (isset($productInfo[$_POST['product_id']][3])) {
                        $p->data['tempzone'] = $productInfo[$_POST['product_id']][3];
                    } else {
                        $p->data['tempzone'] = '';
                    }
                    $tpl = file_get_contents('/var/www/html/whm/receive/ISSN.prn');

                    $save = fopen('/tmp/printer/' . $p->data['issn'] . '_' . $result[1] . '_' . $p->data['qty'] . '.prn', 'w');
                    for ($i = 0; $i < $result[1]; $i++) {
                        $p->send($tpl, $save);
                        $p->data['issn']++;
                    }
                    fclose($save);

                    $p->data['issn'] = $result[3];
                    $p->data['qty'] = $result[5];
                    $save = fopen('/tmp/printer/' . $p->data['issn'] . '_' . $result[4] . '_' . $p->data['qty'] . '.prn', 'w');
                    for ($i = 0; $i < $result[4]; $i++) {
                        $p->send($tpl, $save);
                        $p->data['issn']++;
                    }
                    fclose($save);
                    if ($_POST['complete'] == 'y') {
                        header('Location: /whm/receive/receive_menu.php');
                        echo '<html><head><title>Redirect</title></head><body><p>You are being redirected to <a href="/whm/receive/receive_menu.php">/whm/receive/receive_menu.php"</a></p></body></html>';
                        exit(0);
                    }
                    showPageVerify($Link, $_POST['real_grn_no'], $_POST['real_order_no'], null);
                } else {
                    if (count($result) == 8) {
                        $errors['unknown'] = $result[7];
                    } else {
                        $errors['unknown'] = $result[0];
                    }
                    showPageVerify($Link, $_POST['real_grn_no'], $_POST['real_order_no'], $errors);
                }
            } else {
                showPageVerify($Link, $_POST['real_grn_no'], $_POST['real_order_no'], $errors);
            }
        }
    }
    break;

case 4:  // COMMENT submitted
    if (isset($_POST['action_back_x'])) {
        showPageHire($Link);
    } else {
        $errors = validatePageComment($Link);
        if (empty($errors)) {
            if (isset($_POST['action_accept_x'])) {
                include 'yy.php';
                $dbTran = ibase_trans(IBASE_DEFAULT, $Link);
                list($grn, $orderNo, $error) = sendGRNDTransaction($Link);
                if ($error == 'Processed successfully') {
                    if (isset($_POST['comment']) && $_POST['comment'] != '') {
                        sendGRNCTransaction($Link, $grn);
                    }
                    $error = null;
                } else {
                    ibase_rollback($dbTran);
                }
                if ($error) {
                    showPageConnote($Link, array($error));
                } else {
                    showPageVerify($Link, $grn, $orderNo, array());
                }
            } else {
                // This should really never happen
                showPageComment($Link, $errors);
            }
        } else {
            showPageComment($Link, $errors);
        }
    }
    break;

case 3:  // HIRE submitted
    if (isset($_POST['action_back_x'])) {
        showPageConnote($Link);
    } else {
        $errors = validatePageHire($Link);
        if (empty($errors)) {
            if (isset($_POST['action_accept_x'])) {
                include 'yy.php';
                $dbTran = ibase_trans(IBASE_DEFAULT, $Link);
                list($grn, $orderNo, $error) = sendGRNDTransaction($Link);
                if ($error == 'Processed successfully') {
                    ibase_commit($dbTran);
                    $error = null;
                } else {
                    ibase_rollback($dbTran);
                }
                if ($error) {
                    showPageConnote($Link, array($error));
                } else {
                    showPageVerify($Link, $grn, $orderNo, array());
                }
            } else {
                // This should really never happen
                showPageComment($Link, $errors);
            }
        } else {
            showPageHire($Link, $errors);
        }
    }
    break;

case 2:  // CONNOTE submitted
    if (isset($_POST['action_back_x'])) {
        showPageDelivery($Link);
    } else {
        $errors = validatePageConnote($Link);
        if (empty($errors)) {
            if (isset($_POST['action_accept_x'])) {
                include 'yy.php';
                $dbTran = ibase_trans(IBASE_DEFAULT, $Link);
                list($grn, $orderNo, $error) = sendGRNDTransaction($Link);
                if ($error == 'Processed successfully') {
                    ibase_commit($dbTran);
                    $error = null;
                } else {
                    ibase_rollback($dbTran);
                }
                if ($error) {
                    showPageConnote($Link, array($error));
                } else {
                    showPageVerify($Link, $grn, $orderNo, array());
                }
            } else {
                showPageHire($Link);
            }
        } else {
            showPageConnote($Link, $errors);
        }
    }
    break;

case 1:  // DELIVERY submitted
    if (isset($_POST['action_back_x'])) {
        header('Location: /whm/receive/receive_menu.php');
        echo '<html><head><title>Redirect</title></head><body><p>You are being redirected to <a href="/whm/receive/receive_menu.php">/whm/receive/receive_menu.php"</a></p></body></html>';
    } else {
        $errors = validatePageDelivery($Link);
        if (empty($errors)) {
            if ($_POST['grn_no'] != '') {
                showPageVerify($Link, $_POST['grn_no'], getGrnOrderNo($Link, $_POST['grn_no']), array());
            } else {
                showPageConnote($Link);
            }
        } else {
            showPageDelivery($Link, $errors);
        }
    }
    break;

default:
    showPageDelivery($Link);
}

ibase_close($Link);
