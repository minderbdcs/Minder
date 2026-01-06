<?php

spl_autoload_register(function($className)
{
    include implode('/', explode('_', $className)) . '.php';
});

include(__DIR__ . '/../vendor/autoload.php');

// Setup the environment and includes path
define('ROOT_DIR', realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..'));

set_include_path(get_include_path()
    . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'includes'
    . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array('tests', 'acceptance', 'includes'))
    . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'library'
    . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'PEAR');

$data = array(
    'PICK_ITEM_DETAIL' => array(),
    'PICK_ITEM' => array(),
    'PACK_SSCC' => array(),
    'SSN' => array(),
    'ISSN' => array(),
);

$id = 9999;

$pid = array(
    "PICK_DETAIL_ID" => 444,
    "PICK_LABEL_NO" => "D999999",
    "SSN_ID" => 10001130,
    "PICK_DETAIL_STATUS" => "PL",
    "QTY_PICKED" => 3,
    "DESPATCH_LOCATION" => "BLOCK888",
    "USER_ID" => "BDCS",
    "DEVICE_ID" => "C1",
    "CREATE_DATE" => "2012-12-13 09:45:24",
    "FROM_WH_ID" => "RZ",
    "FROM_LOCN_ID" => "RC010000",
    "LAST_UPDATE_DATE" => "2012-12-13 09:45:24",
    "PID_LEGACY_TRY" => 0,
    "PICK_ORDER" => "S72162698L997",
    "PS_DEL_TO_DC_IN_HOUSE_NO" => 99,
    "PS_DEL_TO_STORE_IN_HOUSE_NO" => 99,
);

$pi = array(
    "PICK_LABEL_NO" => "D999999",
    "PICK_ORDER" => "S72162698L997",
    "PICK_ORDER_LINE_NO" => 1,
    "PROD_ID" => "718408",
    "SSN_ID" => 10001130,
    "WARRANTY_TERM" => "365RTB",
    "PICK_ORDER_QTY" => 3,
    "PICKED_QTY" => 3,
    "PICK_LINE_DUE_DATE" => "2012-12-21 00:00:00",
    "DESPATCH_LOCATION" => "BLOCK888",
    "CREATE_DATE" => "2012-12-13 09:45:16",
    "USER_ID" => "BDCS",
    "DEVICE_ID" => "C1",
    "PICK_LINE_STATUS" => "PL",
    "SALE_PRICE" => 0,
    "DISCOUNT" => 0,
    "SSN_CONFIRM" => "I",
    "WIP_PRELOCN_ORDERING" => "~",
    "WIP_POSTLOCN_ORDERING" => "718408",
    "PICK_RETRIEVE_STATUS" => "F",
    "DESPATCH_LOCATION_GROUP" => "T1",
    "LAST_UPDATE_DATE" => "2013-07-26 12:00:08",
    "LINE_TOTAL" => 0,
    "TAX_AMOUNT" => 0,
    "TAX_RATE" => 10,
    "OVER_SIZED" => "T",
    "PI_LEGACY_CLOSED" => "F",
    "EXPORTED_DESPATCH" => "F",
    "LINE_COST_AMOUNT" => 0,
    "WH_ID" => "RZ",
    "PS_DEL_TO_DC_IN_HOUSE_NO" => 99,
    "PS_DEL_TO_STORE_IN_HOUSE_NO" => 99,
);

$packSscc = array(
    "RECORD_ID" => 1,
    "PS_PICK_ORDER" => "S72162698L997",
    "PS_PICK_ORDER_LINE_NO" => 1,
    "PS_PICK_LABEL_NO" => "D999999",
    "PS_DESPATCH_ID" => 192,
    "PS_SSCC" => "00120000000000000012",
    "PS_SSCC_STATUS" => "GO",
    "PS_CARRIER_ID" => "EPARCEL",
    "PS_AWB_CONSIGNMENT_NO" => "GOTEST",
    "PS_QTY_ORDERED" => 3,
    "PS_OUT_SSCC" => "00120000000000000012",
    "PS_DEL_TO_DC_NO" => 99,
    "PS_DEL_TO_STORE_NO" => 99,
    "PS_DEL_TO_DC_IN_HOUSE_NO" => 99,
    "PS_DEL_TO_STORE_IN_HOUSE_NO" => 99,
    "PS_QTY_SHIPPED" => null,
    "PS_SSCC_WEIGHT" => null,
    "PS_SSCC_WEIGHT_UOM" => null,
    "PS_SSCC_DIM_X" => null,
    "PS_SSCC_DIM_Y" => null,
    "PS_SSCC_DIM_Z" => null,
    "PS_SSCC_DIM_UOM" => null,
    "PS_PACK_TYPE" => null,
);

