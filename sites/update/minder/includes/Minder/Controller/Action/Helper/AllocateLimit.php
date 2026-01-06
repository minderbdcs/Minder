<?php

class Minder_Controller_Action_Helper_AllocateLimit extends Zend_Controller_Action_Helper_Abstract {

    /**
     * @return Minder_Controller_Action_Helper_ScreenDataKeeper
     */
    protected function _screenDataKeeper() {
        return $this->getActionController()->getHelper('ScreenDataKeeper');
    }

    public function getProductLimit($instance) {
        $current = $this->_screenDataKeeper()->getParam('MAX_PRODUCTS', $instance, 'default', '');
        return is_null($current) ? $this->_getMinder()->defaultControlValues['MAX_PICK_PRODUCTS'] : $current;
    }

    protected function _getMinder() {
        return Minder::getInstance();
    }
}