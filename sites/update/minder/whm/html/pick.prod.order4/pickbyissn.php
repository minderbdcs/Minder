<?php
//include_once('../receive/Printer.php');
include_once('Printer.php');
ob_start();
if (!isset($_COOKIE['LoginUser'])) {
    header('Location: /whm/');
    exit(0);
}

list($userId, $deviceId) = explode("|", $_COOKIE["LoginUser"]);

include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include 'transaction.php';

if (!($Link = ibase_pconnect($DBName2, $User, $Password))) {
    exit("Unable to Connect!");
}

$sql = "SELECT COMPANY_ID, DEFAULT_WH_ID, PICK_IMPORT_SSN_STATUS FROM CONTROL";
$result = ibase_query($Link, $sql);
if ($result === false) {
    exit("Unable to read CONTROL table");
}
$row = ibase_fetch_row($result);
if ($row === false) {
    exit("Unable to read CONTROL table");
}
$companyId = $row[0];
$defaultWhId = $row[1];
$pickImportSsnStatus = $row[2];
ibase_free_result($result);

function PrintIssnLabel($Link, $printerId, $printerIp, $ssn_id, $qty, $other1, $other2, $product_id, $company_id, $productInfo) {
    global $userId, $deviceId;

    $p = new Printer($printerIp);
    $p->data['ownerid'] = $company_id;
    $p->data['issn'] = $ssn_id;
    $p->data['qty'] = $qty;
    $p->data['palletno'] = $other1;
    $p->data['parentid'] = $other2;
    //$loginUser = split('\|', $_COOKIE['LoginUser']);
    $loginUser = explode("|", $_COOKIE["LoginUser"]);
    $p->data['userid'] = $loginUser[0];
    if (isset($productInfo[$product_id][0])) {
        $p->data['description'] = $productInfo[$product_id][0];
    } else {
        $p->data['description'] = '';
    }
    $p->data['product_id'] = $product_id;
    if (isset($productInfo[$product_id][2])) {
        $p->data['um'] = $productInfo[$product_id][2];
    } else {
        $p->data['um'] = '';
    }
    $p->data['now'] = date('d/m/y H:i:s');
    if (isset($productInfo[$product_id][3])) {
        $p->data['tempzone'] = $productInfo[$product_id][3];
    } else {
        $p->data['tempzone'] = '';
    }
    $tpl = file_get_contents('../receive/ISSN-new.prn');
    $save = fopen('/data/asset.rf/' . $printerId .  '/' . $p->data['issn'] . '_ISSN.prn', 'w');
    //$p->send($tpl, $save);
    if (!$p->sysLabel($Link, $printerId, "ISSN", 1, "F"))
    {
        $p->send($tpl, $save);
    }
    fclose($save);
}

function getPrinterIp($Link, $printerId) {
    global $userId, $deviceId;

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

function getProductInfo($Link, $product_id) {
    global $userId, $deviceId;

    $sql = 'SELECT PROD_PROFILE.PROD_ID, PROD_PROFILE.SHORT_DESC, UOM.CODE, UOM.DESCRIPTION, PROD_PROFILE.TEMPERATURE_ZONE FROM PROD_PROFILE JOIN UOM ON PROD_PROFILE.UOM = UOM.CODE WHERE PROD_PROFILE.PROD_ID = ?';
    $productInfo = array();
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        $r = ibase_execute($q, $product_id);
        if ($r) {
            while (($row = ibase_fetch_row($r))) {
                $productInfo[$row[0]] = array($row[1], $row[2], $row[3], $row[4]);
            }
            ibase_free_result($r);
        }
        ibase_free_query($q);
    }
    return $productInfo;
}

