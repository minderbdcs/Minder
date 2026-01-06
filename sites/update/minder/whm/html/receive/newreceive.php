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

/**
 * get ORIGINAL_SSN from ISSN table by SSN_ID
 *
 * @param ibase_link $Link Connection to database
 * @param string $ssn_id SSN_ID
 * @return string
 */
function getOriginalSsn($Link, $ssn_id)
{
    $sql = "SELECT ORIGINAL_SSN FROM ISSN WHERE SSN_ID = ?";
    $query = ibase_prepare($Link, $sql);
    $output = false;
        
    if (false !== ($result = ibase_execute($query, $ssn_id))) {
        if (false !== ($row = ibase_fetch_row($result))) {
            $output = $row[0];
        } else {
            $output = false;
        }
        ibase_free_result($result);
    }
    return $output;
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
 * getSSNInfo
 *
 * @param $Link
 * @param string $ssnId
 * @return array or null
 */
function getSSNInfo($Link, $ssnId) {
    $result = array();
    $sql = "SELECT SSN_DESCRIPTION,
            SSN_TYPE AS TYPE_1,
            GENERIC AS TYPE_2, 
            SSN_SUB_TYPE AS TYPE_3,
            BRAND,
            MODEL,
            SERIAL_NUMBER,
            LEGACY_ID,
            SUPPLIER_ID,
            COMPANY_ID,
            MER_DAY(LAST_UPDATE_DATE) || '/' || MER_MONTH(LAST_UPDATE_DATE) || '/' || SUBSTR(CAST(MER_YEAR(LAST_UPDATE_DATE) AS CHAR(4)) , 3,4) AS UPDATE_DATE
            FROM SSN 
            WHERE SSN_ID = ?";
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        $r = ibase_execute($q, $ssnId);
        if ($r) {
            //while(( $d = ibase_fetch_row($r))) { 
            while(( $d = ibase_fetch_assoc($r))) { 
                $result = $d;
            }
            ibase_free_result($r);
        }
        ibase_free_query($q);
    }
    return $result;
}


//****************************************************************/
/**
 * getBrandList
 * <pre>
 * SELECT CODE, DESCRIPTION
 * FROM   BRAND
 * </pre>
 *
 * @return array CODE is key, DESCRIPTION is value
 */
function getBrandList($Link)
{
    $output = array();
    //$output[''] = '';
    $query  = "SELECT CODE, DESCRIPTION
               FROM   BRAND";
    $result = ibase_query($Link, $query);
    if (false !== $result) {
        while (false !== ($row = ibase_fetch_row($result))) {
            $output[$row[0]] = $row[1];
        }
        ibase_free_result($result);
    }
    return array_merge(array('' => ''), $output);
}

/**
 * getVarietyList
 * <pre>
 * SELECT CODE, DESCRIPTION
 * FROM   GENERIC
 * WHERE SSN_TYPE = $ssn_type
 * </pre>
 *
 * @return array CODE is key, DESCRIPTION is value
 */
function getVarietyList($Link, $grnNo)
{
    $output = array();
    $output[''] = '';
    $query  = "SELECT CODE, DESCRIPTION
               FROM   GENERIC";

    $qp     = ibase_prepare($Link, $query);
    $result = ibase_execute($qp);

    //$result = ibase_query($Link, $query);
    if (false !== $result) {
        while (false !== ($row = ibase_fetch_row($result))) {
            $output[$row[0]] = $row[1];
        }
        ibase_free_result($result);
    }
    return $output;
}
/**
 * getVarietyList
 * <pre>
 * SELECT CODE, DESCRIPTION
 * FROM   GENERIC
 * WHERE SSN_TYPE = $ssn_type
 * </pre>
 *
 * @return array CODE is key, DESCRIPTION is value
 */
function getSubtypeList($Link, $grnNo)
{
    $output = array();
    $output[''] = '';
    $query  = "SELECT CODE, DESCRIPTION
               FROM   SSN_SUB_TYPE";

    $qp     = ibase_prepare($Link, $query);
    $result = ibase_execute($qp);

    //$result = ibase_query($Link, $query);
    if (false !== $result) {
        while (false !== ($row = ibase_fetch_row($result))) {
            $output[$row[0]] = $row[1];
        }
        ibase_free_result($result);
    }
    return $output;
}
//****************************************************************/

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
 * getLotInfo
 */
function getLotInfo() {
    $lot_no = null;
    if (isset($_GET['lot_no'])) $lot_no = $_GET['lot_no'];
    if (isset($_POST['lot_no'])) $lot_no = $_POST['lot_no'];
    if ($lot_no == '') {
        if (isset($_GET['lot_line'])) $lot_no = $_GET['lot_line'];
        if (isset($_POST['lot_line'])) $lot_no = $_POST['lot_line'];
    }

    return $lot_no;
}


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

    //logme($Link, $tran_user, $tran_device, "printers :" .print_r($printerOpts, True).":");
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
 * getReceiveLocationOpts
 *
 * @param $Link
 */
