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
    . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'library'
    . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'PEAR');

// Load the config
$defaultConfig = new Zend_Config(array('logging' => array('level' => 7, 'path' => sys_get_temp_dir())), true);
//$defaultConfig->merge(new Zend_Config_Ini(ROOT_DIR . '/minder.ini', null));
// expect in document root something like "/var/sites/sitename/html"
// so the 4th entry is the sitename
$mdrDocRoot = explode("/" , $_SERVER['DOCUMENT_ROOT']);
$mdrSitename = $mdrDocRoot[3];
$config_file = __DIR__ . "/../minder.ini";
if(file_exists($config_file) ) {
    $mdrExists = True;
    //echo "$config_file found";
} else {
    // use previous location for config
    $config_file = ROOT_DIR . '/minder.ini';
}
$defaultConfig->merge(new Zend_Config_Ini($config_file, null));

$options = new Zend_Console_Getopt(array('file=s' => 'path to data file'));

$options->parse();
$dataFile = $options->getOption('file');

if (empty($dataFile)) {
    return;
}

echo "Loading data from " . $dataFile . "\n";

$dataSet = new PHPUnit_Extensions_Database_DataSet_YamlDataSet($dataFile);
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

$connection = new Zend_Test_PHPUnit_Db_Connection(Zend_Db::factory($dbConfig), '');

$triggers = array('STOP_DELETE_PICK_ITEM', 'TG_ADD_PICK_ITEM_LINE_NO');

foreach ($triggers as $triggerName) {
    $connection->getConnection()->query('ALTER TRIGGER ' . $triggerName . ' INACTIVE ');
}

$insert = new PHPUnit_Extensions_Database_Operation_Composite(array(
    PHPUnit_Extensions_Database_Operation_Factory::DELETE(),
));
$insert->execute($connection, $dataSet);

foreach ($triggers as $triggerName) {
    $connection->getConnection()->query('ALTER TRIGGER ' . $triggerName . ' ACTIVE ');
}

echo "Executed\n";