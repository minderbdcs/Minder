<?php
// Setup the environment and includes path
$rootDir = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..');

set_include_path(get_include_path()
    . PATH_SEPARATOR . $rootDir . DIRECTORY_SEPARATOR . 'includes'
    . PATH_SEPARATOR . $rootDir . DIRECTORY_SEPARATOR . 'library'
    . PATH_SEPARATOR . $rootDir . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'PEAR');

    function __autoload($className)
{
    include implode('/', explode('_', $className)) . '.php';
}
?>