function getAvailableIssn($orderNo, $labelNo, $qtyRequired) {
    global $Link, $userId, $deviceId, $sysAdmin, $inventoryOperator;

    $orderInfo = getOrderInfo($orderNo);
    if ($orderNo == null) {
        return array();
    }

    $availableIssns = array();
    $productId = null;
    $ssnId = null;
    $sql  = "SELECT PROD_ID, SSN_ID FROM PICK_ITEM WHERE PICK_ORDER = ? AND PICK_LABEL_NO = ?";
    $query = ibase_prepare($Link, $sql);
    if ($query !== false) {
        $result = ibase_execute($query, $orderNo, $labelNo);
        if ($result !== false) {
            while (($row = ibase_fetch_assoc($result)) !== false) {
                $productId = $row['PROD_ID'];
                $ssnId = $row['SSN_ID'];
            }
            ibase_free_result($result);
        }
        ibase_free_query($query);
    }
    if ($ssnId != null) {
        $sql  = "SELECT WH_ID, LOCN_ID, SSN_ID, CURRENT_QTY FROM ISSN WHERE SSN_ID = ?";
        $query = ibase_prepare($Link, $sql);
        if ($query !== false) {
            $result = ibase_execute($query, $ssnId);
            if ($result !== false) {
                while (($row = ibase_fetch_assoc($result)) !== false) {
                    $availableIssns[] = $row;
                }
                ibase_free_result($result);
            }
            ibase_free_query($query);
        }
    }
    if ($productId != null) {
        $sql  = "SELECT ISSN.WH_ID, ISSN.LOCN_ID, ISSN.SSN_ID, ISSN.CURRENT_QTY FROM ISSN JOIN SSN ON ISSN.ORIGINAL_SSN = SSN.SSN_ID JOIN GRN ON SSN.GRN = GRN.GRN WHERE ISSN.PROD_ID = ? AND ISSN.ISSN_STATUS = 'RS' AND ISSN.PICK_ORDER = ? ORDER BY GRN.GRN_DATE ASC";
        $query = ibase_prepare($Link, $sql);
        if ($query !== false) {
            $result = ibase_execute($query, $productId, $qtyRequired);
            if ($result !== false) {
                while (($row = ibase_fetch_assoc($result)) !== false) {
                    $availableIssns[] = $row;
                }
                ibase_free_result($result);
            }
            ibase_free_query($query);
        }
    }
    if (empty($availableIssns)) {
        if ($orderInfo['WH_ID'] == '') {
            if ($sysAdmin == 'T' || $inventoryOperator == 'T') {
                $q1 = "SELECT WH_ID FROM WAREHOUSE";
            } else {
                $q1 = "SELECT WH_ID FROM ACCESS_USER WHERE USER_ID = $userId";
            }
        } else {
           $q1 = $orderInfo['WH_ID'];
        }
        if ($orderInfo['COMPANY_ID'] == '') {
            if ($sysAdmin == 'T' || $inventoryOperator == 'T') {
                $q2 = "SELECT COMPANY_ID FROM COMPANY";
            } else {
                $q2 = "SELECT COMPANY_ID FROM ACCESS_COMPANY WHERE USER_ID = $userId";
            }
        } else {
            $q2 = $orderInfo['COMPANY_ID'];
        }
        $sql  = "SELECT FIRST 10 ISSN.WH_ID, ISSN.LOCN_ID, ISSN.SSN_ID, ISSN.CURRENT_QTY FROM ISSN JOIN SSN ON ISSN.ORIGINAL_SSN = SSN.SSN_ID JOIN GRN ON SSN.GRN = GRN.GRN WHERE ISSN.PROD_ID = ? AND ISSN.WH_ID IN ($q1) AND ISSN.COMPANY_ID IN ($q2) AND POS(',PA,ST,', ISSN_STATUS, 0, 1) > -1 ORDER BY GRN.GRN_DATE ASC";
        $query = ibase_prepare($Link, $sql);
        if ($query !== false) {
            $result = ibase_execute($query, $productId);
            if ($result !== false) {
                while (($row = ibase_fetch_assoc($result)) !== false) {
                    $availableIssns[] = $row;
                }
                ibase_free_result($result);
            }
            ibase_free_query($query);
        }
    }

    return $availableIssns;
}

function getDespatchLocation($orderNo) {
    global $Link;
    global $userId, $deviceId;


    $despatchLocation = null;
    $sql  = "SELECT DESPATCH_LOCATION FROM PICK_ORDER WHERE PICK_ORDER = ?";
    $query = ibase_prepare($Link, $sql);
    if ($query !== false) {
        $result = ibase_execute($query, $orderNo);
        if ($result !== false) {
            while (($row = ibase_fetch_assoc($result)) !== false) {
                $despatchLocation = $row['DESPATCH_LOCATION'];
            }
            ibase_free_result($result);
        }
        ibase_free_query($query);
    }

    return $despatchLocation;
}

