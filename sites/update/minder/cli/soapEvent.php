<?php
// set no bufferred output
@ob_end_flush();
ob_implicit_flush(TRUE);
// set no time limit
set_time_limit(0);
// time limit talking to soap server
ini_set('default_socket_timeout', 20);

echo date('Y-m-d H:i:s', time()) . 'START CLI' . PHP_EOL;
set_error_handler('errHandler');
error_reporting(E_ALL);

// Setup the environment and includes path
// ROOT_DIR should be a Minder dir. e.g. /var/sites/nightly.barcoding.com.au/minder

//define('ROOT_DIR', realpath('..'));
//define('ROOT_DIR', '/var/sites/nightly.barcoding.com.au/minder'); 
//define('ROOT_DIR', '/var/sites/fpg.barcoding.com.au/minder'); 

$mykey = realpath('..');
define('ROOT_DIR', $mykey); 
//define('LOG_DIR', '/tmp'); 
define('LOG_DIR', DIRECTORY_SEPARATOR . 'tmp'); 

set_include_path(get_include_path()
    . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'includes'
    . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'library'
    . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'PEAR');


function errHandler($errno, $errstr, $errfile, $errline) {
    echo date('Y-m-d H:i:s', time()) . ': ' . $errno . ' ' . $errstr . PHP_EOL . ' ' . $errfile . ' ' . $errline . PHP_EOL;
    return true;
}

function __autoload($className)
{
    include implode(DIRECTORY_SEPARATOR, explode('_', $className)) . '.php';
}
echo 'Load the config' . PHP_EOL;
// Load the config
//$config = new Zend_Config_Ini(ROOT_DIR . '/minder.ini', null);
//$config = new Zend_Config_Ini(ROOT_DIR . DIRECTORY_SEPARATOR .  'minder.ini', null);
// expect in document root something like "/var/sites/sitename/html"
// so the 4th entry is the sitename
$mdrDocRoot = explode("/" , $_SERVER['DOCUMENT_ROOT']);
$mdrSitename = $mdrDocRoot[3];
$config_file = "/etc/minder/" . $mdrSitename . "/minder.ini";
$config_file = DIRECTORY_SEPARATOR . "etc" .  DIRECTORY_SEPARATOR . "minder" . DIRECTORY_SEPARATOR . $mdrSitename . DIRECTORY_SEPARATOR .  'minder.ini' ;
if(file_exists($config_file) ) {
    $mdrExists = True;
    //echo "$config_file found";
} else {
    // use previous location for config
    $config_file = ROOT_DIR . '/minder.ini';
    $config_file = ROOT_DIR . DIRECTORY_SEPARATOR .  'minder.ini' ;
}
$config = new Zend_Config_Ini($config_file, null);
Zend_Registry::set('config', $config);
date_default_timezone_set($config->date->timezone);

echo 'Start the session' . PHP_EOL;


try {
// Start the session
//Zend_Session::start(array('strict' => true, 'remember_me_seconds' => 86400));
//$session = new Zend_Session_Namespace();
} catch (Exception $e) {
//    echo $e->getMessage() . PHP_EOL;
}

// Set up the log writer
echo 'Set up the log writer' . PHP_EOL;
$format = '%timestamp% %priorityName% (%priority%) %user_id% %device_id% %ip_addr%: %message%' . PHP_EOL;
$formatter = new Zend_Log_Formatter_Simple($format);
if (is_writable(LOG_DIR  )) {
    //$writer = new Zend_Log_Writer_Stream(LOG_DIR . '/soap' . date('Ymd') . '.log');
    $writer = new Zend_Log_Writer_Stream(LOG_DIR . DIRECTORY_SEPARATOR . 'soap' . date('Ymd') . '.log');
    $writer->setFormatter($formatter);
    $writer->addFilter(new Zend_Log_Filter_Priority((int)$config->logging->level));
} else {
    $writer = new Zend_Log_Writer_Null();
}
$logger = new Zend_Log();
$logger->addWriter($writer);
Zend_Registry::set('logger', $logger);

echo 'configure DSN' . PHP_EOL;
// configure DSN
if ($config->database->dsn->main) {
    Minder::$dbLiveDsn = $config->database->dsn->main;
}
if ($config->database->dsn->test) {
    Minder::$dbTrainingDsn = $config->database->dsn->test;
}
if ($config->database->dsn->user) {
    Minder::$dbUser = $config->database->dsn->user; 
}
if ($config->database->dsn->password) {
    Minder::$dbPass = $config->database->dsn->password; 
}

echo 'Initialise our Minder instance' . PHP_EOL;
// Initialise our Minder instance
    $minder = Minder::getInstance();
    $logger->setEventItem('user_id', $minder->userId);
    $logger->setEventItem('device_id', $minder->deviceId);
    $logger->setEventItem('ip_addr', $minder->ip);
    $minder->isInventoryOperator = isset($session->isInventoryOperator) ? $session->isInventoryOperator: true;
    $minder->limitCompany = isset($session->limitCompany) ? $session->limitCompany : 'all';
    $minder->limitWarehouse = isset($session->limitWarehouse) ? $session->limitWarehouse : 'all';
    $minder->limitPrinter = isset($session->limitPrinter) ? $session->limitPrinter : $minder->limitPrinter;
    $minder->whId = $minder->defaultControlValues['DEFAULT_WH_ID'];
    
$userId = "BDCS";
$deviceId = "XY";
// initialize Synchronizer
echo 'initialize Synchronizer' . PHP_EOL;
$syn = new Casper_SoapWrapper( $userId, $deviceId);

$syn->getSoapClient();

/*
at startup dont wait for event just process waiting 'WS' and 'WK' status WEB_REQUESTS
*/
{
    echo date('Y-m-d H:i:s', time()) . 'Startup Process Waiting transactions' . PHP_EOL;
    /* update the records in web_requests that are status 'WS' to 'WK'  */
    $minder->updateWebRequestsStatus( 'WS', 'WK' );
    /* now process the waiting 'WK' status records */
    $syn->getWKRequest();
}
/* 
register and wait for db event 'MESSAGE_READY_TOGO'
*/

$doMore = True;
while ($syn->waitForData()) {
    echo date('Y-m-d H:i:s', time()) . 'Got Wakeup' . PHP_EOL;
    /* have to do a commit to get the current set of records */
    /* update the records in web_requests that are status 'WS' to 'WK' then can goback to wait for more */
    $doMore = True;
    while ($doMore) {
    	$minder->updateWebRequestsStatus( 'WS', 'WK' );
    	/* now process the waiting 'WK' status records */
    	$doMore = $syn->getWKRequest();
    }
}

// to get here must have got a shutdown message

/* 
then while retrieving web_requests of status 'WS'

change the status to 'WK'
add to started count

read the transaction type that this is for
then from the transaction type calc the soap member function to use
do the process  to send that message id 

*/

echo date('Y-m-d H:i:s', time()) . 'END CLI' . PHP_EOL;
