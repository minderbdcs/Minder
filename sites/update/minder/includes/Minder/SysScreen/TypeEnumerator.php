<?php

/**
 * Class Minder_SysScreen_TypeEnumerator
 *
 * @deprecated
 */
class Minder_SysScreen_TypeEnumerator {
    private static $_instance;

    /**
     * @var string[]
     */
    protected $_namesMap = array();

    private function __construct() {

    }

    /**
     * @return Minder_SysScreen_TypeEnumerator
     */
    private static function _getInstance() {
        if (empty(static::$_instance)) {
            static::$_instance = new static();
        }

        return static::$_instance;
    }

    /**
     * @deprecated
     * @param $screenType
     * @return string
     */
    public static function getName($screenType) {
        return static::_getInstance()->_getName($screenType);
    }

    /**
     * @return Minder2_Options
     */
    protected function _getOptions() {
        return new Minder2_Options();
    }

    /**
     * @param $screenType
     * @return string
     */
    protected function _fetchName($screenType) {
        return $this->_getOptions()->getScnFormat($screenType);
    }

    /**
     * @param $screenType
     * @return string
     */
    protected function _getName($screenType) {
        if (!isset($this->_namesMap[$screenType])) {
            $this->_namesMap[$screenType] = $this->_fetchName($screenType);
        }

        return $this->_namesMap[$screenType];
    }
}