function getIssn($issn) {
    global $Link;
    global $userId, $deviceId;


    $data = null;
    $sql  = "SELECT * FROM ISSN WHERE SSN_ID = ?";
    $query = ibase_prepare($Link, $sql);
    if ($query !== false) {
        $result = ibase_execute($query, $issn);
        if ($result !== false) {
            while (($row = ibase_fetch_assoc($result)) !== false) {
                if ($data == null) {
                    $data = $row;
                }
                ibase_free_result($result);
            }
            ibase_free_query($query);
        }
    }

    return $data;
}

function getNextOrder($userId, $deviceId) {
    global $Link, $message;


    $orderNo = null;

    // Have any items been allocated to this device for picking?
    $sql  = "SELECT FIRST 1 DISTINCT PICK_ORDER FROM PICK_ITEM WHERE PICK_LINE_STATUS IN ('AL', 'PG', 'PL') AND (PICKED_QTY < PICK_ORDER_QTY OR PICKED_QTY IS NULL) AND (REASON = '' OR REASON IS NULL) AND DEVICE_ID = ? ORDER BY PICK_ORDER";
    $query = ibase_prepare($Link, $sql);
    if ($query !== false) {
        $result = ibase_execute($query, $deviceId);
        if ($result !== false) {
            while (($row = ibase_fetch_row($result)) !== false) {
                if ($orderNo == null) {
                    $orderNo = $row[0];
                }
            }
            ibase_free_result($result);
        }
        ibase_free_query($query);

    }

    if ($orderNo != null) {
        return $orderNo;
    }

    // Find orders that we can pick
    $sql  = "SELECT DISTINCT PICK_ORDER, PICK_LINE_STATUS FROM PICK_ITEM WHERE PICK_ORDER IN (SELECT PICK_ORDER FROM PICK_ORDER WHERE PICK_STATUS = ? ORDER BY CREATE_DATE ASC, PICK_PRIORITY DESC, PICK_ORDER ASC) AND PICK_ORDER NOT IN (SELECT DISTINCT PICK_ORDER FROM PICK_ITEM WHERE PICK_LINE_STATUS <> 'OP')";
    $query = ibase_prepare($Link, $sql);
    if ($query !== false) {
        $result = ibase_execute($query, 'DA');
        if ($result !== false) {
            while (($row = ibase_fetch_row($result)) !== false) {
                if ($orderNo == null) {
                    $orderNo = $row[0];
                }
            }
            ibase_free_result($result);
        }
        if ($orderNo == null) {
            $result = ibase_execute($query, 'OP');
            if ($result !== false) {
                while (($row = ibase_fetch_row($result)) !== false) {
                    if ($orderNo == null) {
                        $orderNo = $row[0];
                    }
                }
                ibase_free_result($result);
            }
        }
        ibase_free_query($query);
    }

    if ($orderNo == null) {
        return null;
    }

    // Allocate them to use for picking
    $tranType = "PKAL";
    $tranClass = "I";
    $myObject = $orderNo;
    $location = $deviceId . 'T|';
    $mySubLocn = $deviceId;
    $myRef = $userId;
    $tranQty = 0;
    $mySource = 'SSSSSSSSS';

    $result = dotransaction_response($tranType, $tranClass, $myObject, $location, $mySubLocn, $myRef, $tranQty, $mySource, $userId, $deviceId);
    if ($result > "") {
            list($myMessField, $myMessLabel) = explode("=", $result);
            $message = urldecode($myMessLabel);
            if ($message == " " || $message == "" || $message == "Processed successfully") {
                $message = "Next Order";
            }
    } else {
        $message = "Next Order";
    }

    return $orderNo;
}

function getOrderInfo($orderNo) {
    global $Link;
    global $userId, $deviceId;

    $sql  = "SELECT WH_ID, COMPANY_ID FROM PICK_ORDER WHERE PICK_ORDER = ?";

    $info = null;
    $query = ibase_prepare($Link, $sql);
    if ($query !== false) {
        $result = ibase_execute($query, $orderNo);
        if ($result !== false) {
            while (($row = ibase_fetch_row($result)) !== false) {
                if ($info == null) {
                    $info = $row;
                }
            }
            ibase_free_result($result);
        }
        ibase_free_query($query);
    }

    return $info;
}