$ssn = array(
    "SSN_ID" => 10001130,
    "WH_ID" => "RZ",
    "LOCN_ID" => "BLOCK888",
    "SSN_DESCRIPTION" => "Product - Consumable Packaging Material",
    "SSN_TYPE" => "PRODUCT PACKAGING",
    "COMPANY_ID" => "TIFS",
    "ORIGINAL_QTY" => 3,
    "CURRENT_QTY" => 3,
    "CREATED_BY" => "Admin",
    "STATUS_SSN" => "ST",
    "AUDITED" => "T",
    "AUDITED_QTY" => 3,
    "LEASED" => "F",
    "DISPOSED" => "F",
    "SSN_TAX_APPLICABLE" => "F",
    "LOAN_SAFETY_PASS" => "F",
    "LOAN_CALIBRATE_PASS" => "F",
);

$issn = array(
    "SSN_ID" => 10001130,
    "ORIGINAL_SSN" => 10001130,
    "PROD_ID" => "718408",
    "WH_ID" => "RZ",
    "LOCN_ID" => "BLOCK888",
    "CURRENT_QTY" => 3,
    "ISSN_STATUS" => "ST",
    "AUDITED" => "T",
    "COMPANY_ID" => "TIFS",
);

/**
 * @param $total
 * @param $id
 * @param $pid
 * @param $pi
 * @param $packSscc
 * @param $ssn
 * @param $issn
 * @param $data
 * @return mixed
 */
function genDatag($total, &$id, $pid, $pi, $packSscc, $ssn, $issn, $data)
{
    while ($total-- > 0) {
        $pid['PICK_DETAIL_ID'] = $id;
        $pid['PICK_LABEL_NO'] = 'D88' . $id;
        $pid['SSN_ID'] = '9999' . $id;

        $pi['PICK_LABEL_NO'] = $pid['PICK_LABEL_NO'];
        $pi['PICK_ORDER_LINE_NO'] = $pi['PICK_ORDER_LINE_NO']++;
        $pi['SSN_ID'] = $pid['SSN_ID'];

        $packSscc['RECORD_ID'] = $id;
        $packSscc['PS_PICK_ORDER_LINE_NO'] = $pi['PICK_ORDER_LINE_NO'];
        $packSscc['PS_PICK_LABEL_NO'] = $pi['PICK_LABEL_NO'];
        $packSscc['PS_SSCC'] = "0099000000000000" . $id;
        $packSscc['PS_OUT_SSCC'] = $packSscc['PS_SSCC'];

        $ssn['SSN_ID'] = $pid['SSN_ID'];

        $issn['SSN_ID'] = $pid['SSN_ID'];
        $issn['ORIGINAL_SSN'] = $pid['SSN_ID'];

        $data['PICK_ITEM_DETAIL'][] = $pid;
        $data['PICK_ITEM'][] = $pi;
        $data['PACK_SSCC'][] = $packSscc;
        $data['SSN'][] = $ssn;
        $data['ISSN'][] = $issn;

        $id--;
    }
    return $data;
}

$data = genData(100, $id, $pid, $pi, $packSscc, $ssn, $issn, $data);

$packSscc['PS_SSCC_STATUS'] = 'CL';
$packSscc['PS_QTY_SHIPPED'] = 3;
$packSscc['PS_SSCC_WEIGHT'] = 5;
$packSscc['PS_SSCC_WEIGHT_UOM'] = 'KG';
$packSscc['PS_SSCC_DIM_X'] = 100;
$packSscc['PS_SSCC_DIM_Y'] = 100;
$packSscc['PS_SSCC_DIM_Z'] = 100;
$packSscc['PS_SSCC_DIM_UOM'] = 'CM';
$packSscc['PS_PACK_TYPE'] = 'C';

$data = genData(5000, $id, $pid, $pi, $packSscc, $ssn, $issn, $data);

PHPUnit_Extensions_Database_DataSet_YamlDataSet ::write(new ArrayDataSet($data), './test-data.yml');