<?php

class Minder_SysScreen_Legacy_OptionManager extends Minder_SysScreen_Legacy_AbstractManager {
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

    public static function getScreenNameByScreenType($screenType) {
        return static::_getInstance()->_getCacheStorage()->getScnFormat($screenType);
    }

    public static function getQueryLimits() {
        return static::_getInstance()->_getCacheStorage()->getQueryLimits();
    }

    public static function getScnRadioButton() {
        return static::_getInstance()->_getCacheStorage()->getScnRadioButton();
    }

    public static function getDefPrice() {
        return static::_getInstance()->_getCacheStorage()->getDefPrice();
    }

    public static function getReportButtons() {
        return static::_getInstance()->_getCacheStorage()->getReportButtons();
    }

    /**
     * @return Zend_Cache_Core|Minder2_Options
     */
    protected function _getCacheStorage() {
        return parent::_getCacheStorage();
    }

    protected function _getCachedEntry() {
        return new Minder2_Options();
    }

    protected function _getPrefix()
    {
        return __CLASS__;
    }
}