function getNextLine($orderNo) {
    global $Link;
    global $userId, $deviceId;

    $line = null;

    $sql  = "SELECT PICK_LABEL_NO, SSN.PROD_ID, PICK_ITEM.SSN_ID, PICK_ORDER_QTY, PICKED_QTY FROM PICK_ITEM, ISSN, SSN WHERE PICK_ITEM.SSN_ID = ISSN.SSN_ID AND ISSN.ORIGINAL_SSN = SSN.SSN_ID AND PICK_ITEM.PICK_ORDER = ? AND PICK_LINE_STATUS IN ('AL', 'PG', 'PL') AND (PICKED_QTY < PICK_ORDER_QTY OR PICKED_QTY IS NULL) AND (REASON = '' OR REASON IS NULL) ORDER BY PICK_LABEL_NO";
    $query = ibase_prepare($Link, $sql);
    if ($query !== false) {
        $result = ibase_execute($query, $orderNo);
        if ($result !== false) {
            while (($row = ibase_fetch_row($result)) !== false) {
                if ($productId == null) {
                    $line = $row;
                    $line[] = $qtyRequired - $qtyAllocated;
                }
            }
            ibase_free_result($result);
        }
        ibase_free_query($query);
    }
    if ($line !== null) {
        return $line;
    }

    $sql  = "SELECT PICK_LABEL_NO, PROD_ID, SSN_ID, PICK_ORDER_QTY, PICKED_QTY FROM PICK_ITEM WHERE PICK_ORDER = ? AND PICK_LINE_STATUS IN ('AL', 'PG', 'PL', 'CF') AND (PICKED_QTY < PICK_ORDER_QTY OR PICKED_QTY IS NULL) AND (REASON = '' OR REASON IS NULL) ORDER BY PICK_LABEL_NO";
    $query = ibase_prepare($Link, $sql);
    if ($query !== false) {
        $result = ibase_execute($query, $orderNo);
        if ($result !== false) {
            while (($row = ibase_fetch_row($result)) !== false) {
                if ($productId == null) {
                    $line = $row;
                    $line[] = $qtyRequired - $qtyAllocated;
                }
            }
            ibase_free_result($result);
        }
        ibase_free_query($query);
    }

    return $line;
}


$orderNo = null;
$labelNo = null;
$productId = '';
$qtyRequired = '';
$qtyAllocated = '';
$message = null;
$availableIssns = array();
$sysAdmin = 'F';
$inventoryOperator = 'F';

$sql  = "SELECT SYS_ADMIN, INVENTORY_OPERATOR FROM SYS_USER WHERE USER_ID = ?";

$info = null;
$query = ibase_prepare($Link, $sql);
if ($query !== false) {
    $result = ibase_execute($query, $userId);
    if ($result !== false) {
        while (($row = ibase_fetch_assoc($result)) !== false) {
            $sysAdmin = $row['SYS_ADMIN'];
            $inventoryOperator = $row['INVENTORY_OPERATOR'];
        }
        ibase_free_result($result);
    }
    ibase_free_query($query);
}

function doPKCA()
{
    global $userId, $deviceId;

    $message = null;

    $data = getIssn($_POST['issn']);

    $tranType = "PKCA";
    $tranClass = "P";
    $myObject = '';
    $location = $deviceId;
    $mySubLocn = '';
    $myRef = '';
    $tranQty = 0;
    $mySource = 'SSBSSKSSS';

    $result = dotransaction_response($tranType, $tranClass, $myObject, $location, $mySubLocn, $myRef, $tranQty, $mySource, $userId, $deviceId);
    if ($result > "") {
        list($field, $message) = explode("=", $result);
        $message = urldecode($message);
        if ($message == "Processed successfully") {
            $message = "Items unpicked";
        }
    } else {
        $message = "Items unpicked";
    }

    return $message;
}

