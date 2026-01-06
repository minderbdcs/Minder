<?php

class Minder_SysScreen_QueryLog {

    const LOG_LIMIT_PROPERTY = 'LOG_LIMIT_PROPERTY';
    const QUERY_LOG = 'QUERY_LOG';
    const PROPERTY = 'PROPERTY';
    const SESSION_NAMESPACE = 'SysScreenQueryLog';

    protected static $_instance = null;
    protected $_session = null;

    protected function __construct() {

    }

    /**
     * @static
     * @return Minder_SysScreen_QueryLog
     */
    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new Minder_SysScreen_QueryLog();
        }

        return self::$_instance;
    }

    public function logQuery($query, $args, $time) {
        $log = $this->getQueryLog();
        $log[] = new Minder_SysScreen_QueryLog_Entry($query, $args, $time);
        $limit = $this->_getLimit();

        if (count($log) > $limit) {
            array_shift($log);
        }

        $this->_setQueryLog($log);

        return $this;
    }

    public function getQueryLog() {
        $session = $this->_getSession();
        $result = isset($session->queryLog) ? $session->queryLog : array();
        return $result;
    }

    protected function _setQueryLog($log) {
        $session = $this->_getSession();
        $session->queryLog = $log;
        return $this;
    }

    protected function _getLimit() {
        $limit = $this->_getProperty(self::LOG_LIMIT_PROPERTY);
        return (empty($limit) ? 0 : $limit);
    }

    public function setLimit($limit) {
        $limit = intval($limit);
        $limit = $limit < 0 ? 0 : $limit;

        $log = $this->getQueryLog();
        $this->_setQueryLog(array_slice($log, -$limit));

        return $this->_setProperty(self::LOG_LIMIT_PROPERTY, intval($limit));
    }

    protected function _setProperty($key, $value) {
        $properies = $this->_getProperties();
        $properies[$key] = $value;
        $this->_setProperties($properies);
        return $this;
    }

    protected function _getProperty($key) {
        $properies = $this->_getProperties();
        return isset($properies[$key]) ? $properies[$key] : null;
    }

    protected function _getProperties() {
        $session = $this->_getSession();
        return isset($session->properties) ? $session->properties : array();
    }

    protected function _setProperties($properties) {
        $session = $this->_getSession();
        $session->properties = $properties;
        return $this;
    }

    protected function _getSession() {
        if (is_null($this->_session)) {
            $this->_session = new Zend_Session_Namespace(self::SESSION_NAMESPACE);
        }

        return $this->_session;
    }
}