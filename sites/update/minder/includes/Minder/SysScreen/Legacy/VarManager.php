<?php

class Minder_SysScreen_Legacy_VarManager extends Minder_SysScreen_Legacy_AbstractManager {

    protected static $_instance;

    private function __construct() {}

    /**
     * @return static
     */
    protected static function _getInstance()
    {
        if (empty(static::$_instance)) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }

    public static function flushData() {
        static::_getInstance()->_flushData();
    }

    public static function getVars($primaryId, $screenName, $queryType) {
        return static::_getInstance()->_getVars($primaryId, $screenName, $queryType);
    }

    protected function _getVars($primaryId, $screenName, $queryType) {
        $vars = $this->_getCacheStorage()->fetchVars($screenName, $queryType);

        if (strtoupper($primaryId) == 'T') {
            $vars = array_filter($vars, function($var){return strtoupper($var['SSV_PRIMARY_ID']) == 'T';});
        }

        return $vars;
    }

    /**
     * @return Zend_Cache_Core|Minder_SysScreen_Legacy_VarProvider
     */
    protected function _getCacheStorage() {
        return parent::_getCacheStorage();
    }

    protected function _getCachedEntry()
    {
        return new Minder_SysScreen_Legacy_VarProvider();
    }

    protected function _getPrefix()
    {
        return __CLASS__;
    }
}