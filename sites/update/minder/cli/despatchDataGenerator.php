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
    . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'tests/acceptance/includes'
    . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'library'
    . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'PEAR');

// Load the config
$defaultConfig = new Zend_Config(array('logging' => array('level' => 7, 'path' => sys_get_temp_dir())), true);
//$defaultConfig->merge(new Zend_Config_Ini(ROOT_DIR . '/minder.ini', null));
// expect in document root something like "/var/sites/sitename/html"
// so the 4th entry is the sitename
    // use previous location for config
$config_file = ROOT_DIR . '/minder.ini';
$defaultConfig->merge(new Zend_Config_Ini($config_file, null));

list($dbHost, $dbAlias) = explode(':', $defaultConfig->database->dsn->main);

$dbConfig = new Zend_Config(array(
    'adapter' => 'Firebird',
    'params' => array(
        'host' => $dbHost,
        'dbname' => $dbAlias,
        'username' => 'SYSDBA',
        'password' => 'masterkey',
        'adapterNamespace' => 'ZendX_Db_Adapter',
        'profiler' => true
    )
));

$file = fopen('/tmp/test.data.sql', 'w');

$connection = new Zend_Test_PHPUnit_Db_Connection(Zend_Db::factory($dbConfig), '');

$insert = new PHPUnit_Extensions_Database_Operation_Composite(array(
    PHPUnit_Extensions_Database_Operation_Factory::DELETE(),
    PHPUnit_Extensions_Database_Operation_Factory::INSERT(),
));

$triggers = array('STOP_DELETE_PICK_ITEM', 'TG_ADD_PICK_ITEM_LINE_NO');


fputs($file, '/*' . PHP_EOL);
fputs($file, ' *ATTENTION!!!!!! ATTENTION!!!!!! ATTENTION!!!!!! ATTENTION!!!!!! ATTENTION!!!!!! ATTENTION!!!!!! ATTENTION!!!!!! ' . PHP_EOL);
fputs($file, ' *' . PHP_EOL);
fputs($file, ' *THIS SCRIPT WILL ERASE ALL DATA FROM PICK_ORDER, PICK_ITEM, PICK_ITEM_DETAIL, ISSN, SSN, PROD_PROFILE TABLES ' . PHP_EOL);
fputs($file, ' *' . PHP_EOL);
fputs($file, ' *ATTENTION!!!!!! ATTENTION!!!!!! ATTENTION!!!!!! ATTENTION!!!!!! ATTENTION!!!!!! ATTENTION!!!!!! ATTENTION!!!!!! ' . PHP_EOL);
fputs($file, ' */' . PHP_EOL);

foreach ($triggers as $triggerName) {
    $connection->getConnection()->query('ALTER TRIGGER ' . $triggerName . ' INACTIVE ');
    fputs($file, 'ALTER TRIGGER ' . $triggerName . ' INACTIVE;' . PHP_EOL);
}

$orderId = 0;
$itemId = 0;
$detailId = 0;
$prodId = 0;
$ssnId = 0;

fputs($file, "delete from PICK_ORDER;" . PHP_EOL);
fputs($file, "delete from PICK_ITEM;" . PHP_EOL);
fputs($file, "delete from PICK_ITEM_DETAIL;" . PHP_EOL);
fputs($file, "delete from ISSN;" . PHP_EOL);
fputs($file, "delete from SSN;" . PHP_EOL);
fputs($file, "delete from PROD_PROFILE;" . PHP_EOL);

$deleteOrder = "delete from PICK_ORDER where pick_order = '%s';";
$insertOrder = "insert into PICK_ORDER (%s) VALUES (%s);";

$deleteItem = "delete from PICK_ITEM where pick_LABEL_NO = '%s';";
$insertItem = "insert into PICK_ITEM (%s) VALUES (%s);";
$insertItemDetail = "insert into PICK_ITEM_DETAIL (%s) VALUES (%s);";
$insert  = "insert into %s (%s) VALUES (%s);";

