<?php

class Minder_SysScreen_Legacy_TabManager extends Minder_SysScreen_Legacy_AbstractManager {

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

    public static function getTabs($screenName) {
        return static::_getInstance()->_getCacheStorage()->fetchTabs($screenName);
    }

    /**
     * @return Zend_Cache_Core|Minder_SysScreen_Legacy_TabProvider
     */
    protected function _getCacheStorage() {
        return parent::_getCacheStorage();
    }

    protected function _getCachedEntry()
    {
        return new Minder_SysScreen_Legacy_TabProvider();
    }

    protected function _getPrefix()
    {
        return __CLASS__;
    }
}