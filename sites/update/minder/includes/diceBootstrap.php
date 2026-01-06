<?php

$diCache = array();
$diCache = require "diCache.php";

$manager = new \MinderNG\Di\ReflectionManager($diCache);
$dice = new \MinderNG\Di\DiContainer($manager);
$rule = new \MinderNG\Di\Rule();
$dice->assign($manager);

$rule->substitutions['Minder2_Environment'] = function() {
    return Minder2_Environment::getInstance();
};

$rule->substitutions['Minder'] = function() {
    return Minder::getInstance();
};

$rule->substitutions['Zend_Cache_Core'] = function() {
    return Zend_Cache::factory('Core', 'File', array('automatic_serialization' => true), array('hashed_directory_level' => 2));
};

$dice->addRule('*', $rule);

$sysTableRule = new \MinderNG\Di\Rule();
$sysTableRule->shared = true;
$sysTableRule->call[] = array('setDefaultCompanyId', array(function(){return Minder::getInstance()->defaultControlValues['COMPANY_ID'];}));

$dice->addRule('MinderNG\\Database\\Table\\SysMenu', $sysTableRule);
$dice->addRule('MinderNG\\Database\\Table\\SysScreen', $sysTableRule);
$dice->addRule('MinderNG\\Database\\Table\\SysScreenForm', $sysTableRule);

$dice->assign($dice);

return $dice;