<?php

class Minder2_View_Helper_WarehouseLimitSelect extends Zend_View_Helper_Abstract {
    protected function _getWarehouseLimit() {
        return Minder2_Environment::getWarehouseLimit()->WH_ID;
    }

    public function warehouseLimitSelect($name, $attribs = null, $listsep = "<br />\n") {
        return $this->view->warehouseSelect($name, $this->_getWarehouseLimit(), $attribs, $listsep);
    }
}