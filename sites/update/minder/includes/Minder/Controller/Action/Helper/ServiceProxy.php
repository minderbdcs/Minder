<?php

abstract class Minder_Controller_Action_Helper_ServiceProxy extends Zend_Controller_Action_Helper_Abstract {
    protected $_serviceInstance = null;

    function __call($name, $arguments)
    {
        return call_user_func_array(array($this->_getServiceInstance(), $name), $arguments);
    }


    protected function _getServiceInstance() {
        if (is_null($this->_serviceInstance)) {
            $this->_serviceInstance = $this->_createServiceInstance();
        }

        return $this->_serviceInstance;
    }

    protected function _createServiceInstance() {
        $className = $this->_getServiceClassName();
        return new $className();
    }

    abstract protected function _getServiceClassName();
}