//$data = array(
//    'PICK_ORDER' => array(),
//    'PICK_ITEM' => array(),
//    'PICK_ITEM_DETAIL' => array(),
//);

$maxOrders = 20900;
$maxItems = ceil($maxOrders * 2.1);

$orderStatuses = array('DA', 'DA', 'DA', 'DA', 'DC', 'OP', 'UC', 'UC', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX');
$lineStatuses = array(
    'UC' => array('UC'),
    'OP' => array('OP'),
    'DC' => array('DC', 'DC', 'DC', 'DC', 'DC', 'DC', 'DC', 'DC', 'DC', 'DC', 'DC', 'DC', 'DC', 'DC', 'DC', 'DC', 'DC', 'DC', 'DC', 'DC', 'DC', 'DC', 'DC', 'DC', 'CN'),
    'DX' => array('DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'DX', 'CN'),
    'DA' => array('AL', 'AL', 'PG', 'PG', 'CN', 'DX', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL', 'PL'),
);
$orderSubTypes = array('EB', 'EB', '');

while ($orderId < $maxOrders) {
    $orderStatus = $orderStatuses[array_rand($orderStatuses)];
    $orderSubType = ($orderStatus == 'DA' ? $orderSubTypes[array_rand($orderSubTypes)] : '');
    $lineStatus  = $lineStatuses[$orderStatus][array_rand($lineStatuses[$orderStatus])];
    $exactLineStatus = ($orderSubType == 'EB') ? 'DX' : $lineStatus;
    $lineNo = 1;
    $averageLines = floor(($maxItems - $itemId) / ($maxOrders - $orderId));
    $maxLines = rand(1, ceil($averageLines * 1.5));
    $parentItemId = '';

    $order = nextOrder($orderId, $orderStatus);
    fputs($file, sprintf($insertOrder, implode(', ', array_keys($order)), "'" . implode("', '", $order) . "'") . PHP_EOL);
//    $deleteOrder->execute(array($order['PICK_ORDER']));
//    $insertOrder->execute(array_values($order));
//    $data['PICK_ORDER'][] = $order;
//    $insert->execute($connection, new ArrayDataSet(array('PICK_ORDER' => array($order))));

    while ($lineNo <= $maxLines) {
        $ssnStatus  = in_array($lineStatus, array('UC', 'OP', 'CN')) ? 'ST' : $exactLineStatus;

        $product = nextProdProfile($prodId);
        $ssn = nextSsn($product, rand(5, 100), $ssnStatus, $ssnId);
        $issn = nextIssn($ssn);
        $item = nextItem($order, $issn, $exactLineStatus, $itemId, $lineNo);
        $exactLineStatus = $lineStatus;

        fputs($file, sprintf($insert, 'PROD_PROFILE', implode(', ', array_keys($product)), "'" . implode("', '", $product) . "'") . PHP_EOL);
        fputs($file, sprintf($insert, 'SSN', implode(', ', array_keys($ssn)), "'" . implode("', '", $ssn) . "'") . PHP_EOL);
        fputs($file, sprintf($insert, 'ISSN', implode(', ', array_keys($issn)), "'" . implode("', '", $issn) . "'") . PHP_EOL);
        fputs($file, sprintf($insertItem, implode(', ', array_keys($item)), "'" . implode("', '", $item) . "'") . PHP_EOL);

        if (in_array($lineStatus, array('PL', 'DC', 'DX', 'PG'))) {
            $itemDetail = nextItemDetail($item, $detailId);
            fputs($file, sprintf($insertItemDetail, implode(', ', array_keys($itemDetail)), "'" . implode("', '", $itemDetail) . "'") . PHP_EOL);
        }

//        $data['PICK_ITEM'][] = $item;
//        $deleteItem->execute(array($item['PICK_LABEL_NO']));
//        $insertItem->execute($item);
//        $insert->execute($connection, new ArrayDataSet(array('PICK_ITEM' => array($item))));

//        $data['PICK_ITEM_DETAIL'][] = $itemDetail;
//        $insert->execute($connection, new ArrayDataSet(array('PICK_ITEM_DETAIL' => array($itemDetail))));
        echo '.';
    }

    echo ($orderId - 1) . PHP_EOL;
}

//$insert->execute($connection, new ArrayDataSet($data));

foreach ($triggers as $triggerName) {
    $connection->getConnection()->query('ALTER TRIGGER ' . $triggerName . ' ACTIVE ');
    fputs($file, 'ALTER TRIGGER ' . $triggerName . ' ACTIVE;' . PHP_EOL);
}

$connection->getConnection()->commit();

fclose($file);

function nextOrder(&$id, $status) {
    $carriers = array('UGLY', 'CPLEASE', 'COURIERP', 'OTHERCARR', 'CPLEASE', 'EPARCEL', 'TOLL');

    return array(
        'PICK_ORDER' => 'S' . str_pad($id++, 9, '0', STR_PAD_LEFT),
        'PICK_ORDER_TYPE' => 'SO',
        'COMPANY_ID' => 'PINPOINT',
        'SHIP_VIA' => $carriers[array_rand($carriers)],
        'PICK_STATUS' => $status,
        'PARTIAL_PICK_ALLOWED' => 'T',
        'WH_ID' => 'RZ',
    );
}

function nextProdProfile(&$id) {
    $prodId = str_pad($id++, 10, '0', STR_PAD_LEFT);

    return array(
        'PROD_ID' => 'TP' . $prodId,
        'COMPANY_ID' => 'PINPOINT',
        'SHORT_DESC' => 'Test Product ' . $prodId,
    );
}

function nextSsn($prodProfile, $qty, $status, &$id) {
    return array(
        'SSN_ID' => str_pad($id++, 20, '0', STR_PAD_LEFT),
        'PROD_ID' => $prodProfile['PROD_ID'],
        'COMPANY_ID' => $prodProfile['COMPANY_ID'],
        'ORIGINAL_QTY' => $qty,
        'CURRENT_QTY' => $qty,
        'STATUS_SSN' => $status,
    );
}

function nextIssn($ssn) {
    return array(
        'SSN_ID' => $ssn['SSN_ID'],
        'ORIGINAL_SSN' => $ssn['SSN_ID'],
        'PROD_ID' => $ssn['PROD_ID'],
        'COMPANY_ID' => $ssn['COMPANY_ID'],
        'ORIGINAL_QTY' => $ssn['ORIGINAL_QTY'],
        'CURRENT_QTY' => $ssn['CURRENT_QTY'],
        'ISSN_STATUS' => $ssn['STATUS_SSN'],
    );
}

function nextItem($order, $issn, $lineStatus, &$id, &$lineNo) {
    return array(
        'PICK_LABEL_NO' => 'D' . str_pad($id++, 6, '0', STR_PAD_LEFT),
        'PICK_ORDER' => $order['PICK_ORDER'],
        'PICK_ORDER_LINE_NO' => str_pad($lineNo++, 4, '0', STR_PAD_LEFT),
        'PICK_ORDER_QTY' => $issn['CURRENT_QTY'],
        'PICKED_QTY' => $issn['CURRENT_QTY'],
        'PICK_LINE_STATUS' => $lineStatus,
        'WH_ID' => $order['WH_ID'],
        'PROD_ID' => $issn['PROD_ID'],
        'SSN_ID' => $issn['SSN_ID'],
    );
}

function nextItemDetail($item, &$id) {
    return array(
        'PICK_DETAIL_ID' => $id++,
        'PICK_LABEL_NO' => $item['PICK_LABEL_NO'],
        'PICK_ORDER' => $item['PICK_ORDER'],
        'QTY_PICKED' => ($item['PICK_LINE_STATUS'] == 'PG' ?  $item['PICKED_QTY'] - 2 : $item['PICKED_QTY']),
        'PICK_DETAIL_STATUS' => $item['PICK_LINE_STATUS'],
        'SSN_ID' => $item['SSN_ID'],
    );
}