function doPKOL()
{
    global $userId, $deviceId;

    $message = null;

    if (!isset($_POST['order_no'])) { $message = 'Missing Pick Order No'; }
    if (!isset($_POST['label_no'])) { $message = 'Missing Pick Item Label No'; }
    if (!isset($_POST['product_id'])) { $message = 'Missing Product Id'; }
    if (!isset($_POST['qty_required'])) { $message = 'Missing Quantity Required'; }
    if (!isset($_POST['issn'])) { $message = 'Missing ISSN'; }
    if (!isset($_POST['reason'])) { $message = 'Missing Reason'; }
    if (isset($_POST['reason']) && $_POST['reason'] != 'NA') {
        if (!isset($_POST['qty_picked'])) { $message = 'Missing Quantity Picked'; }
    }
    if ($message == null) {
        $lines = getAvailableIssn($_POST['order_no'], $_POST['label_no'], $_POST['qty_required']);
        $validIssn = false;
        foreach ($lines as $line) {
            if ($line['SSN_ID'] == $_POST['issn']) {
                $validIssn = true;
            }
        }
        if ($_POST['reason'] == 'NA' && !$validIssn) {
            $message = 'Invalid or unavailable ISSN';
        } else {
            $data = getIssn($_POST['issn']);

            $tranType = "PKOL";
            $tranClass = "B";
            $myObject = $_POST['issn'];
            $location = $data['WH_ID'] . $data['LOCN_ID'];
            $mySubLocn = $_POST['label_no'];
            $myRef = '';
            if ($_POST['reason'] == 'NA') {
                if ((int)$data['CURRENT_QTY'] > (int)$_POST['qty_required']) {
                   $tranQty = $_POST['qty_required'];
                } else {
                   $tranQty = $data['CURRENT_QTY'];
                }
            } else {
                $myRef = $_POST['reason'];
                $tranQty = $_POST['qty_picked'];
            }
            $mySource = 'SSBSSKSSS';

            $result = dotransaction($tranType, $tranClass, $myObject, $location, $mySubLocn, $myRef, $tranQty, $mySource, $userId, $deviceId);
            if ($result > "") {
                $message = 'Error: ' . $result;
            } else {
                $message = "Added successfully";
            }

            $sql = "SELECT p2.ssn_id, s2.prod_id, s2.other1, s2.other2, s2.current_qty, s2.company_id, c3.default_pick_printer FROM pick_item_detail p2 join issn s2 on s2.ssn_id = p2.ssn_id join  control c3 on c3.record_id = 1 where p2.pick_label_no = ? and s2.other2 starting 'Split' and s2.label_date is null";
            $query = ibase_prepare($Link, $sql);
            $sql = "UPDATE ISSN SET LABEL_DATE = 'NOW' WHERE SSN_ID = ?";
            $query2 = ibase_prepare($Link, $sql);
            if ($query !== false && $query2 !== false) {
                $result = ibase_execute($query,  $_POST['label_no']);
                if ($result !== false) {
                    while (($row = ibase_fetch_row($result)) !== false) {
                        $wk_split_ssn = $row[0];
                        $wk_sp_prod = $row[1];
                        $wk_sp_other1 = $row[2];
                        $wk_sp_other2 = $row[3];
                        $wk_sp_qty = $row[4];
                        $wk_sp_comp = $row[5];
                        $wk_sp_printer = $row[6];
                        $printer_Ip = getPrinterIp($Link, $wk_sp_printer);
                        $productInfo = getProductInfo($Link, $wk_sp_prod) ;
                        PrintIssnLabel($Link, $printer_Id, $printer_Ip, $wk_split_ssn, $wk_sp_qty, $wk_sp_other1, $wk_sp_other2, $wk_sp_prod, $wk_sp_comp, $productInfo) ;
                        $result2 = ibase_execute($query, $wk_split_ssn);
                        if ($result2 !== false) {
                            ibase_free_result($result);
                        }
                    }
                    ibase_free_result($result);
                }
                ibase_free_query($query2);
                ibase_free_query($query);
            }
        }
    }

    return $message;
}

function doPKUL()
{
    global $userId, $deviceId;

    $message = null;

//     if (!isset($_POST['product_id'])) { $message = 'Missing Product Id'; }
    if ($message == null) {
        $data = getIssn($_POST['issn']);

        $tranType = "PKUL";
        $tranClass = "P";
        // This may need to be the product ID
        $myObject = '';
        $location = $data['WH_ID'] . $data['LOCN_ID'];
        $mySubLocn = '';
        $myRef = '';
        $tranQty = 200;
        $mySource = 'SSBSSKSSS';

        $result = dotransaction($tranType, $tranClass, $myObject, $location, $mySubLocn, $myRef, $tranQty, $mySource, $userId, $deviceId);
        if ($result > "") {
            $message = 'Error: ' . $result;
        } else {
            $message = "Unpicked successfully";
        }
    }

    return $message;
}

