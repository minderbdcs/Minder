<?php

class Minder2_Controller_Action extends Zend_Controller_Action {
    public function init()
    {
        parent::init();

        $this->_helper->addPrefix('Minder_Controller_Action_Helper');
        Zend_Layout::startMvc();
    }

    protected function _getMinder() {
        return Minder::getInstance();
    }

    /**
     * @return Minder_Controller_Action_Helper_MasterSlave
     */
    protected function _masterSlave() {
        return $this->_helper->getHelper('MasterSlave');
    }
}