function getReceiveLocationOpts($Link) {
    list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
    $myWh = getBDCScookie($Link, $tran_device, "CURRENT_WH_ID");
    //$sql = 'SELECT LOCATION.WH_ID || LOCATION.LOCN_ID, LOCATION.LOCN_NAME FROM LOCATION, SESSION WHERE STORE_AREA = \'RC\' AND LOCATION.WH_ID = SESSION.DESCRIPTION AND SESSION.CODE = \'CURRENT_WH_ID\' AND SESSION.DEVICE_ID = \'MQ\' ORDER BY LOCN_NAME';
    $sql = 'SELECT LOCATION.WH_ID || LOCATION.LOCN_ID, LOCATION.LOCN_NAME FROM LOCATION  WHERE STORE_AREA = \'RC\' AND LOCATION.WH_ID = \'' . $myWh . '\' ORDER BY LOCN_NAME';
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
    //$sql = 'SELECT CODE, DESCRIPTION FROM UOM, PROD_PROFILE WHERE UOM_TYPE = \'UT\' AND UOM.CODE = PROD_PROFILE.UOM';
    $sql = 'SELECT CODE, DESCRIPTION FROM UOM WHERE UOM_TYPE = \'UT\' AND EXISTS(SELECT FIRST 1 PROD_ID FROM PROD_PROFILE WHERE PROD_PROFILE.UOM = UOM.CODE ) ';
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
 * @param string $companyId
 */
function getProductInfo($Link, $companyId = null) {
    if ($companyId == null) 
    {
        //$sql = 'SELECT PROD_PROFILE.PROD_ID,
        $sql = 'SELECT FIRST 100 PROD_PROFILE.PROD_ID,
                   PROD_PROFILE.SHORT_DESC,
                   UOM.CODE,
                   UOM.DESCRIPTION,
                   PROD_PROFILE.TEMPERATURE_ZONE,
                   PROD_PROFILE.PROD_TYPE,
                   PROD_PROFILE.SSN_TYPE
            FROM PROD_PROFILE JOIN UOM ON PROD_PROFILE.UOM = UOM.CODE
            ORDER BY PROD_ID';
            /*  AND UOM.UOM_TYPE = \'UT\'*/
    } else {
        //$sql = 'SELECT PROD_PROFILE.PROD_ID,
        $sql = 'SELECT FIRST 100 PROD_PROFILE.PROD_ID,
                   PROD_PROFILE.SHORT_DESC,
                   UOM.CODE,
                   UOM.DESCRIPTION,
                   PROD_PROFILE.TEMPERATURE_ZONE,
                   PROD_PROFILE.PROD_TYPE,
                   PROD_PROFILE.SSN_TYPE
            FROM PROD_PROFILE JOIN UOM ON PROD_PROFILE.UOM = UOM.CODE
            WHERE PROD_PROFILE.COMPANY_ID  = \'' . $companyId . '\'
            OR    PROD_PROFILE.COMPANY_ID  IS NULL
            OR    PROD_PROFILE.COMPANY_ID = \'\' 
            ORDER BY PROD_ID';
            /*  AND UOM.UOM_TYPE = \'UT\'*/
    } 
    if (!($result = ibase_query($Link, $sql))) {
        exit('Unable to read products!');
    }
    $productInfo = array();
    while (($row = ibase_fetch_row($result))) {
        //$productInfo[$row[0]] = array($row[1], $row[2], $row[3], $row[4]);
        //$productInfo[$row[0]] = array($row[1], $row[2], $row[3], $row[4], $row[5]);
        $productInfo[$row[0]] = array($row[1], $row[2], $row[3], $row[4], $row[5], $row[6]);
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
 * getOwnedByOpts
 *
 * @param $Link
 */
function getGroupTitles($Link) {
    $sql = 'SELECT FIELD_SSN_TYPE, FIELD_GENERIC, FIELD_BRAND, FIELD_MODEL, FIELD_SUB_TYPE FROM SSN_GROUP';
    if (!($result = ibase_query($Link, $sql))) {
        exit('Unable to read group!');
    }
    $groupTitles = array();
    while (($row = ibase_fetch_row($result))) {
        $groupTitles['FIELD_SSN_TYPE'] = $row[0];
        $groupTitles['FIELD_GENERIC'] = $row[1];
        $groupTitles['FIELD_BRAND'] = $row[2];
        $groupTitles['FIELD_MODEL'] = $row[3];
        $groupTitles['FIELD_SUB_TYPE'] = $row[4];
    }
    ibase_free_result($result);

    return $groupTitles;
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
 * getPurchaseOrderType
 *
 * @param $Link
 * @param string $orderNo
 * @return string or null
 */
function getPurchaseOrderType($Link, $orderNo) {
    $sql = 'SELECT ORDER_TYPE FROM PURCHASE_ORDER WHERE PURCHASE_ORDER = ?';
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
function validatePageLot($Link) {
    $errors = array();
    if ($_POST['grn_no'] != '') {
        if (getGrnOrderNo($Link, $_POST['grn_no']) == '') {
            $errors['grn_no'] = 'Invalid or closed GRN';
        }
    } else {
	$type = getTypeInfo();
	if (($type == "PO") or
	    ($type == "WO") or
	    ($type == "RA") or
	    ($type == "TR"))
	{
            if ($_POST['order_no'] == '') {
                $errors['order_no'] = 'Order no is Required';
            }
            if ($_POST['order_no'] != '') {
                if (getPurchaseOrderType($Link, $_POST['order_no']) !=  $type)  {
                    $errors['order_no'] = 'Order not of ' . $type . ' type ';
                }
                elseif (getPurchaseOrderStatus($Link, $_POST['order_no'] ) != 'OP') {
                    $errors['order_no'] = 'Invalid or closed order no';
                }
            }
	}
        if ($_POST['lot_no'] == '') {
            $errors['lot_no'] = 'Lot No must not be empty';
        }
	else {
		//include "checkdata.php";
		require_once "checkdata.php";

        	$wk_lotNo = $_POST['lot_no'];
		$field_type = checkForTypein($wk_lotNo, 'GRNLOT','LOTNO' ); 
		if ($field_type == "none")
		{
			//dosn't match a lot
            	$errors['lot_no'] = 'Not a valid Lot No';
		}
	}
    }

    return $errors;
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
	$type = getTypeInfo();
	if (($type == "PO") or
	    ($type == "WO") or
	    ($type == "RA") or
	    ($type == "TR"))
	{
            if ($_POST['order_no'] == '') {
                $errors['order_no'] = 'Order no is Required';
            }
            if ($_POST['order_no'] != '') {
                if (getPurchaseOrderType($Link, $_POST['order_no']) !=  $type)  {
                    $errors['order_no'] = 'Order not of ' . $type . ' type ';
                }
                elseif (getPurchaseOrderStatus($Link, $_POST['order_no'] ) != 'OP') {
                    $errors['order_no'] = 'Invalid or closed order no';
                }
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
    //phpinfo();
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
    if ($_POST['lot_line'] != '') {
        if ($_POST['lot_no'] == '') {
            $errors['lot_no'] = 'Lot No must not be empty';
        }
	else {
		//include "checkdata.php";
		require_once "checkdata.php";

        	$wk_lotNo = $_POST['lot_no'];
		$field_type = checkForTypein($wk_lotNo, 'GRNLOT','LOTNO' ); 
		if ($field_type == "none")
		{
			//dosn't match a lot
            	$errors['lot_no'] = 'Not a valid Lot No';
		}
	}
    }
    return $errors;
}


/**
 * showPageLot
 *
 * @param $Link
 */
function showPageLot($Link, $errors = array()) {
    $type = getTypeInfo();
    $typeName = getTypeName($type);
    $message = isset($_GET['message']) ? $_GET['message'] : '';
    include 'nr_lot.php';
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
    $lot_no = getLotInfo();
    if ($lot_no != null) {
        $sentById = $ownedById;
    } else {
        $sentById = null;
    }
    $message = isset($_GET['message']) ? $_GET['message'] : '';
    list($user, $device) = explode("|", $_COOKIE["LoginUser"]);
    if (!isset($_POST['printer_id']) ) {
        $_POST['printer_id'] = getBDCScookie($Link, $device, 'printer');
    }
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
    $lot_no = getLotInfo();
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
    $lot_no = $_POST['lot_no'];

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
    $lot_no = $_POST['lot_no'];

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
    list($user, $device) = explode("|", $_COOKIE["LoginUser"]);
    $type = getTypeInfo();
    $typeName = getTypeName($type);

    $printerOpts = getPrinterOpts($Link);
    $lineNo = getNextLineNo($Link, $grnNo, $orderNo);
    $ownedBy = isset($_POST['owned_by']) ? $_POST['owned_by'] : '';
    $productInfo = getProductInfo($Link, $ownedBy);
    //-- $productNames not defined before
    //-- use $productInfo for testing purposes

    //-- $t = array_keys($productNames);
    $t = array_keys($productInfo);
    $productIdOpts       = array_combine($t, $t);
    $umOpts              = getUmOpts($Link);
    $receiveLocationOpts = getReceiveLocationOpts($Link);
    $varietyList         = getVarietyList($Link, $grnNo);
    $subtypeList         = getSubtypeList($Link, $grnNo);
    $brandList           = getBrandList($Link);

    $vehReg = isset($_POST['veh_reg']) ? $_POST['veh_reg'] : '';

    $lot_no = getLotInfo();

    $wk_prod_type = getBDCScookie($Link, $device, "ReceiveProdType" );
    //$wk_prod_type = '';

    if (!isset($_POST['printer_id']) ) {
        $_POST['printer_id'] = getBDCScookie($Link, $device, 'printer');
    }
    $groupTitles =  getGroupTitles($Link) ;

    include 'nr_verify_0310_001.php';
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
case 7:  // Lot  submitted
    if (isset($_POST['action_back_x'])) {
        header('Location: /whm/receive/receive_menu.php');
        echo '<html><head><title>Redirect</title></head><body><p>You are being redirected to <a href="/whm/receive/receive_menu.php">/whm/receive/receive_menu.php"</a></p></body></html>';
    } else {
        $errors = validatePageLot($Link);
        if (empty($errors)) {
                showPageDelivery($Link);
        } else {
            showPageLot($Link, $errors);
        }
    }
    break;

case 6: // 3rd PartyNo Yes
{
    if (isset($_POST['action_back_x'])) {
        include 'trans.php';
        sendGRNXTransaction($Link, $_POST['grn_no']);
        header('Location: /whm/receive/receive_menu.php');
        echo '<html><head><title>Redirect</title></head><body><p>You are being redirected to <a href="/whm/receive/receive_menu.php">/whm/receive/receive_menu.php"</a></p></body></html>';
    } else {
        if (isset($_POST['third_no'])) {
            if ($_POST['third_no'] !== '') {
                $printerIp = getPrinterIp($Link, $_POST['printer_id']);
                if ($printerIp == '') {
                    $errors['printer_id'] = 'No IP address for printer';
                }
                $printerDir = getPrinterDir($Link, $_POST['printer_id']);
                if ($printerDir == '') {
                    $errors['printer_id'] = 'No Folder for printer';
                }
                if (empty($errors)) {
                    if (false === isset($_POST['cIssn1'])) {
                        $cIssn1 = $result[0];
                    } else {
                        $cIssn1 = $_POST['cIssn1'];
                    }
                    if (false === isset($_POST['cIssn2'])) {
                        $cIssn2 = $result[3];
                    } else {
                        $cIssn2 = $_POST['cIssn2'];
                    }
                    $result = $_POST['result'];
                    $rQty1 = $_POST['r_qty1'] + 0;
                    $rQty2 = $_POST['r_qty2'] + 0;
                    $ownedBy = isset($_POST['owned_by']) ? $_POST['owned_by'] : '';
                    $productInfo = getProductInfo($Link, $ownedBy);
                    $umOpts = getUmOpts($Link);
                    include 'Printer.php';
                    $p = new Printer($printerIp);
                    $p->data['printer_id'] = $_POST['printer_id'];
                    $p->data['ownerid'] = $_POST['owned_by'];
                    $p->data['ISSN.COMPANY_ID'] = $p->data['ownerid'] ;
                    $p->data['issn'] = $cIssn1;
                    $p->data['ISSN.SSN_ID'] = $cIssn1;
                    $wk_save_issn = $cIssn1;
                    $ssn_original = getOriginalSsn($Link, $cIssn1);
                    $p->data['ISSN.ORIGINAL_SSN'] = $ssn_original ;
                    $ssnInfo = getSSNInfo($Link, $ssn_original);
                    $p->data['issnlabelprefix'] = substr($cIssn1,0,2);
                    $p->data['issnlabelsuffix'] = substr($cIssn1,2,strlen($cIssn1) - 2);

                    $p->data['qty'] = $result[2];
                    $p->data['ISSN.CURRENT_QTY'] = $p->data['qty'] ;
                    $p->data['palletno'] = $_POST['third_no'];
                    $p->data['ISSN.OTHER1'] = $p->data['palletno'] ;
                    $p->data['parentid'] = "";
                    $p->data['other3'] = "";
                    $p->data['pickorder'] = "";
                    //$loginUser = split('\|', $_COOKIE['LoginUser']);
                    $loginUser = explode("|", $_COOKIE["LoginUser"]);
                    $p->data['userid'] = $loginUser[0];
                    $p->data['ISSN.CREATED_BY'] = $p->data['userid'] ;
                    if (isset($productInfo[$_POST['product_id']][0])) {
                        $p->data['description'] = $productInfo[$_POST['product_id']][0];
                    } else {
                        $p->data['description'] = '';
                    }
                    $p->data['PROD_PROFILE.SHORT_DESC'] = $p->data['description'] ;
                    $p->data['product_id'] = $_POST['product_id'];
                    $p->data['ISSN.PROD_ID'] = $p->data['product_id'] ;
                    $p->data['PROD_PROFILE.PROD_ID'] = $p->data['product_id'] ;
                    if (isset($productInfo[$_POST['product_id']][2])) {
                        $p->data['um'] = $productInfo[$_POST['product_id']][2];
                    } else {
                        $p->data['um'] = '';
                    }
                    $p->data['UOM.CODE'] = $p->data['um'] ;
                    $p->data['now'] = date('d/m/y H:i:s');
                    $p->data['ISSN.CREATE_DATE'] = $p->data['now'] ;
                    if (isset($productInfo[$_POST['product_id']][3])) {
                        $p->data['tempzone'] = $productInfo[$_POST['product_id']][3];
                    } else {
                        $p->data['tempzone'] = '';
                    }
                    $p->data['PROD_PROFILE.TEMPERATURE_ZONE'] = $p->data['tempzone'] ;
                    if (isset($productInfo[$_POST['product_id']][4])) {
                        $p->data['prodtype'] = $productInfo[$_POST['product_id']][4];
                    } else {
                        $p->data['prodtype'] = '';
                    }
                    $p->data['PROD_PROFILE.PROD_TYPE'] = $p->data['prodtype'] ;
                    /* 20-01-09 add
                       ssn_description as ssn_desc
                       ssn_type as type_1
                       generic as type_2
                       ssn_sub_type as type_3
                       model as model
                       brand as brand
                       serial number as serial
                       print title as title_1
                       print file version as version
                    */
                    $p->data['ssn_desc'] = $ssnInfo['SSN_DESCRIPTION'];
                    $p->data['SSN.SSN_DESCRIPTION'] = $p->data['ssn_desc'] ;
                    //$p->data['type_1'] = $ssnInfo['TYPE_1'];
                    if (isset($productInfo[$_POST['product_id']][5])) {
                        $p->data['type_1'] = $productInfo[$_POST['product_id']][5];
                    } else {
                        $p->data['type_1'] = '';
                    }
                    $p->data['SSN.TYPE_1'] = $p->data['type_1'] ;
                    $p->data['SSN.SSN_TYPE'] = $p->data['type_1'] ;
                    //$p->data['type_2'] = $ssnInfo['TYPE_2'];
                    $p->data['type_2'] = $_POST['variety_id'];
                    $p->data['SSN.TYPE_2'] = $p->data['type_2'] ;
                    $p->data['SSN.GENERIC'] = $p->data['type_2'] ;
                    //$p->data['type_3'] = $ssnInfo['TYPE_3'];
                    $p->data['type_3'] = $_POST['subtype_id'];
                    $p->data['SSN.TYPE_3'] = $p->data['type_3'] ;
                    $p->data['SSN.SSN_SUB_TYPE'] = $p->data['type_3'] ;
                    $p->data['model'] = $ssnInfo['MODEL'];
                    $p->data['SSN.MODEL'] = $p->data['model'] ;
                    //$p->data['brand'] = $ssnInfo['BRAND'];
                    $p->data['brand'] = $_POST['brand_id'];
                    $p->data['SSN.BRAND'] = $p->data['brand'] ;
                    $p->data['serial'] = $ssnInfo['SERIAL_NUMBER'];
                    $p->data['SSN.SERIAL_NUMBER'] = $p->data['serial'] ;
                    $p->data['title_1'] = "";
                    $p->data['version'] = "";
                    if (isset($_POST['receive_location'] )) {
                        $p->data['ISSN.WH_ID'] = substr($_POST['receive_location'], 0, 2);
                        $p->data['ISSN.LOCN_ID'] = substr($_POST['receive_location'], 2, 10);
                    } else {
                        $p->data['ISSN.WH_ID'] = '';
                        $p->data['ISSN.LOCN_ID'] = '';
                    }
                    // get the label data for this issn
                    $q = getISSNLabel($Link, $p );
                    //echo("\nP:");
                    //var_dump($p);
                    //echo("\nQ:");
                    //var_dump($q);
                    //$tpl = file_get_contents('./ISSN-new.prn');
                    $tpl = "";
                    //if (false == ($tpl = file_get_contents($printerDir . 'ISSN-new.fmt'))) {
                    //    $tpl = file_get_contents('./ISSN-new.prn');
                    //}
                    //-- proceed while not printed all labels in 1st set
                    if ($result[1] > $rQty1) {
                        include 'trans.php';
                                $UIO_res = sendUIO1Transaction($Link,
                                                    $cIssn1,
                                                    $_POST['third_no']);
                                    $rQty1++;
                                    //$save = fopen('/data/asset.rf/' . $_POST['printer_id'] .
                                    //              '/' . $p->data['issn'] . '_ISSN_' . $p->data['qty'] . '.prn', 'w');
                                    $save = fopen($printerDir .
                                                  $p->data['issn'] . '_ISSN_' . $p->data['qty'] . '.prn', 'w');
                                    if (!$p->sysLabel($Link, $_POST['printer_id'], "ISSN", 1))
                                    {
                                        $p->send($tpl, $save);
                                    }
                                    //$cIssn1++;
                                    $wk_suffix = "1" . $p->data['issnlabelsuffix'];
                                    $wk_suffix++;
                                    $p->data['issnlabelsuffix'] = substr($wk_suffix,1);
                                    $p->data['issn'] = $p->data['issnlabelprefix'] .  $p->data['issnlabelsuffix']; 
                                    $cIssn1 = $p->data['issn'];
                                    $p->data['ISSN.SSN_ID'] = $cIssn1;
                                    fclose($save);
                    } else {
                    //-- proceed while not printed all labels in 2nd set
                        if ($result[4] > $rQty2) {
                            include 'trans.php';
                                    $UIO_res = sendUIO1Transaction($Link,
                                                        $cIssn2,
                                                        $_POST['third_no']);
                                        $rQty2++;
                                        $p->data['issn'] = $cIssn2;
                                        $p->data['issnlabelprefix'] = substr($cIssn2,0,2);
                                        $p->data['issnlabelsuffix'] = substr($cIssn2,2,strlen($cIssn2) - 2);
                                        $p->data['ISSN.SSN_ID'] = $cIssn2;
                                        $p->data['qty']  = $result[5];
                                        $p->data['ISSN.CURRENT_QTY'] = $p->data['qty'] ;
                                        //$save = fopen('/data/asset.rf/' . $_POST['printer_id'] .
                                        //              '/' . $p->data['issn'] . '_ISSN_' . $p->data['qty'] . '.prn', 'w');
                                        $save = fopen($printerDir .
                                                      $p->data['issn'] . '_ISSN_' . $p->data['qty'] . '.prn', 'w');
                                        if (!$p->sysLabel($Link, $_POST['printer_id'], "ISSN", 1))
                                        {
                                            $p->send($tpl, $save);
                                        }
                                        //$cIssn2++;
                                        $wk_suffix = "1" . $p->data['issnlabelsuffix'];
                                        $wk_suffix++;
                                        $p->data['issnlabelsuffix'] = substr($wk_suffix,1);
                                        $p->data['issn'] = $p->data['issnlabelprefix'] .  $p->data['issnlabelsuffix']; 
                                        $cIssn2 = $p->data['issn'];
                                        $p->data['ISSN.SSN_ID'] = $cIssn2;
                                        fclose($save);
                        } else {
                            if ($_POST['complete'] == 'y') {
                                header('Location: /whm/receive/receive_menu.php');
                                echo '<html><head><title>Redirect</title></head><body><p>You are being redirected to <a href="/whm/receive/receive_menu.php">/whm/receive/receive_menu.php"</a></p></body></html>';
                                exit(0);
                            }
                            showPageVerify($Link, $_POST['real_grn_no'], $_POST['real_order_no'], null);
                            exit(0);
                        }
                    }
                    //-- while TOTAL printed labels less then total_labels, retrieve 3rd party No
                    if ($rQty1 + $rQty2 < $_POST['total_labels'] &&
                        false == isset($errors) ) {
                        include 'trdparty.php';
                    } elseif (false == isset($signalVerify)) {
                            if ($_POST['complete'] == 'y') {
                                header('Location: /whm/receive/receive_menu.php');
                                echo '<html><head><title>Redirect</title></head><body><p>You are being redirected to <a href="/whm/receive/receive_menu.php">/whm/receive/receive_menu.php"</a></p></body></html>';
                                exit(0);
                            }
                            showPageVerify($Link, $_POST['real_grn_no'], $_POST['real_order_no'], null);
                            exit(0);
                    }
                }
            }
        }
    }
}
    break;
case 5:  // VERIFY submitted
    if (isset($_POST['action_back_x'])) {
        include 'trans.php';
        sendGRNXTransaction($Link, $_POST['grn_no']);
        header('Location: /whm/receive/receive_menu.php');
        echo '<html><head><title>Redirect</title></head><body><p>You are being redirected to <a href="/whm/receive/receive_menu.php">/whm/receive/receive_menu.php"</a></p></body></html>';
    } else {
        if (isset($_POST['action_continue_x'])) {
            $errors = validatePageVerify($Link);
            $printerIp = getPrinterIp($Link, $_POST['printer_id']);
            if ($printerIp == '') {
                $errors['printer_id'] = 'No IP address for printer';
            }
            $printerDir = getPrinterDir($Link, $_POST['printer_id']);
            if ($printerDir == '') {
                $errors['printer_id'] = 'No Folder for printer';
            }
            if (empty($errors)) {
                include 'trans.php';
                $dbTran = ibase_trans(IBASE_DEFAULT, $Link);
                if ($_POST['lot_line'] != '') {
			if ($_POST['lot_no'] <> $_POST['lot_line']) {
				// lot no changed so do a uglt
                		$wk_ugxx_result = sendUGLTTransaction($Link, $_POST['real_grn_no'], $_POST['lot_no']);
			}
                }
		// save the prod type for next receive
    		list($user, $device) = explode("|", $_COOKIE["LoginUser"]);
    		setBDCScookie($Link, $device, "ReceiveProdType" ,$_POST['prodtype'] );
                $result = sendGRNVTransaction($Link);
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
                ibase_commit($dbTran);
                if (isset($_POST['recorded'])) {
                    $_POST['recorded']++;
                }
                //if (count($result) == 8 && $result[7] == 'Processed successfully') {
                if (count($result) == 11 && $result[10] == 'Processed successfully') {
                    $ssn_original = getOriginalSsn($Link, $result[0]);
                    if (false === $ssn_original) {
                        echo "Error occured. No ORIGINAL_SSN found for " . htmlspecialchars($result[0]) . "Can't continue.";
                        echo '<p>Return to menu <a href="/whm/receive/receive_menu.php">/whm/receive/receive_menu.php"</a></p>';
                        die();
                    }
                    //$ssnInfo = getSSNInfo($Link, $ssn_original);
                    //-- send NIOB, NIBC transaction with ssn_original
                    if ($_POST['variety_id'] != '') {
                        $NIOB_result = sendNIOBTransaction($Link,
                                                           $ssn_original);
                    }
                    if ($_POST['subtype_id'] != '') {
                        $NIOB_result = sendNID3Transaction($Link,
                                                           $ssn_original);
                    }
                    if ($_POST['brand_id'] != '') {
                        $NIBC_result = sendNIBCTransaction($Link,
                                                           $ssn_original);
                    }
                    $ssnInfo = getSSNInfo($Link, $ssn_original);
                    //-- if 3rd party present go to 3rd Party Screen
                    if (isset($_POST['thirdparty']) && $_POST['thirdparty']=='y') {
                        $rQty1 = 0;
                        $rQty2 = 0;
                        $cIssn1 = $result[0];
                        $cIssn2 = $result[3];
                        include "trdparty.php";
                    } else {
                        //-- otherwise print labels
                        $ownedBy = isset($_POST['owned_by']) ? $_POST['owned_by'] : '';
                        $productInfo = getProductInfo($Link, $ownedBy);
                        $umOpts = getUmOpts($Link);
                        include 'Printer.php';

                        $p = new Printer($printerIp);

                        $p->data['printer_id'] = $_POST['printer_id'];
                        $p->data['ISSN.ORIGINAL_SSN'] = $ssn_original ;
                        $p->data['ownerid'] = $_POST['owned_by'];
                        $p->data['ISSN.COMPANY_ID'] = $p->data['ownerid'] ;
                        $p->data['issn'] = $result[0];
                        $p->data['ISSN.SSN_ID'] = $p->data['issn'];
                        $wk_save_issn = $result[0];
                        $p->data['issnlabelprefix'] = substr($result[0],0,2);
                        $p->data['issnlabelsuffix'] = substr($result[0],2,strlen($result[0]) - 2);
                        $p->data['qty'] = $result[2];
                        $p->data['ISSN.CURRENT_QTY'] = $p->data['qty'] ;
                        $p->data['palletno'] = '';
                        $p->data['parentid'] = '';
                        $p->data['other3'] = "";
                        $p->data['pickorder'] = "";
                        //$loginUser = split('\|', $_COOKIE['LoginUser']);
                        $loginUser = explode('\|', $_COOKIE['LoginUser']);
                        $p->data['userid'] = $loginUser[0];
                        $p->data['ISSN.CREATED_BY'] = $p->data['userid'] ;
                        if (isset($productInfo[$_POST['product_id']][0])) {
                            $p->data['description'] = $productInfo[$_POST['product_id']][0];
                        } else {
                            $p->data['description'] = '';
                        }
                        $p->data['PROD_PROFILE.SHORT_DESC'] = $p->data['description'] ;
                        $p->data['product_id'] = $_POST['product_id'];
                        $p->data['ISSN.PROD_ID'] = $p->data['product_id'] ;
                        $p->data['PROD_PROFILE.PROD_ID'] = $p->data['product_id'] ;
                        if (isset($productInfo[$_POST['product_id']][2])) {
                            $p->data['um'] = $productInfo[$_POST['product_id']][2];
                        } else {
                            $p->data['um'] = '';
                        }
                        $p->data['UOM.CODE'] = $p->data['um'] ;
                        $p->data['now'] = date('d/m/y H:i:s');
                        $p->data['ISSN.CREATE_DATE'] = $p->data['now'] ;
                        if (isset($productInfo[$_POST['product_id']][3])) {
                            $p->data['tempzone'] = $productInfo[$_POST['product_id']][3];
                        } else {
                            $p->data['tempzone'] = '';
                        }
                        $p->data['PROD_PROFILE.TEMPERATURE_ZONE'] = $p->data['tempzone'] ;
                        if (isset($productInfo[$_POST['product_id']][4])) {
                            $p->data['prodtype'] = $productInfo[$_POST['product_id']][4];
                        } else {
                            $p->data['prodtype'] = '';
                        }
                        $p->data['PROD_PROFILE.PROD_TYPE'] = $p->data['prodtype'] ;
                        /* 20-01-09 add
                           ssn_description as ssn_desc
                           ssn_type as type_1
                           generic as type_2
                           ssn_sub_type as type_3
                           model as model
                           brand as brand
                           serial number as serial
                           print title as title_1
                           print file version as version
                        */
                        $p->data['ssn_desc'] = $ssnInfo['SSN_DESCRIPTION'];
                        $p->data['SSN.SSN_DESCRIPTION'] = $p->data['ssn_desc'] ;
                        //$p->data['type_1'] = $ssnInfo['TYPE_1'];
                        if (isset($productInfo[$_POST['product_id']][5])) {
                            $p->data['type_1'] = $productInfo[$_POST['product_id']][5];
                        } else {
                            $p->data['type_1'] = '';
                        }
                        $p->data['SSN.TYPE_1'] = $p->data['type_1'] ;
                        $p->data['SSN.SSN_TYPE'] = $p->data['type_1'] ;
                        //$p->data['type_2'] = $ssnInfo['TYPE_2'];
                        $p->data['type_2'] = $_POST['variety_id'];
                        $p->data['SSN.TYPE_2'] = $p->data['type_2'] ;
                        $p->data['SSN.GENERIC'] = $p->data['type_2'] ;
                        //$p->data['type_3'] = $ssnInfo['TYPE_3'];
                        $p->data['type_3'] = $_POST['subtype_id'];
                        $p->data['SSN.TYPE_3'] = $p->data['type_3'] ;
                        $p->data['SSN.SSN_SUB_TYPE'] = $p->data['type_3'] ;
                        $p->data['model'] = $ssnInfo['MODEL'];
                        $p->data['SSN.MODEL'] = $p->data['model'] ;
                        //$p->data['brand'] = $ssnInfo['BRAND'];
                        $p->data['brand'] = $_POST['brand_id'];
                        $p->data['SSN.BRAND'] = $p->data['brand'] ;
                        $p->data['serial'] = $ssnInfo['SERIAL_NUMBER'];
                        $p->data['SSN.SERIAL_NUMBER'] = $p->data['serial'] ;
                        $p->data['ISSN.OTHER1'] = "" ;
                        if (isset($_POST['receive_location'] )) {
                            $p->data['ISSN.WH_ID'] = substr($_POST['receive_location'], 0, 2);
                            $p->data['ISSN.LOCN_ID'] = substr($_POST['receive_location'], 2, 10);
                        } else {
                            $p->data['ISSN.WH_ID'] = '';
                            $p->data['ISSN.LOCN_ID'] = '';
                        }
                        $p->data['title_1'] = "";
                        $p->data['version'] = "";
                        $q = getISSNLabel($Link, $p );
                        //echo("\nP:");
                        //var_dump($p);
                        //echo("\nQ:");
                        //var_dump($q);
                        //var_dump($result);
                        //$tpl = file_get_contents('./ISSN-new.prn');
                        $tpl = "";
                        //if (false == ($tpl = file_get_contents($printerDir . 'ISSN-new.fmt'))) {
                        //    $tpl = file_get_contents('./ISSN-new.prn');
                        //}

                        for ($i = 0; $i < $result[1]; $i++) {
                                    //$save = fopen('/data/asset.rf/' . $_POST['printer_id'] .
                                    //              '/' . $p->data['issn'] .
                                    //              '_ISSN.prn', 'w');
                                    $save = fopen($printerDir .
                                                  $p->data['issn'] . '_ISSN.prn', 'w');
                                    if (!$p->sysLabel($Link, $_POST['printer_id'], "ISSN", 1))
                                    {
                                        $p->send($tpl, $save);
                                    }
                                    //$p->data['issn']++;
                                    //cal next issn
                                    //$p->data['issnlabelsuffix']++;
                                    $wk_suffix = "1" . $p->data['issnlabelsuffix'];
                                    $wk_suffix++;
                                    $p->data['issnlabelsuffix'] = substr($wk_suffix,1);
                                    $p->data['issn'] = $p->data['issnlabelprefix'] .  $p->data['issnlabelsuffix']; 
                                    $p->data['ISSN.SSN_ID'] = $p->data['issn'];
                                    fclose($save);
                        }
                        if (false === isset($signalVerify)) {
                            $p->data['issn'] = $result[3];
                            $p->data['ISSN.SSN_ID'] = $p->data['issn'];
                            $p->data['issnlabelprefix'] = substr($result[3],0,2);
                            $p->data['issnlabelsuffix'] = substr($result[3],2,strlen($result[3]) - 2);
                            $p->data['qty'] = $result[5];
                            $p->data['ISSN.CURRENT_QTY'] = $p->data['qty'] ;
                            for ($i = 0; $i < $result[4]; $i++) {
                                //$save = fopen('/data/asset.rf/' . $_POST['printer_id'] .
                                //              '/' . $p->data['issn'] .
                                //              '_ISSN-new.prn', 'w');
                                $save = fopen($printerDir .
                                              $p->data['issn'] . '_ISSN-new.prn', 'w');
                                if (!$p->sysLabel($Link, $_POST['printer_id'], "ISSN", 1))
				{
                                	$p->send($tpl, $save);
				}
                                //$p->data['issn']++;
                                //$p->data['issnlabelsuffix']++;
                                $wk_suffix = "1" . $p->data['issnlabelsuffix'];
                                $wk_suffix++;
                                $p->data['issnlabelsuffix'] = substr($wk_suffix,1);
                                $p->data['issn'] = $p->data['issnlabelprefix'] .  $p->data['issnlabelsuffix']; 
                                $p->data['ISSN.SSN_ID'] = $p->data['issn'];
                                fclose($save);
                            }
                            if ($_POST['complete'] == 'y') {
                                header('Location: /whm/receive/receive_menu.php');
                                echo '<html><head><title>Redirect</title></head><body><p>You are being redirected to <a href="/whm/receive/receive_menu.php">/whm/receive/receive_menu.php"</a></p></body></html>';
                                exit(0);
                            }
                        }
                        showPageVerify($Link, $_POST['real_grn_no'], $_POST['real_order_no'], null);
                    }
                } elseif (count($result) == 1 && $result[0] == 'Processed successfully') {
                            if ($_POST['complete'] == 'y') {
                                header('Location: /whm/receive/receive_menu.php');
                                echo '<html><head><title>Redirect</title></head><body><p>You are being redirected to <a href="/whm/receive/receive_menu.php">/whm/receive/receive_menu.php"</a></p></body></html>';
                                exit(0);
                            }
                        showPageVerify($Link, $_POST['real_grn_no'], $_POST['real_order_no'], null);
                } else {
                    //if (count($result) == 8) {
                    if (count($result) == 11) {
                        //$errors['unknown'] = $result[7];
                        $errors['unknown'] = $result[10];
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
                include 'trans.php';
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
                include 'trans.php';
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
                include 'trans.php';
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
        if ($_POST['lot_no'] == null) {
            header('Location: /whm/receive/receive_menu.php');
            echo '<html><head><title>Redirect</title></head><body><p>You are being redirected to <a href="/whm/receive/receive_menu.php">/whm/receive/receive_menu.php"</a></p></body></html>';
        } else {
            showPageLot($Link);
        } 
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
    list($user, $device) = explode("|", $_COOKIE["LoginUser"]);
    setBDCScookie($Link, $device, "ReceiveProdType" ,'' );
    if (isset($_GET['alttype'])) {
        showPageLot($Link);
    } else {
        showPageDelivery($Link);
    }
}

ibase_close($Link);
