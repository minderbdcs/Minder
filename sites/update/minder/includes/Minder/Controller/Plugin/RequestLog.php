<?php

class Minder_Controller_Plugin_RequestLog extends Zend_Controller_Plugin_Abstract {
    protected $_log;

    protected function _getLog() {
        if (empty($this->_log)) {
            $this->_log = Minder_Registry::getLogger()->startDetailedLog();
        }
        return $this->_log;
    }

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $actionController = $request->getControllerName() . '::' . $request->getActionName();
        $this->_getLog()->starting('controller ' . $actionController);
    }

    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $this->_getLog()->done();
    }

}