if (!empty($_POST)) {
    if (isset($_POST['accept_x']) || isset($_POST['nostock_x'])) {
/*
        header('Content-type: text/plain');
        print_r($_POST);
        exit('Stopped');
*/
        $message = doPKOL();
    }
    if (isset($_POST['dolater_x'])) {
        $message = doPKUL();
    }
    if (isset($_POST['unpick_x'])) {
        $message = doPKCA();
    }
    if (isset($_POST['cancel_x'])) {
        header('Content-type: text/plain');
        print_r($_POST);
        exit('Cancel');
    }
}

if (isset($_POST['order_no']) && $_POST['order_no'] != '') {
    $orderNo = $_POST['order_no'];
} else {
    $orderNo = getNextOrder($userId, $deviceId);
}

if ($orderNo == null) {
    $message = 'No more orders';
    $orderNo = '';
} else {
    $line = getNextLine($orderNo);
    if ($line == null) {
        $data = getIssn($_POST['issn']);

        $tranType = "PKIL";
        $tranClass = "M";
        $myObject = $orderNo;
        $location = getDespatchLocation($orderNo);
        $mySubLocn = $deviceId;
        $myRef = '';
        $tranQty  = 1;
        $mySource = 'SSBSSKSSS';

/*
header('Content-Type: text/plain');
echo "tranType = $tranType\n";
echo "tranClass = $tranClass\n";
echo "myObject = $myObject\n";
echo "location = $location\n";
echo "mySubLocn = $mySubLocn\n";
echo "myRef = $myRef\n";
echo "tranQty  = $tranQty\n";
echo "mySource = $mySource\n";
echo "userId = $userId\n";
echo "deviceId = $deviceId\n";
exit(0);
*/

        $result = dotransaction($tranType, $tranClass, $myObject, $location, $mySubLocn, $myRef, $tranQty, $mySource, $userId, $deviceId);
        $orderNo = '';
        if ($result > "") {
            $message = 'Error: ' . $result;
        } else {
            $message = 'Deliver to despatch location ' . $location;
        }
        header('HTTP/1.1 303 See other');
        header('Location: pick_Menu.php');
        exit(0);
    } else {
        list($labelNo, $productId, $ssnId, $qtyRequired, $qtyAllocated) = $line;
    }
}


$availableIssns = getAvailableIssn($orderNo, $labelNo, $qtyRequired);

?>
<html>
    <head>
        <link rel="stylesheet" href="style.css" type="text/css" />
        <script type="text/javascript">
function focusInput() {
    document.forms[0].order_no.focus();
}

function showMainTab() {
    var tabReason = document.getElementById('tab_back');
    tabReason.style.display = 'none';
    var tabMain = document.getElementById('tab_main');
    tabMain.style.display = 'block';
}

function showBackTab() {
<?php if ($orderNo == ''): ?>
    window.location = '/whm/pick.prod.order3/pick_Menu.php';
    return false;
<?php else: ?>
    var tabReason = document.getElementById('tab_back');
    tabReason.style.display = 'block';
    var tabMain = document.getElementById('tab_main');
    tabMain.style.display = 'none';
    return false;
<?php endif; ?>
}

function showNoStock() {
    var tabReason = document.getElementById('nostock_reason');
    tabReason.style.display = 'table-row';
    var tabMain = document.getElementById('nostock_qty');
    tabMain.style.display = 'table-row';
    return false;
}
        </script>
    </head>
    <body onload="focusInput()">
        <div id="tab_main">
            <form action="pickbyissn.php" method="post"> 
                <input type="hidden" name="order_no" value="<?php echo htmlentities($orderNo); ?>" />
                <input type="hidden" name="product_id" value="<?php echo htmlentities($productId); ?>" />
                <input type="hidden" name="label_no" value="<?php echo htmlentities($labelNo); ?>" />
                <input type="hidden" name="qty_required" value="<?php echo htmlentities($qtyRequired); ?>" />
<?php if ($message <> null): ?>
                <div id="message"><?php echo htmlentities($message, ENT_QUOTES); ?></div>
