<?php
/**
* CLI module to work with Austpost Manifest
*/

function __autoload($className)
{
    include implode('/', explode('_', $className)) . '.php';
}

// Setup the environment and includes path
define('ROOT_DIR', realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..'));

set_include_path(get_include_path()
    . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'includes'
    . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'library'
    . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'PEAR');

    
// Load the config
$defaultConfig = new Zend_Config(array('logging' => array('level' => 7, 'path' => sys_get_temp_dir())), true);
//$defaultConfig->merge(new Zend_Config_Ini(ROOT_DIR . '/minder.ini', null));
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
$defaultConfig->merge(new Zend_Config_Ini($config_file, null));

try {
    AustpostManifest::init($defaultConfig);
    AustpostManifest::getInstance()->run();
} catch (Zend_Console_Getopt_Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL . PHP_EOL;
    echo AustpostManifest::getInstance()->getGetopt()->getUsageMessage();
} catch (Exception $e) {
    echo 'Critical Error: ' . $e->getMessage() . PHP_EOL . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL . PHP_EOL;
    
    AustpostManifest::error($e->getMessage());
}
