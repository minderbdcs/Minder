<?php

class Minder2_SysScreen_Decorator_Abstract extends Zend_Form_Decorator_Abstract {
    /**
     * @return Minder2_Model_SysScreen
     */
    public function _getModel() {
        return $this->getElement();
    }

    public function setElement($element)
    {
        $this->_element = $element;
    }

    protected function _getView() {
        $element = $this->getElement();
        return $element->getView();
    }

    /**
     * @return Minder2_Model_SysScreen
     */
    protected function _getSysScreenModel() {
        return $this->getElement();
    }
}