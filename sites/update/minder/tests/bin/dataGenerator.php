<?php

spl_autoload_register(function($className)
{
    include implode('/', explode('_', $className)) . '.php';
});

include(__DIR__ . '/../../vendor/autoload.php');

// Setup the environment and includes path
define('ROOT_DIR', realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..'));

set_include_path(get_include_path()
    . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'includes'
    . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'library'
    . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'PEAR');

$options = getOptions();
$dataToGenerate = json_decode($options->getOption('data'));

if (empty($dataToGenerate)) {
    return;
}

$config = getConfig();
$connection = getConnection($config->database->dsn->main);
$result = array();

foreach ($dataToGenerate as $tableName => $settings) {
    $result[$tableName] = generateData($tableName, $settings, $connection);
}

echo '<?php' . PHP_EOL . PHP_EOL . 'return ';
var_export($result);
echo ';' . PHP_EOL;

function generateData($tableName, $settings, $connection) {
    $defaultValues = getDefaultValues($tableName, $connection);
    $values = generateTestValues($settings);

    $result = array();

    foreach ($values as $dataRow) {
        $result[] = array_merge($defaultValues, $dataRow);
    }

    return $result;
}

/**
 * @param $tableName
 * @param Zend_Test_PHPUnit_Db_Connection $connection
 * @return mixed
 */
function getDefaultValues($tableName, $connection) {
    $sql = 'SELECT TRIM(RDB$FIELD_NAME), TRIM(RDB$FIELD_NAME) FROM RDB$RELATION_FIELDS WHERE RDB$RELATION_NAME = ? ORDER BY RDB$FIELD_POSITION';
    $fields = $connection->getConnection()->fetchPairs($sql, array($tableName));

    $defaults = array();
    $dataFile = __DIR__ . '/defaults/' . $tableName . '.php';
    if (file_exists($dataFile) && is_readable($dataFile)) {
        $defaults = include __DIR__ . '/defaults/' . $tableName . '.php';
    }

    $defaults = empty($defaults) ? array() : $defaults;

    return array_merge(array_combine(array_values($fields), array_fill(0, count($fields), null)), $defaults);
}

function generateTestValues($settings) {
    return is_array($settings) ? $settings : array_fill(0, intval($settings), array());

}

function getConfig() {
// Load the config
    $defaultConfig = new Zend_Config(array('logging' => array('level' => 7, 'path' => sys_get_temp_dir())), true);
    $config_file = ROOT_DIR . '/minder.ini';
    $defaultConfig->merge(new Zend_Config_Ini($config_file, null));

    return $defaultConfig;
}

function getOptions() {
    $options = new Zend_Console_Getopt(array('data=s' => 'Data to generate'));

    $options->parse();

    return $options;
}

function getConnection($dsn) {
    list($dbHost, $dbAlias) = explode(':', $dsn);

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

    return new Zend_Test_PHPUnit_Db_Connection(Zend_Db::factory($dbConfig), '');
}