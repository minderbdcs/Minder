<?php
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
$config = new Zend_Config_Ini($config_file , null);
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
if (is_writable(ROOT_DIR . '/log')) {
    $writer = new Zend_Log_Writer_Stream(ROOT_DIR . '/log/' . date('Ymd') . '.log');
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


//    'get|g=i'     => 'Get item by internalId and transaction type',
//    'getAll|a=w'  => 'Get all items by type',
//    'getList|l=w' => 'Get list of transactions by type',

$param = array(
    'erase|e'     => 'Remove all records from local cache',
    'refresh|r'   => 'Refresh local cache of NetSuite tables',
    'update|u'    => 'Retrieve new transactions from NetSuite every N seconds',
    'commit|c'    => 'Commit local changes to NetSuite',
    'root|p'    => 'Root Path to use'
);

echo 'GO main try/catch block ' . PHP_EOL;

try
{
    $c = new Zend_Console_Getopt($param);
    $l = $c->parse();

    // SOAP login DEFAULT data
    $SoapPassport = new stdClass();
    $SoapPassport->email    = 'fpgminder@barcoding.com.au'; //'glenn@barcoding.com.au' ; //'artyom.goncharov@binary-studio.com';
    $SoapPassport->password = 'fpgminder1';
    $SoapPassport->role     = 'Administrator';
    $SoapPassport->account  = '823303';//'32147';

    echo 'parse remain args' . PHP_EOL;
    
    $args = $c->getRemainingArgs();
    foreach ($args as $val) {
        if (false !== strpos($val, '=')) {
            list($key, $val) = explode('=', $val, 2);
            switch (strtolower($key)) {
                case 'userid':
                    $userId    = substr($val, 0, 10); 
                    break;
                case 'deviceid':
                    $deviceId  = substr($val, 0, 2);
                    break;
                case 'email':
                    $SoapPassport->email = $val;
                    break; 
                case 'password':
                    $SoapPassport->password = $val;
                    break;
                case 'role' :
                    $SoapPassport->role = $val;
                    break;
                case 'account':
                    $SoapPassport->account = $val;
                    break;
                default:
                    break;
            }
        } else {
            //var_dump($val);
        }
    }
    if (!isset($userId)) {
        echo 'userid required! e.g. soap-cli.php userid=someuser' . PHP_EOL;
        die();
    } elseif (!isset($deviceId)) {
        echo 'deviceid required! e.g. soap-cli.php deviceid=somedevice' . PHP_EOL;
        die();
    }
} catch (Zend_Console_Getopt_Exception $e) {
    echo 'ERROR occurs' . PHP_EOL;
    echo $e->getUsageMessage();
    exit;
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
    
    echo 'check is allowed CLI.... ';    
    if (!$minder->isAllowedCli()) {
        die('not allowed' . PHP_EOL);
    } else {
        echo 'allowed' . PHP_EOL;
    }
    
// instantiate     
echo 'Initialise our SoapWrapper instance' . PHP_EOL;
$s = new NetSuite_SoapWrapper($SoapPassport);
$s->Passport = $SoapPassport;
// initialize Synchronizer
echo 'initialize Synchronizer' . PHP_EOL;
$syn = new NetSuite_Synchronizer($s, $userId, $deviceId);

if (isset($c->g)) {
    $action = 'get';
    $arg    = $c->g;
    $type   = $c->getRemainingArgs();
    $type   = $type[0];
    $res    = $syn->get($type, $arg);
} else if (isset($c->a)) {
    $action = 'getAll';
    $arg    = $c->a;
} else if (isset($c->e)) {
    $action = 'erase';
    echo 'Erase cache started.' . date('Y-m-d H:i:s', time()) . PHP_EOL;
    $syn->clearSoapCache();
    echo 'Erase cache completed. ' . date('Y-m-d H:i:s', time()) . PHP_EOL;
} else if (isset($c->l)) {
    $action = 'getList';
    $arg    = $c->l;
} else if (isset($c->r)) {
    $action = 'refresh';
    $arg    = true;
    
    if(true == $s->lockSoapTransaction()) {
        echo 'SOAP requests locked ' . PHP_EOL;
        
        if (false === ($res = $syn->refresh())) {
            echo 'Refresh failed.' . PHP_EOL;
        }
        
        if(true == $s->unlockSoapTransaction()) {
            echo 'SOAP requests is unlocked ' . PHP_EOL;
        } else {
                echo 'Impossible unlock SOAP requests ' . PHP_EOL;     
               } 
    } else {
        $res['toUpdate'] = 0;
        $res['updated'] =0;
        echo 'Impossible to lock SOAP requests ' . PHP_EOL;    
    }
} else if (isset($c->u)) {
    $action = 'update';
    $arg    = $c->u;
    echo 'Update started.' . date('Y-m-d H:i:s', time()) . PHP_EOL;
    try {
            if(true == $s->lockSoapTransaction()) {
                echo 'SOAP requests locked ' . PHP_EOL;
                $res    = $syn->update();
                if(true == $s->unlockSoapTransaction()) {
                    echo 'SOAP requests is unlocked ' . PHP_EOL;
                } else {
                        echo 'Impossible unlock SOAP requests ' . PHP_EOL;     
                       } 
            } else {
                $res['toUpdate'] = 0;
                $res['updated'] =0;
                echo 'Impossible to block SOAP requests ' . PHP_EOL;    
            }
    } catch (Exception $e) {
        echo $e->getLine() . ': ' . $e->getCode() . ': ' . $e->getMessage();
        echo $e->getTraceAsString();
        die();
    }
    echo 'Update completed. ' . date('Y-m-d H:i:s', time()) .' - record to update ' . $res['toUpdate'] . ' record updated ' . $res['updated']. PHP_EOL;
} else if (isset($c->c)) {
    $action = 'commit';
    $arg    = $c->c;
    if ($s->login()) {
        echo 'Commit started.' . date('Y-m-d H:i:s', time()) . PHP_EOL;
        
         if(true == $s->lockSoapTransaction()) {
                echo 'SOAP requests locked ' . PHP_EOL;
                
                echo 'Update Sales Orders ' . date('Y-m-d H:i:s', time()) . PHP_EOL;
                    //$result = $s->updateSalesOrder();
                    $result = $s->sendSalesOrdersByOrder();
                echo 'Update InventoryAdjustments ' . date('Y-m-d H:i:s', time()) . PHP_EOL;
                    $result = $s->inventoryAdjustments();
                echo 'Update Receives ' . date('Y-m-d H:i:s', time()) . PHP_EOL;
                    $result = $s->sendItemReceiptsByOrder();
                        
                if(true == $s->unlockSoapTransaction()) {
                    echo 'SOAP requests is unlocked ' . PHP_EOL;
                } else {
                        echo 'Impossible unlock SOAP requests ' . PHP_EOL;     
                       } 
            } else {
                $res['toUpdate'] = 0;
                $res['updated'] =0;
                echo 'Impossible to block SOAP requests ' . PHP_EOL;    
            }
      
        echo 'Commit completed. ' . date('Y-m-d H:i:s', time()) . PHP_EOL;
    } else {
        echo 'Can\'t login to NetSuite' . PHP_EOL;
    }
} else {
    //echo $c->getUsageMessage();
}

echo date('Y-m-d H:i:s', time()) . 'END CLI' . PHP_EOL;
