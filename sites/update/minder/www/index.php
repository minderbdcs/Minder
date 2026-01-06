<?php
/**
 * Minder bootstrap
 *
 * This script will bootstrap the Minder application
 *
 * PHP version 5
 *
 * @category  Minder
 * @package   Bootstrap
 * @author    Richard Buggy <rich@zoombug.com.au>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html BDCS License
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 *
 */

// If the site is closed then display the closed message and exit.
if (file_exists('site-closed.html')) {
    include 'site-closed.html';
    exit(0);
}

// Setup the environment and includes path
define('ROOT_DIR', realpath('..'));
set_include_path(get_include_path()
    . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'includes'
    . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'library'
    . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'PEAR'
    . PATH_SEPARATOR . DIRECTORY_SEPARATOR . 'usr' . DIRECTORY_SEPARATOR . 'share' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PHPMailer');

// Load the config

require ROOT_DIR . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

spl_autoload_register(function($className) {
    include implode('/', explode('_', $className)) . '.php';
});

Zend_Registry::set('dice', require 'diceBootstrap.php');

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
if (is_writable('/data/tmp')) {
    putenv('TMPDIR=/data/tmp'); 
    $writer = new Zend_Log_Writer_Stream('/data/tmp/' . date('Ymd') . '.log');
    $writer->setFormatter($formatter);
    $writer->addFilter(new Zend_Log_Filter_Priority((int)$config->logging->level));
} else {
    $writer = new Zend_Log_Writer_Null();
}
$logger = new Minder_Log();
$logger->setEventItem('request_uuid', uniqid('', true));
$logger->addWriter($writer);
Zend_Registry::set('logger', $logger);
$log = $logger->startDetailedLog();
$log->starting('Minder application v' . Minder_Version::getFull() . '.');

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

// configure SOAP Passport
    $SoapPassport           = new stdClass();
    if (!isset($config->soap->passport) || null == $config->soap->passport) {
        $SoapPassport->email    = 'fpgminder@barcoding.com.au';
        $SoapPassport->password = 'fpgminder1';
        $SoapPassport->role     = 'Administrator';
        $SoapPassport->account  = '823303';
    } else {
        $SoapPassport->email    = $config->soap->passport->email;
        $SoapPassport->password = $config->soap->passport->password;
        $SoapPassport->role     = $config->soap->passport->role;
        $SoapPassport->account  = $config->soap->passport->account;
    }
Zend_Registry::set('SoapPassport', $SoapPassport);

// Initialise our Minder instance
$minder = Minder::getInstance();
if (isset($session->userId)) {
    $LogFile = "/data/tmp/login.log";
    $minder->userId         = $session->userId;
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
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
    {
        $logger->setEventItem('ip_addr', $_SERVER['HTTP_X_FORWARDED_FOR']);
    } else {
        $logger->setEventItem('ip_addr', $_SERVER['REMOTE_ADDR']);
    }
}

// Create the front controller and add the modules from the module directory
$front = Zend_Controller_Front::getInstance();
$front->registerPlugin(new Minder_Controller_Plugin_RequestLog());
$front->addModuleDirectory(ROOT_DIR . '/application/');

$front->throwExceptions(true);

$front->addControllerDirectory(ROOT_DIR . '/application/admin2/' . $config->company->licensee . '/controllers', 'admin2');
$front->addControllerDirectory(ROOT_DIR . '/application/default/' . $config->company->licensee . '/controllers', 'default');
$front->addControllerDirectory(ROOT_DIR . '/application/despatches/' . $config->company->licensee . '/controllers', 'despatches');
$front->addControllerDirectory(ROOT_DIR . '/application/install/' . $config->company->licensee . '/controllers', 'install');
$front->addControllerDirectory(ROOT_DIR . '/application/orders/' . $config->company->licensee . '/controllers', 'orders');
$front->addControllerDirectory(ROOT_DIR . '/application/pages/' . $config->company->licensee . '/controllers', 'pages');
$front->addControllerDirectory(ROOT_DIR . '/application/picking/' . $config->company->licensee . '/controllers', 'picking');
$front->addControllerDirectory(ROOT_DIR . '/application/receipts/' . $config->company->licensee . '/controllers', 'receipts');
$front->addControllerDirectory(ROOT_DIR . '/application/services/' . $config->company->licensee . '/controllers', 'services');
$front->addControllerDirectory(ROOT_DIR . '/application/transfer2/' . $config->company->licensee . '/controllers', 'transfer2');
$front->addControllerDirectory(ROOT_DIR . '/application/warehouse/' . $config->company->licensee . '/controllers', 'warehouse');

define('APPLICATION_CONFIG_DIR' , ROOT_DIR . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . $config->company->licensee);

//add routes
$route = new Zend_Controller_Router_Route('page/:menuId', array(
        'controller' => 'page',
        'action' => 'show'
                                                          ));
$front->getRouter()->addRoute('minderPage', $route);
$route = new Zend_Controller_Router_Route('page/page-service/:menuId', array(
        'controller' => 'page',
        'action' => 'page-service'
                                                          ));
$front->getRouter()->addRoute('pageService', $route);
$route = new Zend_Controller_Router_Route('page/screen-service/:menuId/:screenId', array(
        'controller' => 'page',
        'action' => 'screen-service'
                                                          ));
$front->getRouter()->addRoute('screenService', $route);
$route = new Zend_Controller_Router_Route('page/export/:menuId/:screenId/:reportFormat', array(
        'controller' => 'page',
        'action' => 'export',
        'reportFormat' => 'REPORT: CSV'
                                                          ));
$front->getRouter()->addRoute('pageExport', $route);
$route = new Zend_Controller_Router_Route('chart/:menuId/:chartId/*', array(
        'controller' => 'page',
        'action' => 'get-chart'
                                                          ));
$front->getRouter()->addRoute('minderChart', $route);
$route = new Zend_Controller_Router_Route('user/limit/*', array(
        'controller' => 'user',
        'action' => 'limit'
                                                          ));
$front->getRouter()->addRoute('setLimit', $route);

$route = new Zend_Controller_Router_Route('pages/:menuId', array(
    'module' => 'pages',
    'controller' => 'index',
    'action' => 'index',
    'menuId' => ''
));
$front->getRouter()->addRoute('mdrPages', $route);

$route = new Zend_Controller_Router_Route('pages/:menuId/edit-row/:screen/:form/*', array(
    'module' => 'pages',
    'controller' => 'index',
    'action' => 'edit-row',
    'menuId' => ''
));
$front->getRouter()->addRoute('mdrPagesEditRow', $route);

// Load the plugins
$plugin = new Zend_Controller_Plugin_ErrorHandler();
$plugin->setErrorHandler(array('module' => 'default', 'controller' => 'index', 'action' => 'error'));
$front->registerPlugin($plugin);


if(!isset($config->company->licensee)) {
    Zend_Registry::set('licensee', null);
} else {
    Zend_Registry::set('licensee', $config->company->licensee);
}


// Set the format we are using and the correct content-type for it
$fmt = 'pc';
if (isset($session->fmt)) {
    $fmt = $session->fmt;
}
if (isset($_REQUEST['fmt'])) {
    $fmt = $_REQUEST['fmt'];
}
if (isset($config->contenttype->$fmt)) {
    header('Content-type: ' . $config->contenttype->$fmt);
} else {
    $fmt = 'pc';
    header('Content-type: text/html');
}

// Initialise the view renderer
$view = new Zend_View();
$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
$view->addScriptPath(ROOT_DIR . '/application/scripts/' . $config->company->licensee . '/');
$view->addHelperPath(ROOT_DIR . '/includes/Minder2/View/Helper', 'Minder2_View_Helper');
$view->addHelperPath(ROOT_DIR . '/includes/Minder/View/Helper', 'Minder_View_Helper');
$view->addHelperPath(ROOT_DIR . '/includes/Minder/View/Helper/JsTemplates', 'Minder_View_Helper_JsTemplates');
$viewRenderer->setView($view);
$viewRenderer->setViewBasePathSpec(':moduleDir/views/' . $fmt . '/');

//set default Mail transport
Zend_Mail::setDefaultTransport(new Minder_Mail_Transport_FileSystem('../mail/'));

// Handle the request
$front->dispatch();
$log->done();
