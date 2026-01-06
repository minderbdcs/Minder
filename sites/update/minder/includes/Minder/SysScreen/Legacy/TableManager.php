<?php

/**
 * Class Minder_SysScreen_Legacy_TableManager
 */
class Minder_SysScreen_Legacy_TableManager extends Minder_SysScreen_Legacy_AbstractManager {
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

    public static function getTables($screenName) {
        return static::_getInstance()->_getTables($screenName);
    }

    public static function flushData() {
        static::_getInstance()->_flushData();
    }

    protected function _getTables($screenName) {
        return $this->_getCacheStorage()->fetchTables($screenName);
    }

    /**
     * @return Zend_Cache_Core|Minder_SysScreen_Legacy_TableProvider
     */
    protected function _getCacheStorage()
    {
        return parent::_getCacheStorage();
    }


    /**
     * @return Minder_SysScreen_Legacy_TableProvider
     */
    protected function _getCachedEntry()
    {
        return new Minder_SysScreen_Legacy_TableProvider();
    }

    protected function _getPrefix()
    {
        return __CLASS__;
    }
}