<?php
if (!isset($_COOKIE['LoginUser'])) {
    header('Location: /whm/');
    exit(0);
}

include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include 'transaction.php';
include "logme.php";
//echo get_include_path();
//exit();
//******************************************************************/
$ssn = '';
$label_no = '';
$order = '';
$prod_no = '';
$description = '';
$uom = '';
$order_qty = 0;
$picked_qty = 0;
$required_qty = 0;
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
        echo("Unable to Connect!<BR>\n");
        exit();
}

$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
if (isset($_COOKIE['BDCSData']))
{
	list($label_no, $order, $ssn, $prod_no, $uom, $description, $required_qty, $location, $scanned_ssn, $picked_qty) = explode("|", $_COOKIE["BDCSData"]);
}
{
    $wkBDCSData = getBDCScookie($Link, $tran_device, "BDCSData");
	list($label_no, $order, $ssn, $prod_no, $uom, $description, $required_qty, $location, $scanned_ssn, $picked_qty) = explode("|", $wkBDCSData);
}
//****************************************************************/


/**
 * getPrinterOpts
 *
 * @param $Link
 */
function getPrinterOpts($Link) {
    list($user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
    $myWh = getBDCScookie($Link, $tran_device, "CURRENT_WH_ID");
    //logme($Link, $tran_user, $tran_device, "current WH:" .$myWh.":");
    //$sql = 'SELECT DEVICE_ID FROM SYS_EQUIP WHERE DEVICE_TYPE = \'PR\' ORDER BY DEVICE_ID';
    $sql = 'SELECT SYS_EQUIP.DEVICE_ID FROM SYS_EQUIP WHERE SYS_EQUIP.DEVICE_TYPE = \'PR\' AND ( SYS_EQUIP.WH_ID = \''  . $myWh . '\' OR SYS_EQUIP.WH_ID IS NULL) ORDER BY DEVICE_ID';
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
global $tran_device, $dbTran, $tran_user;
logme($Link, $tran_user, $tran_device, "start getProductInfo");
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

logme($Link, $tran_user, $tran_device, "end getProductInfo");
    return $productInfo;
}


/**
 * getSsnInfo
 *
 * @param $Link
 */
function getSsnInfo($Link, $ssn) {
global $tran_device, $dbTran, $tran_user;
logme($Link, $tran_user, $tran_device, "start getSsnInfo");
    //echo("getssninfo:" . $ssn . "<br />");
    $sql = "SELECT SSN_DESCRIPTION
            FROM SSN 
            WHERE SSN_ID = '" . $ssn . "'";
    if (!($result = ibase_query($Link, $sql))) {
        exit('Unable to read products!');
    }
    $productInfo = array();
    while (($row = ibase_fetch_row($result))) {
        $productInfo = array($row[0], 'I','EA',$ssn);
    }
    ibase_free_result($result);

logme($Link, $tran_user, $tran_device, "end getSsnInfo");
    //print_r ($productInfo);
    //echo("<br />");
    return $productInfo;
}


/**
 * getSsnPicked
 *
 * @param $Link
 */
function getSsnPicked($Link, $orderNo, $ssn) {
global $tran_device, $dbTran, $tran_user;
logme($Link, $tran_user, $tran_device, "start getSsnPicked");
    //echo("getssninfo:" . $ssn . "<br />");
    $productInfo = array();
    $sql = 'SELECT PICK_ITEM.PICKED_QTY
            FROM PICK_ITEM
            WHERE PICK_ITEM.PICK_ORDER = ?
            AND PICK_ITEM.SSN_ID = ?
            AND PICK_ITEM.PICK_LINE_STATUS IN (\'AL\',\'PG\',\'PL\',\'DS\',\'OP\',\'UP\',\'DC\')
            ';
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        if (!($result = ibase_execute($q, $orderNo, $ssn))) {
           exit('Unable to read orders products!');
        }
        while (($row = ibase_fetch_row($result))) {
            $productInfo = array($row[0]);
        }
        ibase_free_result($result);
        ibase_free_query($q);
    }

logme($Link, $tran_user, $tran_device, "end getSsnPicked");
    //print_r ($productInfo);
    //echo("<br />");
    return $productInfo;
}


/**
 * getProductPicked
 *
 * @param $Link
 */
function getProductPicked($Link, $orderNo, $prod) {
global $tran_device, $dbTran, $tran_user;
logme($Link, $tran_user, $tran_device, "start getProductPicked");
    $productInfo = array();
    $sql = 'SELECT SUM(PICK_ITEM_DETAIL.QTY_PICKED)
            FROM PICK_ITEM
            JOIN PICK_ITEM_DETAIL ON PICK_ITEM_DETAIL.PICK_LABEL_NO = PICK_ITEM.PICK_LABEL_NO
            JOIN ISSN ON PICK_ITEM_DETAIL.SSN_ID = ISSN.SSN_ID
            WHERE PICK_ITEM.PICK_ORDER = ?
            AND ISSN.PROD_ID = ?
            AND PICK_ITEM.PICK_LINE_STATUS IN (\'AL\',\'PG\',\'PL\',\'DS\',\'OP\',\'UP\',\'DC\')
            ';
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        if (!($result = ibase_execute($q, $orderNo, $prod))) {
           exit('Unable to read orders products!');
        }
        while (($row = ibase_fetch_row($result))) {
            $productInfo = array($row[0]);
        }
        ibase_free_result($result);
        ibase_free_query($q);
    }

logme($Link, $tran_user, $tran_device, "end getProductPicked");
    //print_r ($productInfo);
    //echo("<br />");
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
 * getOrderIssn
 *
 * @param $Link
 */
function getOrderIssn($Link, $orderNo) {
    $productInfo = array();
    //echo "getorderssn:" . $orderNo . "<br />";
    $sql = 'SELECT DISTINCT ISSN.SSN_ID, ISSN.ORIGINAL_SSN
            FROM PICK_ITEM
            JOIN ISSN ON ISSN.SSN_ID = PICK_ITEM.SSN_ID
            WHERE PICK_ITEM.PICK_ORDER = ?
            AND PICK_ITEM.SSN_ID IS NOT NULL
            AND PICK_ITEM.PICK_LINE_STATUS IN (\'PG\',\'PL\',\'DS\',\'DC\')
            AND ISSN.PACK_ID IS NULL
            ORDER BY ISSN.ORIGINAL_SSN';
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        if (!($result = ibase_execute($q, $orderNo))) {
           exit('Unable to read orders ssns!');
        }
        while (($row = ibase_fetch_row($result))) {
            $productInfo[$row[0]] = $row[1];
        }
        ibase_free_result($result);
        ibase_free_query($q);
    }

    //print_r($productInfo);

    return $productInfo;
}


/**
 * getOrderSsn
 *
 * @param $Link
 */
function getOrderSsn($Link, $orderNo) {
    $productInfo = array();
    //echo "getorderssn:" . $orderNo . "<br />";
    $sql = 'SELECT DISTINCT ISSN.SSN_ID, ISSN.ORIGINAL_SSN
            FROM PICK_ITEM
            JOIN PICK_ITEM_DETAIL ON PICK_ITEM_DETAIL.PICK_LABEL_NO = PICK_ITEM.PICK_LABEL_NO
            JOIN ISSN ON ISSN.SSN_ID = PICK_ITEM_DETAIL.SSN_ID
            WHERE PICK_ITEM.PICK_ORDER = ?
            AND PICK_ITEM.SSN_ID IS NOT NULL
            AND PICK_ITEM.PICK_LINE_STATUS IN (\'PG\',\'PL\',\'DS\',\'DC\')
            AND ISSN.PACK_ID IS NULL
            ORDER BY ISSN.ORIGINAL_SSN';
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        if (!($result = ibase_execute($q, $orderNo))) {
           exit('Unable to read orders ssns!');
        }
        while (($row = ibase_fetch_row($result))) {
            $productInfo[$row[0]] = $row[1];
        }
        ibase_free_result($result);
        ibase_free_query($q);
    }

    //print_r($productInfo);

    return $productInfo;
}

/**
 * getOrderProduct
 *
 * @param $Link
 *        $ssn
 */
function getIssnProduct($Link, $ssn) {
    $productId = null;
    $sql = 'SELECT ISSN.PROD_ID
            FROM ISSN 
            WHERE ISSN.SSN_ID = ?  ';
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        if (!($result = ibase_execute($q, $ssn))) {
           exit('Unable to read issns products!');
        }
        while (($row = ibase_fetch_row($result))) {
            $productId = $row[0];
        }
        ibase_free_result($result);
        ibase_free_query($q);
    }


    return $productId;
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
    $sql = "SELECT CONTACT_NAME,
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
            S_COUNTRY,
            P_PHONE,
            D_PHONE,
            MER_DAY(PICK_DUE_DATE) || '/' || MER_MONTH(PICK_DUE_DATE) || '/' || SUBSTR(CAST(MER_YEAR(PICK_DUE_DATE) AS CHAR(4)) , 3,4) AS DUE_DATE,
            S_PERSON_ID
            FROM PICK_ORDER 
            WHERE PICK_ORDER = ?";
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        $r = ibase_execute($q, $orderNo);
        if ($r) {
            //while(( $d = ibase_fetch_row($r))) { 
            while(( $d = ibase_fetch_assoc($r))) { 
/*
                $result = array($d[0], $d[1], $d[2], $d[3], $d[4],
                                $d[5], $d[6], $d[7], $d[8], $d[9],
                                $d[10], $d[11], $d[12], $d[13], $d[14],
                                $d[15], $d[16], $d[17], $d[18], $d[19],
                                $d[20], $d[21], $d[22], $d[23], $d[24],
                                $d[25], $d[26], $d[27], $d[28], $d[29],
                                $d[30], $d[31], $d[32], $d[33], $d[34],
                                $d[35], $d[36], $d[37], $d[38], $d[39],
                                $d[40], $d[41], $d[42], $d[43], $d[44],
                                $d[45], $d[46] );
*/
                //$result[] = $d;
                $result = $d;
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
global $tran_device, $dbTran, $tran_user;
logme($Link, $tran_user, $tran_device, "start getCompanyInfo");
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
logme($Link, $tran_user, $tran_device, "end getCompanyInfo");
    return $result;
}


/**
 * getCompanyInfo
 *
 * @param $Link
 * @param string $personId
 * @return array or null
 */
function getPersonInfo($Link, $personId) {
global $tran_device, $dbTran, $tran_user;
    $result = array();
    if ($personId != null)
    {
        $sql = 'SELECT FIRST_NAME,
                LAST_NAME,
                TITLE,
                PHONE_NO
                FROM PERSON 
                WHERE PERSON_ID = ?';
        $q = ibase_prepare($Link, $sql);
        if ($q) {
            $r = ibase_execute($q, $personId);
            if ($r) {
                while(( $d = ibase_fetch_assoc($r))) { 
                    //$result = array($d[0], $d[1], $d[2], $d[3] );
                    $result = $d;
                }
                ibase_free_result($r);
            }
            ibase_free_query($q);
        }
    }
    return $result;
}


/**
 * getNextLabel
 *
 * @param $Link
 * @param string $consignmentNo
 * @param string $salesOrder
 * @param string $productId
 * @param string $ssnId
 * @param string $qtyWanted
 * @return array or null
 */
function getNextLabel($Link, $consignmentNo, $salesOrder, $productId, $ssnId = null, $qtyWanted = 9999990) {
    $result = array();
    $ssnIdLabel = '';

//echo("start of getnextlabel ");
//echo("connote:" . $consignmentNo);
//echo(",order:" . $salesOrder);
//echo(",product:" . $productId);
//echo(",ssn:" . $ssnId);
//echo(":<br />");
    $sql = ' SELECT FIRST 1 PACK_ID.PACK_ID, PACK_ID.DESPATCH_LABEL_NO, PACK_ID.DESPATCH_ID
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
                $result = array($d[0], $d[1], $d[2] );
            }
            ibase_free_result($r);
        }
        ibase_free_query($q);
    }
//print_r($result);
    if (count($result) > 0)
    {
        // now find the next ssn for the label
        // for order read pick_item_detail to get issn
        // where issn for the product
        // and its pack id and despatch id are null
        if ($productId == '')
        {
            $sql = ' SELECT FIRST 1 ISSN.SSN_ID
               FROM PICK_ITEM
               JOIN PICK_ITEM_DETAIL ON PICK_ITEM.PICK_LABEL_NO = PICK_ITEM_DETAIL.PICK_LABEL_NO
               JOIN ISSN ON PICK_ITEM_DETAIL.SSN_ID = ISSN.SSN_ID
               WHERE PICK_ITEM.PICK_ORDER = ?
                 AND PICK_ITEM.SSN_ID = ? 
                 AND PICK_ITEM_DETAIL.PICK_DETAIL_STATUS <> \'XX\' 
                 AND PICK_ITEM_DETAIL.PICK_DETAIL_STATUS <> \'xx\' 
                 AND ISSN.DESPATCH_ID IS NULL   
                 AND ISSN.PACK_ID IS NULL   
               ORDER BY ISSN.CREATE_DATE ';
            $q = ibase_prepare($Link, $sql);
            if ($q) {
                $r = ibase_execute($q, $salesOrder, $ssnId);
                if ($r) {
                    while(( $d = ibase_fetch_row($r))) { 
                        $ssnIdLabel = $d[0];
                    }
                    ibase_free_result($r);
                }
                ibase_free_query($q);
            }
        }
        else
        {
            // first try for an issn with >= qty for this label
            $sql = ' SELECT FIRST 1 ISSN.SSN_ID
               FROM PICK_ITEM
               JOIN PICK_ITEM_DETAIL ON PICK_ITEM.PICK_LABEL_NO = PICK_ITEM_DETAIL.PICK_LABEL_NO
               JOIN ISSN ON PICK_ITEM_DETAIL.SSN_ID = ISSN.SSN_ID
               WHERE PICK_ITEM.PICK_ORDER = ?
                 AND ISSN.PROD_ID = ? 
                 AND ISSN.CURRENT_QTY >= ?
                 AND PICK_ITEM_DETAIL.PICK_DETAIL_STATUS <> \'XX\' 
                 AND PICK_ITEM_DETAIL.PICK_DETAIL_STATUS <> \'xx\' 
                 AND ISSN.DESPATCH_ID IS NULL   
                 AND ISSN.PACK_ID IS NULL   
               ORDER BY ISSN.CURRENT_QTY ';
               //ORDER BY ISSN.CREATE_DATE ';
            $q = ibase_prepare($Link, $sql);
            if ($q) {
                $r = ibase_execute($q, $salesOrder, $productId, $qtyWanted);
                if ($r) {
                    while(( $d = ibase_fetch_row($r))) { 
                        $ssnIdLabel = $d[0];
 //   echo("have ssn:" . $ssnIdLabel);
                    }
                    ibase_free_result($r);
                }
                ibase_free_query($q);
            } // end of looking for issn with qty>= label qty
            // otherwise get highest qty issn
            if ($ssnIdLabel == '')
            {
                $sql = ' SELECT FIRST 1 ISSN.SSN_ID
                   FROM PICK_ITEM
                   JOIN PICK_ITEM_DETAIL ON PICK_ITEM.PICK_LABEL_NO = PICK_ITEM_DETAIL.PICK_LABEL_NO
                   JOIN ISSN ON PICK_ITEM_DETAIL.SSN_ID = ISSN.SSN_ID
                   WHERE PICK_ITEM.PICK_ORDER = ?
                     AND ISSN.PROD_ID = ? 
                     AND PICK_ITEM_DETAIL.PICK_DETAIL_STATUS <> \'XX\' 
                     AND PICK_ITEM_DETAIL.PICK_DETAIL_STATUS <> \'xx\' 
                     AND ISSN.DESPATCH_ID IS NULL   
                     AND ISSN.PACK_ID IS NULL   
                   ORDER BY ISSN.CURRENT_QTY DESC ';
                   //ORDER BY ISSN.CREATE_DATE ';
                $q = ibase_prepare($Link, $sql);
                if ($q) {
                    $r = ibase_execute($q, $salesOrder, $productId);
                    if ($r) {
                        while(( $d = ibase_fetch_row($r))) { 
                            $ssnIdLabel = $d[0];
 //   echo("have ssn:" . $ssnIdLabel);
                        }
                        ibase_free_result($r);
                    }
                    ibase_free_query($q);
                }
            } // end of looking for issn by qty
        }
        if ($ssnIdLabel == '')
        {
//    echo("have no ssn" );
            // if none then split from the last for this prod/ssn
            if ($productId == '')
            {
                // an ssn
                //$ssnIdLabel = $ssnId;
                $ssnIdOldLabel = $ssnId;
                $pickLabelId = '';
                $fromWhId = '';
                $fromLocnId = '';
                $despatchLocnId = '';
/*
                $sql = ' SELECT FIRST 1 PICK_ITEM.PICK_LABEL_NO, PICK_ITEM_DETAIL.FROM_WH_ID, PICK_ITEM_DETAIL.FROM_LOCN_ID, PICK_ITEM_DETAIL.DESPATCH_LOCATION
               FROM PICK_ITEM
               JOIN PICK_ITEM_DETAIL ON PICK_ITEM.PICK_LABEL_NO = PICK_ITEM_DETAIL.PICK_LABEL_NO
               JOIN ISSN ON PICK_ITEM_DETAIL.SSN_ID = ISSN.SSN_ID
               WHERE PICK_ITEM.PICK_ORDER = ?
                 AND PICK_ITEM.SSN_ID = ?
                 AND PICK_ITEM_DETAIL.PICK_DETAIL_STATUS <> \'XX\' 
                 AND PICK_ITEM_DETAIL.PICK_DETAIL_STATUS <> \'xx\' 
                ';
*/
                $sql = ' SELECT FIRST 1 PICK_ITEM.PICK_LABEL_NO, PICK_ITEM_DETAIL.FROM_WH_ID, PICK_ITEM_DETAIL.FROM_LOCN_ID, PICK_ITEM_DETAIL.DESPATCH_LOCATION
               FROM PICK_ITEM
               JOIN PICK_ITEM_DETAIL ON PICK_ITEM.PICK_LABEL_NO = PICK_ITEM_DETAIL.PICK_LABEL_NO
               JOIN ISSN ON PICK_ITEM_DETAIL.SSN_ID = ISSN.SSN_ID
               WHERE PICK_ITEM.PICK_ORDER = ?
                 AND PICK_ITEM_DETAIL.SSN_ID = ?
                 AND PICK_ITEM_DETAIL.PICK_DETAIL_STATUS <> \'XX\' 
                 AND PICK_ITEM_DETAIL.PICK_DETAIL_STATUS <> \'xx\' 
                ';
                $q = ibase_prepare($Link, $sql);
                if ($q) {
                    $r = ibase_execute($q, $salesOrder, $ssnId);
                    if ($r) {
                        while(( $d = ibase_fetch_row($r))) { 
                            $pickLabelId = $d[0];
                            $fromWhId = $d[1];
                            $fromLocnId = $d[2];
                            $despatchLocnId = $d[3];
                        }
                        ibase_free_result($r);
                    }
                    ibase_free_query($q);
                }
                // now split this issn
                // do dsss
                $myResult = sendDSSSTransaction($Link, $salesOrder, $ssnIdOldLabel);
                //print_r ($myResult);
                // new ssn in the error_text
                $ssnIdLabel = $myResult[0];
                // add the pick_item_detail for this new issn
                list($user, $device) = explode("|", $_COOKIE["LoginUser"]);
 //   print_r($result);
 //   echo("picklabel:" . $pickLabelId);
 //   echo(",ssn:" . $ssnIdLabel);
 //   echo(",user:" . $user);
 //   echo(",device:" . $device);
 //   echo(",wh:" . $fromWhId);
 //   echo(",locn:" . $fromLocnId);
 //   echo(",despatch:" . $result[2].":<br />");
                $sql = ' INSERT INTO PICK_ITEM_DETAIL 
                   (PICK_LABEL_NO, 
                    SSN_ID, 
                    PICK_DETAIL_STATUS, 
                    QTY_PICKED,
                    USER_ID, 
                    DEVICE_ID, 
                    CREATE_DATE,
                    FROM_WH_ID,
                    FROM_LOCN_ID,
                    DESPATCH_ID,
                    DESPATCH_LOCATION)
               VALUES (?, ?, ?, ?, ?, ?, \'NOW\', ?, ?, ?, ?) ';
                $q = ibase_prepare($Link, $sql);
                if ($q) {
                    $r = ibase_execute($q, $pickLabelId, $ssnIdLabel, 'DC', '0', $user, $device, $fromWhId, $fromLocnId, $result[2], $despatchLocnId);
                    //if ($r) {
                    //    ibase_free_result($r);
                    //}
                    ibase_free_query($q);
                }
            }
            else
            {
                // a product
                // get oldest issn picked
                $ssnIdOldLabel = '';
                $pickLabelId = '';
                $fromWhId = '';
                $fromLocnId = '';
                $despatchLocnId = '';
                $sql = ' SELECT FIRST 1 ISSN.SSN_ID, PICK_ITEM.PICK_LABEL_NO, PICK_ITEM_DETAIL.FROM_WH_ID, PICK_ITEM_DETAIL.FROM_LOCN_ID, PICK_ITEM_DETAIL.DESPATCH_LOCATION
               FROM PICK_ITEM
               JOIN PICK_ITEM_DETAIL ON PICK_ITEM.PICK_LABEL_NO = PICK_ITEM_DETAIL.PICK_LABEL_NO
               JOIN ISSN ON PICK_ITEM_DETAIL.SSN_ID = ISSN.SSN_ID
               WHERE PICK_ITEM.PICK_ORDER = ?
                 AND ISSN.PROD_ID = ? 
                 AND PICK_ITEM_DETAIL.PICK_DETAIL_STATUS <> \'XX\' 
                 AND PICK_ITEM_DETAIL.PICK_DETAIL_STATUS <> \'xx\' 
               ORDER BY ISSN.CREATE_DATE ';
                $q = ibase_prepare($Link, $sql);
                if ($q) {
                    $r = ibase_execute($q, $salesOrder, $productId);
                    if ($r) {
                        while(( $d = ibase_fetch_row($r))) { 
                            $ssnIdOldLabel = $d[0];
                            $pickLabelId = $d[1];
                            $fromWhId = $d[2];
                            $fromLocnId = $d[3];
                            $despatchLocnId = $d[4];
                        }
                        ibase_free_result($r);
                    }
                    ibase_free_query($q);
                }
                // now split this issn
                // do dsss
                $myResult = sendDSSSTransaction($Link, $salesOrder, $ssnIdOldLabel);
                //print_r ($myResult);
                // new ssn in the error_text
                $ssnIdLabel = $myResult[0];
                // add the pick_item_detail for this new issn
                list($user, $device) = explode("|", $_COOKIE["LoginUser"]);
 //   print_r($result);
 //   echo("picklabel:" . $pickLabelId);
 //   echo(",ssn:" . $ssnIdLabel);
 //   echo(",user:" . $user);
 //   echo(",device:" . $device);
 //   echo(",wh:" . $fromWhId);
 //   echo(",locn:" . $fromLocnId);
 //   echo(",despatch:" . $result[2].":<br />");
                $sql = ' INSERT INTO PICK_ITEM_DETAIL 
                   (PICK_LABEL_NO, 
                    SSN_ID, 
                    PICK_DETAIL_STATUS, 
                    QTY_PICKED,
                    USER_ID, 
                    DEVICE_ID, 
                    CREATE_DATE,
                    FROM_WH_ID,
                    FROM_LOCN_ID,
                    DESPATCH_ID,
                    DESPATCH_LOCATION)
               VALUES (?, ?, ?, ?, ?, ?, \'NOW\', ?, ?, ?, ?) ';
                $q = ibase_prepare($Link, $sql);
                if ($q) {
                    $r = ibase_execute($q, $pickLabelId, $ssnIdLabel, 'DC', '0', $user, $device, $fromWhId, $fromLocnId, $result[2], $despatchLocnId);
                    //if ($r) {
                    //    ibase_free_result($r);
                    //}
                    ibase_free_query($q);
                }
            }
        }
    } // end of if have a despatch
    else
    {
        // have no despatch so end of loop use empty strings
        $result = array('', '', '' );
    }
    $result[3] = $ssnIdLabel;
//print_r($result);
//echo("end of getnextlabel <br />");
    return $result;
}


/**
 * updateLabel
 *
 * @param $Link
 * @param array $labelInfo
 *        integer $packId
 *        string $LabelNo
 *        integer $despatchId
 *        string $ssnId
 * @param string $productId
 * @param string $qty
 * @return string or null
 */
function updateLabel($Link, $labelInfo, $productId, $qty , $prodDescType ) {

//echo("start of updatelabel ");
//echo(",product:" . $productId);
//echo(",qty:" . $qty);
//echo(":<br />");
//print_r($labelInfo);
    if ($prodDescType == 'I')
    {
        // an ssn only
        $sql = ' UPDATE PACK_ID SET LABEL_PRINTED_DATE = \'NOW\',
                 QTY = ? ,
                 SSN_ID = ? 
                 WHERE PACK_ID.PACK_ID  = ? ';
        $q = ibase_prepare($Link, $sql);
        if ($q) {
            $r = ibase_execute($q, $qty, $labelInfo[3], $labelInfo[0]);
            //ibase_free_result($r);
            ibase_free_query($q);
        }
    }
    else
    {
        // an product
        $sql = ' UPDATE PACK_ID SET LABEL_PRINTED_DATE = \'NOW\',
                 PROD_ID = ? ,
                 QTY = ? ,
                 SSN_ID = ? 
                 WHERE PACK_ID.PACK_ID  = ? ';
        $q = ibase_prepare($Link, $sql);
        if ($q) {
            $r = ibase_execute($q, $productId, $qty, $labelInfo[3], $labelInfo[0]);
            //ibase_free_result($r);
            ibase_free_query($q);
        }
    }
    $sql = ' UPDATE ISSN SET PACK_ID = ? ,
             DESPATCH_ID = ?  
             WHERE ISSN.SSN_ID  = ? ';
    /*
    // save the issn current qty in picked_qty and make the current qty the packs qty field
    $sql = ' UPDATE ISSN SET PACK_ID = ? ,
             DESPATCH_ID = ? , 
             PICKED_QTY = CURRENT_QTY ,
             CURRENT_QTY = ?
             WHERE ISSN.SSN_ID  = ? ';
    */
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        $r = ibase_execute($q, $labelInfo[0], $labelInfo[2], $labelInfo[3]);
        //$r = ibase_execute($q, $labelInfo[0], $labelInfo[2], $qty, $labelInfo[3]);
        //ibase_free_result($r);
        ibase_free_query($q);
    }
//echo("end of updatelabel <br />");
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
function showPageVerify($Link, $orderNo, $productId = null, $errors = array()) {

    $printerOpts = getPrinterOpts($Link);
    $productInfo = getProductInfo($Link);
    if ($productId == null)
    {
        $orderProduct = getOrderProduct($Link, $orderNo);
    }
    //-- $productNames not defined before
    //-- use $productInfo for testing purposes

    //-- $t = array_keys($productNames);
    //$t = array_keys($productInfo);
    $productIdOpts = array();
    $pickedQty = array();
    $returnQty = 0;
    $returnQty = getMoveISSNQtys($Link );
    if ($returnQty == '')
    {
        $returnQty = 0;
    }

/*
    if ($productId == null)
    {
        foreach ($orderProduct as $Key_product => $Value_product) 
        {
            $productIdOpts[$Key_product] = array($productInfo[ $Key_product ]);
        }
    } else {
*/ 
   if ($productId == '')
   {
        $orderSsn = getOrderIssn($Link, $orderNo);
	if ($orderSsn == array())
	{
        	$orderSsn = getOrderSsn($Link, $orderNo);
	}
	//print_r($orderSsn);
//echo " product is empty";
        foreach ($orderSsn as $Key_product => $Value_product) 
        {
            $productIdOpts[$Key_product] = array(getSsnInfo( $Link, $Value_product ));
            $pickedQty[$Key_product] = array( getSsnPicked( $Link, $orderNo, $Key_product));
            if ($pickedQty[$Key_product] == array(0 => array()))

            {
            	$pickedQty[$Key_product] = array( getSsnPicked( $Link, $orderNo, $Value_product));
            }
        }
   }
   else
   {
//echo (" product is " . $productId . "<br />");
        $Key_product = $productId;
        $productIdOpts[$Key_product] = array($productInfo[ $Key_product ]);
        $pickedQty[$Key_product] = array( getProductPicked( $Link, $orderNo, $Key_product));
    }
//print_r($pickedQty);
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


/**
 * exitBDCSScreen
 *
 * @param $Link
 * @param $dbTran
 * @param $tran_device
 * @param $tran_user
 */
function exitBDCSScreen($Link, $dbTran, $tran_device, $tran_user) {
//global $tran_device, $dbTran, $Link, $tran_user;
	// use the passed value for the next screen
	logme($Link, $tran_user, $tran_device, "start exitBDCSScreen" );
        $nextScreen = getBDCScookie($Link, $tran_device, "PickNextScreen");
        ibase_commit($dbTran);
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
        header('Location: ' . $nextScreen );
        echo '<html><head><title>Redirect</title></head><body><p>You are being redirected to <a href="' . $nextScreen . '">nextscreen.php"</a></p></body></html>';
	logme($Link, $tran_user, $tran_device, "end exitBDCSScreen" );
}


/**
 * updateLabel
 *
 * @param $Link
 * @param array $labelInfo
 * @param string $productId
 * @param string $qty
 * @return string or null
 */
function printLaterLabel($Link  ) {

//echo("start of printLaterLabel ");
//echo(":<br />");
    if (isset($_COOKIE['LoginUser']))
    {
		list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
    }
    $sql = ' UPDATE PRINT_REQUESTS SET REQUEST_STATUS = \'NP\',
             PRN_DATE = \'NOW\' 
             WHERE PERSON_ID  = ? 
             AND REQUEST_STATUS = \'LP\' 
             AND FROM_DEVICE_ID = ? ';
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        $r = ibase_execute($q, $tran_user, $tran_device);
        //ibase_free_result($r);
        ibase_free_query($q);
    }
//echo("end of printLaterLabel <br />");
    //return $result;
}


/**
 * moveISSNLabels
 *
 * @param 
 */
function moveISSNLabels($Link ) {
	if (isset($_COOKIE['LoginUser']))
	{
		list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	}
	//include 'Printer.php';
	require_once 'Printer.php';
        $printerIp = getPrinterIp($Link, $_POST['printer_id']);
        if ($printerIp == '') {
            $errors['printer_id'] = 'No IP address for printer';
        }
        $printerPath = getPrinterDirectory($Link, $_POST['printer_id']);
        $formatPrinter =  getDefaultDespatchPrinter($Link) ;
        $formatPath = getPrinterDirectory($Link, $formatPrinter);
        $p = new Printer($printerIp);
	$wk_file_list = '';
    	$wk_file_list = getBDCScookie($Link, $tran_device, "PickSplitSSN" );
	logme($Link, $tran_user, $tran_device, "ssn printfiles " . $wk_file_list);
	$wk_files = explode("|", $wk_file_list);
	// print files from the pick - these are for split issn's
        foreach ($wk_files as $wk_file) {
		if ($wk_file <> '')
		{
			logme($Link, $tran_user, $tran_device, "ssn print file " . $wk_file);
                	//$save = fopen($wk_file , 'r');
                        $tpl = file_get_contents($wk_file);
			// send it to the printer
                        $p->send($tpl );
			// move the file to the printers folder
                        $wk_file_name = basename($wk_file) ;
                        //$wk_file_dir = dirname($wk_file) ;
			rename($wk_file, $printerPath . $wk_file_name);
                        //fclose($save);
		}
        }
	// must update the print requests to print the saved printes for this user
    	printLaterLabel($Link );
	// clear the cookie
    	setBDCScookie($Link, $tran_device, "PickSplitSSN" ,"" );
    	setBDCScookie($Link, $tran_device, "PickSplitQty" ,"" );
}


/**
 * getMoveISSNQtys
 *
 * @param 
 */
function getMoveISSNQtys($Link ) {
	if (isset($_COOKIE['LoginUser']))
	{
		list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	}
	$wk_file_list = '';
        $wk_net_qty = 0;
    	$wk_file_list = getBDCScookie($Link, $tran_device, "PickSplitQty" );
	logme($Link, $tran_user, $tran_device, "ssn qty " . $wk_file_list);
	$wk_files = explode("|", $wk_file_list);
        foreach ($wk_files as $wk_file) {
		if ($wk_file <> '')
		{
			logme($Link, $tran_user, $tran_device, "ssn split qty " . $wk_file);
			$wk_net_qty = $wk_net_qty + $wk_file;
		}
        }
	return ($wk_net_qty);
}

logme($Link, $tran_user, $tran_device, "addrprodlabel start " );
$page = 0;
if (isset($_POST['page'])) {
    $page = $_POST['page'];
}
switch ($page) {
case 5:  // VERIFY submitted
    if (isset($_POST['action_back_x'])) {
	// send the old ssn labels to the printer
	moveISSNLabels($Link);
        //exitBDCSScreen();
        exitBDCSScreen($Link, $dbTran, $tran_device, $tran_user);
    } else {
        if (isset($_POST['action_continue_x'])) {
            $errors = validatePageVerify($Link);
logme($Link, $tran_user, $tran_device, "end of call to validatePageVerify");
            $printerIp = getPrinterIp($Link, $_POST['printer_id']);
logme($Link, $tran_user, $tran_device, "end of call to getprinterIP");
            if ($printerIp == '') {
                $errors['printer_id'] = 'No IP address for printer';
            }
            $printerPath = getPrinterDirectory($Link, $_POST['printer_id']);
logme($Link, $tran_user, $tran_device, "end of call to getprinterdirectory");
            $formatPrinter =  getDefaultDespatchPrinter($Link) ;
logme($Link, $tran_user, $tran_device, "end of call to getdefaultdespatchprinter");
            $formatPath = getPrinterDirectory($Link, $formatPrinter);
logme($Link, $tran_user, $tran_device, "end of call to getprinterdirectory format");
            $formatName = "despatch.fmt";
            if (empty($errors)) {
logme($Link, $tran_user, $tran_device, "start empty errors");
                $orderInfo = getSalesOrderInfo($Link, $_POST['real_order_no']);
logme($Link, $tran_user, $tran_device, "end of call to getsaleorderinfo");
                include 'addrprodlabeltrans.php';
                //$despatchId = getOrderNoDespatch($Link, $orderNo);
                //$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
                //if ($despatchId == '') {
                    // must get a despatch for this label set
                    // and create labels for this despatch
                    // if qty1 and qty2 <> 0 qty to print += qty1
                    // if qty3 and qty4 <> 0 qty to print += qty3
                    $result = sendDSOTTransaction($Link, $_POST['real_order_no'], $orderInfo['COMPANY_ID']);
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

		// send the old ssn labels to the printer
		moveISSNLabels($Link);
logme($Link, $tran_user, $tran_device, "end of call to moveissnlabels");

                {

                    {
                        //-- otherwise print labels
                        $productInfo = getProductInfo($Link);
logme($Link, $tran_user, $tran_device, "end of call to getproductinfo");
                        //$supplierInfo = getCompanyInfo($Link, $orderInfo[23]);
                        $supplierInfo = getCompanyInfo($Link, $orderInfo['SUPPLIER_ID']);
logme($Link, $tran_user, $tran_device, "end of call to getCompanyInfo 1");
                        //$companyInfo = getCompanyInfo($Link, $orderInfo[31]);
                        $companyInfo = getCompanyInfo($Link, $orderInfo['COMPANY_ID']);
logme($Link, $tran_user, $tran_device, "end of call to getCompanyInfo 2");
                        if (count($supplierInfo) == 0)
                        {
                            //$orderInfo[23] = ''; // supplier is not a company
                            $orderInfo['SUPPLIER_ID'] = ''; // supplier is not a company
                        }
                        $delivertoPersonInfo = getPersonInfo($Link, $orderInfo['PERSON_ID']);
                        $supplierPersonInfo = getPersonInfo($Link, $orderInfo['S_PERSON_ID']);
                        //include 'Printer.php';
			require_once 'Printer.php';
                        $p = new Printer($printerIp);

/*
' despatch barcode %despatchlabel%
' despatch barcode 1st char %despatchlabelprefix%
' despatch barcode after the 1st char %despatchlabelsuffix%
'
' deliver to name %deliverto%
' supplied by company name %suppliedby%
*/
//phpinfo();
//exit();

                        //$loginUser = split('\|', $_COOKIE['LoginUser']);
                        $loginUser = explode("|", $_COOKIE["LoginUser"]);
                        $p->data['userid'] = $loginUser[0];
                        $p->data['now'] = date('d/m/y H:i:s');
                        $p->data['orderno'] = $_POST['real_order_no'];
                        $p->data['PICK_ORDER.PICK_ORDER'] = $_POST['real_order_no'];
                        $p->data['prodid'] = $_POST['product_id'];
                        //list($prodDesc,$prodDescType,$prodDescSsn) = split(",", $_POST['real_product_desc']);
                        list($prodDesc,$prodDescType,$prodDescSsn) = explode(",", $_POST['real_product_desc']);
                        if (isset($productInfo[$_POST['product_id']][0])) {
                            $p->data['proddesc'] = $productInfo[$_POST['product_id']][0];
                            $p->data['PROD_PROFILE.SHORT_DESC'] = $productInfo[$_POST['product_id']][0];
                        } else {
                            $p->data['proddesc'] = '';
                            $p->data['PROD_PROFILE.SHORT_DESC'] = '';
                        }
                        if ($prodDescType == 'I')
                        {
                            // an ssn
                            $p->data['proddesc'] = $prodDesc;
                        }
                        if (isset($productInfo[$_POST['product_id']][2])) {
                            $p->data['uom'] = $productInfo[$_POST['product_id']][2];
                        } else {
                            $p->data['uom'] = '';
                        }
                        //$p->data['contact'] = $orderInfo[0];
                        $p->data['contact'] = $orderInfo['CONTACT_NAME'];
                        //$p->data['customerpowo'] = $orderInfo[1];
                        $p->data['customerpowo'] = $orderInfo['CUSTOMER_PO_WO'];
                        //$p->data['specialinstructions1'] = $orderInfo[2];
                        $p->data['specialinstructions1'] = $orderInfo['SPECIAL_INSTRUCTIONS1'];
			// want pick items special instructions as well
    			foreach ($orderInfo as $k => $v) {
                        	$p->data['PICK_ORDER.' . $k] = $v;
			}
/*
	if d_addr line 1 or 2 populated
		use the d_addresses and name
	otherwise
		use the p addresses and name
	but
	want to include the name from the person table when the name not populated
*/
                        //if ($orderInfo[17] == '' and $orderInfo[18] == '') // no delivery address line 1 or 2
                        if ($orderInfo['D_ADDRESS_LINE1'] == '' and $orderInfo['D_ADDRESS_LINE2'] == '') // no delivery address line 1 or 2
                        {
                            // use the p_ addresses
                            //$p->data['deliverto'] = $orderInfo[6] . ' ' . $orderInfo[7] . ' ' . $orderInfo[8];
                            $p->data['deliverto'] = $orderInfo['P_TITLE'] . ' ' . $orderInfo['P_FIRST_NAME'] . ' ' . $orderInfo['P_LAST_NAME'];
                            //$p->data['delivertoline1'] = $orderInfo[9];
                            $p->data['delivertoline1'] = $orderInfo['P_ADDRESS_LINE1'];
                            //$p->data['delivertoline2'] = $orderInfo[10];
                            $p->data['delivertoline2'] = $orderInfo['P_ADDRESS_LINE2'];
                            //if ($orderInfo[11] == '' and $orderInfo[12] == '' and $orderInfo[13] == '') // no person address line 3 or 4 or 5
                            if ($orderInfo['P_ADDRESS_LINE3'] == '' and $orderInfo['P_ADDRESS_LINE4'] == '' and $orderInfo['P_ADDRESS_LINE5'] == '') // no person address line 3 or 4 or 5
                            {
                                //$p->data['delivertoline3'] = $orderInfo[32]; // city
                                $p->data['delivertoline3'] = $orderInfo['P_CITY']; // city
                                //$p->data['delivertoline4'] = $orderInfo[33]; // state
                                //$p->data['delivertoline5'] = $orderInfo[35]; // country
                                $p->data['delivertoline5'] = $orderInfo['P_COUNTRY']; // country
                                //$p->data['delivertoline4'] = $orderInfo[33] . ' ' . $orderInfo[34]; // post
                                $p->data['delivertoline4'] = $orderInfo['P_STATE'] . ' ' . $orderInfo['P_POST_CODE']; // post
                                //$p->data['delivertophone'] = $orderInfo[44]; // phone
                                $p->data['delivertophone'] = $orderInfo['P_PHONE']; // phone
                            } else {
                                //$p->data['delivertoline3'] = $orderInfo[11];
                                $p->data['delivertoline3'] = $orderInfo['P_ADDRESS_LINE3'];
                                //$p->data['delivertoline4'] = $orderInfo[12];
                                $p->data['delivertoline4'] = $orderInfo['P_ADDRESS_LINE4'];
                                //$p->data['delivertoline5'] = $orderInfo[13];
                                $p->data['delivertoline5'] = $orderInfo['P_ADDRESS_LINE5'];
                                $p->data['delivertophone'] = $orderInfo['P_PHONE']; // phone
                            }
                        }
                        else
                        {
                            // use the d_ addresses
                            //$p->data['deliverto'] = $orderInfo[14] . ' ' . $orderInfo[15] . ' ' . $orderInfo[16];
                            $p->data['deliverto'] = $orderInfo['D_TITLE'] . ' ' . $orderInfo['D_FIRST_NAME'] . ' ' . $orderInfo['D_LAST_NAME'];
                            //$p->data['delivertoline1'] = $orderInfo[17];
                            $p->data['delivertoline1'] = $orderInfo['D_ADDRESS_LINE1'];
                            //$p->data['delivertoline2'] = $orderInfo[18];
                            $p->data['delivertoline2'] = $orderInfo['D_ADDRESS_LINE2'];
                            //if ($orderInfo[19] == '' and $orderInfo[20] == '' and $orderInfo[21] == '') // no delivery address line 3 or 4 or 5
                            if ($orderInfo['D_ADDRESS_LINE3'] == '' and $orderInfo['D_ADDRESS_LINE4'] == '' and $orderInfo['D_ADDRESS_LINE5'] == '') // no delivery address line 3 or 4 or 5
                            {
                                //$p->data['delivertoline3'] = $orderInfo[36] ; // city
                                $p->data['delivertoline3'] = $orderInfo['D_CITY'] ; // city
                                //$p->data['delivertoline4'] = $orderInfo[37] ; // state
                                //$p->data['delivertoline5'] = $orderInfo[39]; // country
                                $p->data['delivertoline5'] = $orderInfo['D_COUNTRY']; // country
                                //$p->data['delivertoline4'] = $orderInfo[37] . ' ' . $orderInfo[38]; // post
                                $p->data['delivertoline4'] = $orderInfo['D_STATE'] . ' ' . $orderInfo['D_POST_CODE']; // post
                                //$p->data['delivertophone'] = $orderInfo[45]; // phone
                                $p->data['delivertophone'] = $orderInfo['D_PHONE']; // phone
                            } else {
                                //$p->data['delivertoline3'] = $orderInfo[19];
                                $p->data['delivertoline3'] = $orderInfo['D_ADDRESS_LINE3'];
                                //$p->data['delivertoline4'] = $orderInfo[20];
                                $p->data['delivertoline4'] = $orderInfo['D_ADDRESS_LINE4'];
                                //$p->data['delivertoline5'] = $orderInfo[21];
                                $p->data['delivertoline5'] = $orderInfo['D_ADDRESS_LINE5'];
                                $p->data['delivertophone'] = $orderInfo['D_PHONE']; // phone
                            }
                        }
                        if ( $p->data['deliverto'] == '  ')
                        {
                            $p->data['deliverto'] = $delivertoPersonInfo['TITLE'] . ' ' . $delivertoPersonInfo['FIRST_NAME'] . ' ' . $delivertoPersonInfo['LAST_NAME'];
                        }
                        {
                            //if ($orderInfo[25] == '' and $orderInfo[26] == '') // no supplier name 
                            //if ($orderInfo['S_FIRST_NAME'] == '' and $orderInfo['S_LAST_NAME'] == '') // no supplier name 
                            /*
	previously used 
	if S_FIRST_NAME or S_LAST_NAME populated use the 1st and last name from S_FIRST_NAME and S_LAST_NAME
	otherwise use the company name of the order (whose stock)
	now
	if SUPPLIER_ID populated use the company name of it
	if S_PERSON_ID populated use the 1st and last name from it
	otherwise use the 1st and last name of S_FIRST_NAME and S_LAST_NAME
                            */
                            $supplierFrom = '';
                            if (count($supplierInfo) > 0)
                            {
                                if ($supplierInfo[0] != '' ) // a company supplier name 
                                {
                                    $p->data['suppliedby'] = $supplierInfo[0] ;
                                    $supplierFrom = 'suppliercompany';
                                }
                            }
                            if ($supplierFrom == '')
                            {
                            	if (count($supplierPersonInfo) > 0)
                            	{
                                    if ($supplierPersonInfo['FIRST_NAME'] != '' or $supplierPersonInfo['LAST_NAME'] != '')
                                    {
                                        $p->data['suppliedby'] = $supplierPersonInfo['FIRST_NAME'] . ' ' . $supplierPersonInfo['LAST_NAME'];
                                        $supplierFrom = 'supplierperson';
                                    }
				}
                            }
                            if ($supplierFrom == '')
                            {
                                $p->data['suppliedby'] = $orderInfo['S_FIRST_NAME'] . ' ' . $orderInfo['S_LAST_NAME'];
                                $supplierFrom = 'suppliercontact';
                            }

/*
                            if ($orderInfo['S_FIRST_NAME'] == '' and $orderInfo['S_LAST_NAME'] == '') // no supplier name 
                            {
                                $p->data['suppliedby'] = $companyInfo[0] ;
                            } else {
                                //$p->data['suppliedby'] = $orderInfo[25] . ' ' . $orderInfo[26];
                                $p->data['suppliedby'] = $orderInfo['S_FIRST_NAME'] . ' ' . $orderInfo['S_LAST_NAME'];
                            }
*/
                            //$p->data['suppliedbyline1'] = $orderInfo[27];
                            $p->data['suppliedbyline1'] = $orderInfo['S_ADDRESS_LINE1'];
                            //if ($orderInfo[28] == '' and $orderInfo[29] == '') // no supplier address line 2 or 3 
                            if ($orderInfo['S_ADDRESS_LINE2'] == '' and $orderInfo['S_ADDRESS_LINE3'] == '') // no supplier address line 2 or 3 
                            {
                                //$p->data['suppliedbyline2'] = $orderInfo[40] . ', ' . $orderInfo[41] . ' ' . $orderInfo[42];
                                $p->data['suppliedbyline2'] = $orderInfo['S_CITY'] . ', ' . $orderInfo['S_STATE'] . ' ' . $orderInfo['S_POST_CODE'];
                                //$p->data['suppliedbyline3'] = $orderInfo[43];
                                $p->data['suppliedbyline3'] = $orderInfo['S_COUNTRY'];
                            } else {
                                //$p->data['suppliedbyline2'] = $orderInfo[28];
                                $p->data['suppliedbyline2'] = $orderInfo['S_ADDRESS_LINE2'];
                                //$p->data['suppliedbyline3'] = $orderInfo[29];
                                $p->data['suppliedbyline3'] = $orderInfo['S_ADDRESS_LINE3'];
                            }
                            //$p->data['suppliedbyphone'] = $orderInfo[30];
                            $p->data['suppliedbyphone'] = $orderInfo['S_PHONE'];
                        }
                        //$p->data['duedate'] = $orderInfo[46];
                        $p->data['duedate'] = $orderInfo['DUE_DATE'];
                        //$companyId = $orderInfo[31];
                        $companyId = $orderInfo['COMPANY_ID'];

                        $p->data['qty'] = $result[2];
                        $p->data['labelqty'] = $result[1];

    			// must update all issn's on this despatch for this product or ssn to zero or null picked_qty 
logme($Link, $tran_user, $tran_device, "before getNextLabel");
                        //$labelNo = getNextLabel($Link, $_POST['real_order_no']);
                       	//$labelNo = getNextLabel($Link, $_POST['real_order_no'],$_POST['real_order_no'], $_POST['product_id']);
                        if ($prodDescType == 'I')
                        {
                        	//$labelNo = getNextLabel($Link, $_POST['real_order_no'],$_POST['real_order_no'], '', $_POST['product_id']);
                        	$labelNo = getNextLabel($Link, $_POST['real_order_no'],$_POST['real_order_no'], '', $_POST['product_id'], $p->data['qty']);
                        }
                        else
                        {
                        	//$labelNo = getNextLabel($Link, $_POST['real_order_no'],$_POST['real_order_no'], $_POST['product_id']);
                        	$labelNo = getNextLabel($Link, $_POST['real_order_no'],$_POST['real_order_no'], $_POST['product_id'], '', $p->data['qty']);
                        }
              
// , $ssnId) 
logme($Link, $tran_user, $tran_device, "after call to getnextlabel");
                        $p->data['despatchlabel'] = $labelNo[1];
                        //$p->data['despatchlabelprefix'] = substr($labelNo[1],0,1);
                        //$p->data['despatchlabelsuffix'] = substr($labelNo[1],1,strlen($labelNo[1]) - 1);
                        $p->data['issnlabel'] = $labelNo[3];
                        $p->data['issnlabelprefix'] = substr($labelNo[3],0,2);
                        $p->data['issnlabelsuffix'] = substr($labelNo[3],2,strlen($labelNo[3]) - 2);
                        $p->data['other1'] = '';
                        $p->data['other2'] = '';

                        //print_r($p->data);

                        //echo $formatPath. $formatName;
                        $tpl = file_get_contents($formatPath . $formatName);
                        //echo strlen($tpl);
                        // first label set 
                        for ($i = 0; $i < $result[1]; $i++) {
                                    $save = fopen($printerPath .
                                                  $p->data['orderno'] .
                                                  '_' . $p->data['issnlabel'] .
                                                  '_DESPATCH.prn', 'w');
                                    $p->data['ofqty'] = $i + 1;
                                    //$p->send($tpl, $save);
                                    if (!$p->sysLabel($Link, $_POST['printer_id'], "DESPATCH", 1))
				    {
                                	$p->send($tpl, $save);
				    }
                                    /* update this despatch label to printed */
                                    /* save the product and qty used */
                                    updateLabel($Link, $labelNo, $_POST['product_id'] , $result[2], $prodDescType ) ;
                                    /* get next labels data */
                                    //$labelNo = getNextLabel($Link, $_POST['real_order_no']);
                                    //$labelNo = getNextLabel($Link, $_POST['real_order_no'],$_POST['real_order_no'], $_POST['product_id']);
                                    if ($prodDescType == 'I')
                                    {
                            		//$labelNo = getNextLabel($Link, $_POST['real_order_no'],$_POST['real_order_no'], '', $_POST['product_id']);
                        	        $labelNo = getNextLabel($Link, $_POST['real_order_no'],$_POST['real_order_no'], '', $_POST['product_id'], $p->data['qty']);
                                    }
                                    else
                                    {
                        		//$labelNo = getNextLabel($Link, $_POST['real_order_no'],$_POST['real_order_no'], $_POST['product_id']);
                        	        $labelNo = getNextLabel($Link, $_POST['real_order_no'],$_POST['real_order_no'], $_POST['product_id'], '', $p->data['qty']);
                                    }
                                    $p->data['despatchlabel'] = $labelNo[1];
                                    //$p->data['despatchlabelprefix'] = substr($labelNo[1],0,1);
                                    //$p->data['despatchlabelsuffix'] = substr($labelNo[1],1,strlen($labelNo[1]) - 1);
                                    $p->data['issnlabel'] = $labelNo[3];
                                    $p->data['issnlabelprefix'] = substr($labelNo[3],0,2);
                                    $p->data['issnlabelsuffix'] = substr($labelNo[3],2,strlen($labelNo[3]) - 2);
                                    $p->data['other1'] = '';
                                    $p->data['other2'] = '';
                                    fclose($save);
                        }
                        // second label set 
                        if ($result[5] > 0 or $result[4] > 0 ) {
                            // get starting next label
                            //$labelNo = getNextLabel($Link, $_POST['real_order_no']);
                            //$labelNo = getNextLabel($Link, $_POST['real_order_no'],$_POST['real_order_no'], $_POST['product_id']);
                            if ($prodDescType == 'I')
                            {
                        	//$labelNo = getNextLabel($Link, $_POST['real_order_no'],$_POST['real_order_no'], '', $_POST['product_id']);
                        	$labelNo = getNextLabel($Link, $_POST['real_order_no'],$_POST['real_order_no'], '', $_POST['product_id'], $p->data['qty']);
                            }
                            else
                            {
                        	//$labelNo = getNextLabel($Link, $_POST['real_order_no'],$_POST['real_order_no'], $_POST['product_id']);
                        	$labelNo = getNextLabel($Link, $_POST['real_order_no'],$_POST['real_order_no'], $_POST['product_id'], '', $p->data['qty']);
                            }
                            $p->data['despatchlabel'] = $labelNo[1];
                            //$p->data['despatchlabelprefix'] = substr($labelNo[1],0,1);
                            //$p->data['despatchlabelsuffix'] = substr($labelNo[1],1,strlen($labelNo[1]) - 1);
                            $p->data['issnlabel'] = $labelNo[3];
                            $p->data['issnlabelprefix'] = substr($labelNo[3],0,2);
                            $p->data['issnlabelsuffix'] = substr($labelNo[3],2,strlen($labelNo[3]) - 2);
                            $p->data['other1'] = '';
                            $p->data['other2'] = '';
                            $p->data['qty'] = $result[5];
                            $p->data['labelqty'] = $result[4];
                            for ($i = 0; $i < $result[4]; $i++) {
                                $save = fopen($printerPath .
                                              $p->data['orderno'] .
                                              '_' . $p->data['issnlabel'] .
                                              '_DESPATCH.prn', 'w');
                                $p->data['ofqty'] = $i + 1;
                                //$p->send($tpl, $save);
                                if (!$p->sysLabel($Link, $_POST['printer_id'], "DESPATCH", 1))
				{
                                	$p->send($tpl, $save);
				}
                                /* update this despatch label to printed */
                                /* save the product and 2 qtys used */
                                updateLabel($Link, $labelNo, $_POST['product_id'] , $result[5], $prodDescType ) ;
                                //$labelNo = getNextLabel($Link, $_POST['real_order_no']);
                                //$labelNo = getNextLabel($Link, $_POST['real_order_no'],$_POST['real_order_no'], $_POST['product_id']);
                                if ($prodDescType == 'I')
                                {
                        		//$labelNo = getNextLabel($Link, $_POST['real_order_no'],$_POST['real_order_no'], '', $_POST['product_id']);
                        	        $labelNo = getNextLabel($Link, $_POST['real_order_no'],$_POST['real_order_no'], '', $_POST['product_id'], $p->data['qty']);
                                }
                                else
                                {
                        		//$labelNo = getNextLabel($Link, $_POST['real_order_no'],$_POST['real_order_no'], $_POST['product_id']);
                        	        $labelNo = getNextLabel($Link, $_POST['real_order_no'],$_POST['real_order_no'], $_POST['product_id'], '', $p->data['qty']);
                                }
                                $p->data['despatchlabel'] = $labelNo[1];
                                $p->data['despatchlabelprefix'] = substr($labelNo[1],0,1);
                                $p->data['despatchlabelsuffix'] = substr($labelNo[1],1,strlen($labelNo[1]) - 1);
                                $p->data['issnlabel'] = $labelNo[3];
                                $p->data['issnlabelprefix'] = substr($labelNo[3],0,2);
                                $p->data['issnlabelsuffix'] = substr($labelNo[3],2,strlen($labelNo[3]) - 2);
                                $p->data['other1'] = '';
                                $p->data['other2'] = '';
                                fclose($save);
                            }
                        }
                        $prodId = getBDCScookie($Link, $tran_device, "PickProd");
                        //ibase_commit($dbTran);
			logme($Link, $tran_user, $tran_device, "complete " . $_POST['complete'] );
                        {
                            if ($_POST['complete'] == 'y') {
    				// then must update all issn's on this despatch for this product to zero qty where the picked qty is null or 0
                                // now adjust issn qtys
                                // do dsuq
                                $myResult = sendDSUQTransaction($Link, $p->data['orderno'] );
                                //exitBDCSScreen();

                                exitBDCSScreen($Link, $dbTran, $tran_device, $tran_user);
                                exit(0);
                            }
                        }
    			showPageVerify($Link, $_POST['real_order_no'], $prodId, null);
                        ibase_commit($dbTran);
			$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
                    }
                }
            } else {
                $prodId = getBDCScookie($Link, $tran_device, "PickProd");
    		showPageVerify($Link, $_POST['real_order_no'], $prodId, $errors);
                ibase_commit($dbTran);
		$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
            }
        }
    }
    break;

case 2:  // SSN scanned
    if (isset($_POST['action_back_x'])) {
	if ($_POST['action_back_x'] > 0) {
            //exitBDCSScreen();
            exitBDCSScreen($Link, $dbTran, $tran_device, $tran_user);
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
                $prodId = getBDCScookie($Link, $tran_device, "PickProd");
                showPageVerify($Link, $orderNo, $prodId, array());
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
            //exitBDCSScreen();
            exitBDCSScreen($Link, $dbTran, $tran_device, $tran_user);
            exit;
        }
    } 
    {
        $errors = validatePageDelivery($Link);
        if (empty($errors)) {
            if ($_POST['order_no'] != '') {
                $prodId = getBDCScookie($Link, $tran_device, "PickProd");
                showPageVerify($Link, $_POST['order_no'], $prodId, array());
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
    //showPageSSN($Link);
    // what about the prod
    if ($prod_no == '')
    {
        // get the prod from the issn
        $prodId =  getIssnProduct($Link, $ssn);
	if ($prodId == null)
        {
            // get the prod from the scanned issn
            $prodId =  getIssnProduct($Link, $scanned_ssn);
	    if ($prodId == null)
	    {
		$prodId = '';
 	    }
        }
    }
    else
    {
        $prodId =  $prod_no;
    }
    setBDCScookie($Link, $tran_device, "PickProd", $prodId);
    $nextScreen = getBDCScookie($Link, $tran_device, "PickNextScreen");
//echo ("Prod:" . $prodId . "<br />");
    showPageVerify($Link, $order, $prodId, array());
}

logme($Link, $tran_user, $tran_device, "addrprodlabel end " );
ibase_close($Link);
?>