<?php endif; ?>
                <table id="pick-item">
                    <tr>
                        <th>Order #:</th>
                        <td><?php echo htmlentities($orderNo, ENT_QUOTES); ?></td>
                    </tr>
                    <tr>
                        <th>Product #:</th>
                        <td><?php echo htmlentities($productId, ENT_QUOTES); ?></td>
                    </tr>
                    <tr>
                        <th>Qty to Pick:</th>
                        <td><?php echo htmlentities($qtyRequired, ENT_QUOTES); ?></td>
                    </tr>
                    <tr>
                        <th>Scan ISSN:</th>
                        <td><input type="text" name="issn" value="" autocomplete="off" /></td>
                    </tr>
                    <tr id="nostock_reason">
                        <th>Reason:</th>
                        <td><select name="reason">
                            <option value="NA"></option>
                            <option value="No Stock">No Stock</option>
                            <option value="Damaged Stock">Damaged Stock</option>
                            <option value="Wrong Stock">Wrong Stock</option>
                            <option value="None">No Reason</option>
                        </select></td>
                    </tr>
                    <tr id="nostock_qty">
                        <th>Actual Qty:</th>
                        <td><input type="text" name="qty_picked" value="" autocomplete="off" /></td>
                    </tr>
                </table>
                <input type="image" name="accept" src="/icons/whm/accept.gif" alt="Submit ISSN" />
                <input type="image" name="back" src="/icons/whm/Back_50x100.gif" alt="Finish picking" onclick="return showBackTab()" /><br />
                <input type="image" name="nostock" src="/icons/whm/button.php?text=No+Stock+Reason&fromimage=Blank_Button_50x100.gif" alt="No Stock Reason" onclick="return showNoStock()" />
                <input type="image" name="dolater" src="/icons/whm/button.php?text=Do+Later&fromimage=Blank_Button_50x100.gif" alt="Do Later" />
                <table class="with-border">
                    <tr>
                        <th>WH</th>
                        <th>Location</th>
                        <th>ISSN</th>
                        <th>Qty</th>
                    </tr>
<?php if (empty($availableIssns)): ?>
                    <tr>
                        <td colspan="4" style="padding: 1em 1ex">No ISSN's found</td>
                    </tr>
<?php else: ?>
<?php foreach ($availableIssns as $row): ?>
                    <tr>
                        <td><?php echo htmlentities($row['WH_ID'], ENT_QUOTES); ?></td>
                        <td><?php echo htmlentities($row['LOCN_ID'], ENT_QUOTES); ?></td>
                        <td><?php echo htmlentities($row['SSN_ID'], ENT_QUOTES); ?></td>
                        <td class="number"><?php echo htmlentities($row['CURRENT_QTY'], ENT_QUOTES); ?></td>
                    </tr>
<?php endforeach; ?>
<?php endif;?>
                </table>
            </form>
        </div>
        <div id="tab_back">
            <form action="pickbyissn.php" method="post"> 
                <input type="hidden" name="order_no" value="<?php echo htmlentities($orderNo); ?>" />
                <input type="hidden" name="product_id" value="<?php echo htmlentities($productId); ?>" />
                <input type="hidden" name="label_no" value="<?php echo htmlentities($labelNo); ?>" />
                <input type="hidden" name="qty_required" value="<?php echo htmlentities($qtyRequired); ?>" />
                <input type="image" name="unpick" src="/icons/whm/button.php?text=Unpick+All&fromimage=Blank_Button_50x100.gif" alt="Unpick">
            </form>
            <form action="CancelOrder.php" method="post"> 
                <input type="hidden" name="order" value="<?php echo htmlentities($orderNo); ?>" />
                <input type="image" name="cancel" src="/icons/whm/button.php?text=Cancel+Order" alt="Cancel+Order">
            </form>
            <form action="pickbyissn.php" method="post"> 
                <input type="hidden" name="order_no" value="<?php echo htmlentities($orderNo); ?>" />
                <input type="hidden" name="product_id" value="<?php echo htmlentities($productId); ?>" />
                <input type="hidden" name="label_no" value="<?php echo htmlentities($labelNo); ?>" />
                <input type="hidden" name="qty_required" value="<?php echo htmlentities($qtyRequired); ?>" />
                <input type="image" name="back" src="/icons/whm/continue_picks.gif" alt="Finish picking" onclick="return showMainTab()" />
            </form>
        </div>
    </body>
</html>
