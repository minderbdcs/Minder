<?php

define('ROOT_DIR', dirname(__FILE__) . '/../..');

$includes = array(
    get_include_path(),
    dirname(__FILE__),
    ROOT_DIR . '/library'
);

set_include_path(implode(PATH_SEPARATOR, $includes));

$classMap = require_once 'classmap.php';

spl_autoload_register(function($className) {
    return @include(implode('/', explode('_', $className)) . '.php');
}, true);

spl_autoload_register(function($className)use($classMap)
{

    if (isset($classMap[$className])) {
        require_once $classMap[$className];
        return true;
    }

    return false;
}, true, true);