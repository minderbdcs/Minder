<?php

/**
 * Class Minder_Controller_Action_Helper_MinderOptions
 *
 * @method string getCostCenterCaption()
 * @method Minder2_Model_Options|null getProdIdGenerator()
 * @method string getSsnEditFormStyle($default = 'default')
 */
class Minder_Controller_Action_Helper_MinderOptions extends Zend_Controller_Action_Helper_Abstract {
    protected $_optionsManager;

    protected function _getOptionsManager() {
        if (empty($this->_optionsManager)) {
            $this->_optionsManager = new Minder2_Options();
        }

        return $this->_optionsManager;
    }

    function __call($name, $arguments)
    {
        $options = $this->_getOptionsManager();
        return call_user_func_array(array($options, $name), $arguments);
    }


}