<?php

class Minder_Log_Detailed {
    const MARK_MSG      = '------------------------------------------------------------------------------------------';
    const MARK_START    = '>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>';
    const MARK_END      = '<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<';

    protected $_classicLog;

    protected $_defaultLogLevel;

    protected $_previous;

    protected $_startingMsg;

    function __construct(Zend_Log $log, $defaultLogLevel)
    {
        $this->_classicLog = $log;
        $this->_defaultLogLevel = $defaultLogLevel;
        $this->_setPrevious(microtime(true));
    }

    /**
     * @return mixed
     */
    protected function _getPrevious()
    {
        return $this->_previous;
    }

    /**
     * @param mixed $previous
     * @return $this
     */
    protected function _setPrevious($previous)
    {
        $this->_previous = $previous;
        return $this;
    }

    /**
     * @return \Zend_Log
     */
    public function getClassicLog()
    {
        return $this->_classicLog;
    }

    /**
     * @return mixed
     */
    protected function _getDefaultLogLevel()
    {
        return $this->_defaultLogLevel;
    }

    public function log($message, $priority = null) {
        $current = microtime(true);
        $priority = is_null($priority) ? $this->_getDefaultLogLevel() : $priority;
        $duration = $current - $this->_getPrevious();

        $message = 'Completed in ' . $duration . ' seconds: ' . $message;
        $this->getClassicLog()->log($message, $priority);
        $this->_setPrevious($current);
    }

    public function error($message) {
        $current = microtime(true);
        $duration = $current - $this->_getPrevious();

        $message = 'Completed with error in ' . $duration . ' seconds: ' . $message;
        $this->getClassicLog()->log($message, Zend_Log::ERR);
        $this->_setPrevious($current);
    }

    public function info($message) {
        $this->log($message, Zend_Log::INFO);
    }

    public function classicInfo($message) {
        $this->getClassicLog()->info($message);
    }

    public function mark($priority = null) {
        $priority = is_null($priority) ? $this->_getDefaultLogLevel() : $priority;
        $this->getClassicLog()->log(static::MARK_MSG, $priority);
    }

    public function starting($msg) {
        $this->classicInfo(static::MARK_START);
        $this->classicInfo($msg);
        $this->_startingMsg = $msg;
    }

    public function done() {
        $this->info($this->_startingMsg);
        $this->classicInfo(static::MARK_END);
    }

    public function doneWithErrors($errorMsg = '') {
        $this->error($this->_startingMsg . ' ' . $errorMsg);
        $this->classicInfo(static::MARK_END);
    }
}