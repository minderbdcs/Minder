<?php

class Minder2_Model_StateManager {

    /**
     * @var null|Minder2_Model_StateManager
     */
    static private  $_instance = null;

    /**
     * @var null|Zend_Session_Namespace
     */
    protected $_session = null;

    private function __construct() {

    }

    /**
     * @static
     * @return Minder2_Model_StateManager
     */
    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new Minder2_Model_StateManager();
        }

        return self::$_instance;
    }

    /**
     * @return Zend_Session_Namespace
     */
    protected function _getSession() {
        if (is_null($this->_session))
            $this->_session = new Zend_Session_Namespace('modelState');

        return $this->_session;
    }

    /**
     * @return array
     */
    protected function _loasSavedStates() {
        $session = $this->_getSession();

        if (isset($session->savedSataes))
            return $session->savedSataes;

        return array();
    }

    /**
     * @param array $states
     * @return Minder2_Model_StateManager
     */
    protected function _saveStates(array $states = array()) {
        $session = $this->_getSession();
        $session->savedSataes = $states;

        return $this;
    }

    /**
     * @param \Minder2_Model_Interface $model
     * @return Minder2_Model_StateManager
     */
    public function saveState(Minder2_Model_Interface $model) {
        $savedStates = $this->_loasSavedStates();
        $savedStates[$model->getStateId()] = $model->getState();
        $this->_saveStates($savedStates);

        return $this;
    }

    /**
     * @param Minder2_Model_Interface $model
     * @return Minder2_Model_StateManager
     */
    public function restoreState(Minder2_Model_Interface $model) {
        $savedStates = $this->_loasSavedStates();

        if (isset($savedStates[$model->getStateId()]))
            $model->setState($savedStates[$model->getStateId()]);

        return $this;
    }
}