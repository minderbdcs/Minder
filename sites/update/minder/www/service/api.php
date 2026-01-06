<?php
// Setup the environment and includes path
define('ROOT_DIR', realpath('../..'));
set_include_path(get_include_path()
    . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'includes'
    . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'library'
    . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'PEAR');

// Load the config

require ROOT_DIR . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

spl_autoload_register(function($className) {
    include implode('/', explode('_', $className)) . '.php';
});

$dice = require 'diceBootstrap.php';

// expect in document root something like "/var/sites/sitename/html"
// so the 4th entry is the sitename
$mdrDocRoot = explode("/" , $_SERVER['DOCUMENT_ROOT']);
$mdrSitename = $mdrDocRoot[3];
$config_file = "/etc/minder/" . $mdrSitename . "/minder.ini";
if(file_exists($config_file) ) {
    $mdrExists = True;
    //echo "$config_file found";
} else {
    // use previous location for config
    $config_file = ROOT_DIR . '/minder.ini';
}

$config = new Zend_Config_Ini($config_file, null);
Zend_Registry::set('config', $config);
date_default_timezone_set($config->date->timezone);
// Start the session
Zend_Session::start(array('strict' => true, 'remember_me_seconds' => 86400));
$session = new Zend_Session_Namespace();

// Set up the log writer
$format = '%timestamp% %priorityName% (%priority%) %user_id% %device_id% %ip_addr% %request_uuid%: %message%' . PHP_EOL;
$formatter = new Zend_Log_Formatter_Simple($format);
if (is_writable('/tmp')) {
//    $writer = new Zend_Log_Writer_Stream(ROOT_DIR . '/log/' . date('Ymd') . '.log');
    $writer = new Zend_Log_Writer_Stream('/tmp/' . date('Ymd') . '.log');
    $writer->setFormatter($formatter);
    $writer->addFilter(new Zend_Log_Filter_Priority((int)$config->logging->level));
} else {
    $writer = new Zend_Log_Writer_Null();
}
$logger = new Minder_Log();
$logger->setEventItem('request_uuid', uniqid('', true));
$logger->addWriter($writer);
//$writer = new Zend_Log_Writer_Firebug();
//$writer->addFilter(new Zend_Log_Filter_Priority(Zend_Log::INFO, '!='));
//$logger->addWriter($writer);
Zend_Registry::set('logger', $logger);
$log = $logger->startDetailedLog();
$log->starting('Minder application v' . Minder_Version::getFull() . ' api request.');

// configure DSN
if ($config->database->dsn->main) {
    Minder::$dbLiveDsn = $config->database->dsn->main;
}
if ($config->database->dsn->user) {
    Minder::$dbUser = $config->database->dsn->user;
}
if ($config->database->dsn->password) {
    Minder::$dbPass = $config->database->dsn->password;
}
if (isset($session->dbLiveDsn)) {
    Minder::$dbLiveDsn = $session->dbLiveDsn;
    Minder::$dbName    = $session->dbName;
}

// Initialise our Minder instance
$minder = Minder::getInstance();
if (isset($session->userId)) {
    $LogFile = ROOT_DIR . "/log/login.log";
    $minder->userId         = $session->userId;
    //$minder->ip             = $_SERVER['REMOTE_ADDR'];
    $minder->ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
    $wkMessage =  "LOGIN MDR Instance user:" . $session->userId ;
    $wkMessage .= isset($_SERVER['HTTP_X_FORWARDED_FOR']) ?  " http_x_forwarded_for " . $_SERVER['HTTP_X_FORWARDED_FOR'] : "";
    $wkMessage .=  " remote_addr: " . $_SERVER['REMOTE_ADDR']  ;
    //file_put_contents($LogFile, $wkMessage . "\n", FILE_APPEND );
    $minder->deviceId       = $session->deviceId;
    $wkMessage .=  " device: " . $session->deviceId  ;
    $wkMessage .= " " . date("M, d-M-Y H:i:s.u");
    //file_put_contents($LogFile, $wkMessage . "\n", FILE_APPEND );
    $minder->isAdmin        = $session->isAdmin;
    $minder->isInventoryOperator = $session->isInventoryOperator;
    $minder->isEditable     = $session->isEditable;
    $minder->isStockAdjust  = $session->isStockAdjust;
    $minder->limitCompany   = isset($session->limitCompany) ? $session->limitCompany : 'all';
    $minder->limitWarehouse = isset($session->limitWarehouse) ? $session->limitWarehouse : 'all';
    $minder->limitPrinter   = isset($session->limitPrinter) ? $session->limitPrinter : $minder->limitPrinter;
    $logger->setEventItem('user_id', $minder->userId);
    $logger->setEventItem('device_id', $minder->deviceId);
    $logger->setEventItem('ip_addr', $minder->ip);
} else {
    $logger->setEventItem('user_id', 'anonymous');
    $logger->setEventItem('device_id', '-');
    //$logger->setEventItem('ip_addr', $_SERVER['REMOTE_ADDR']);
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
    {
        $logger->setEventItem('ip_addr', $_SERVER['HTTP_X_FORWARDED_FOR']);
    } else {
        $logger->setEventItem('ip_addr', $_SERVER['REMOTE_ADDR']);
    }
}

if(!isset($config->company->licensee)) {
    Zend_Registry::set('licensee', null);
} else {
    Zend_Registry::set('licensee', $config->company->licensee);
}

Zend_Db_Table::setDefaultAdapter(Minder::getDefaultDbAdapter());
$api = $dice->create(\MinderNG\PageMicrocode\JsonRpc\Api::CLASS_NAME);
$server = new \MinderNG\JsonRPC\Server();
$server->attach($api);
$server->attachException();

$result = $server->execute();

if (isset($result['result'])) {
    $result['result'] = ($result['result'] instanceof \MinderNG\JsonSerializableInterface) ? $result['result']->jsonSerialize() : $result['result'];
}

echo json_encode($result);


$diCache = var_export($dice->getManager()->getData(), true);

$log->done();