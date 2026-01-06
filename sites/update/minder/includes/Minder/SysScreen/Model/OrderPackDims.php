<?php

class Minder_SysScreen_Model_OrderPackDims extends Minder_SysScreen_Model implements Minder_SysScreen_DataSource_Parameter_Interface {

    protected $_pickOrders = array();

    /**
     * @return string
     */
    protected function getPickItemPickOrderLimit() {
        if (count($this->_pickOrders) < 1)
            return '1 = 1'; //no limit

        return "PICK_ITEM.PICK_ORDER IN ('" . implode("', '", $this->_pickOrders) . "')";
    }

    /**
     * @throws Minder_SysScreen_DataSource_Parameter_Exception
     * @param string $paramName
     * @return string
     */
    public function getValue($paramName)
    {
        if ($paramName == '%PICK_ITEM_PICK_ORDER_LIMIT%') return $this->getPickItemPickOrderLimit();

        throw new Minder_SysScreen_DataSource_Parameter_Exception('Unsupported Datasource Parameter "' . $paramName . '"');
    }

    /**
     * @param array $pickOrders
     * @return void
     */
    public function setPickOrderLimit($pickOrders) {
        $this->_pickOrders = $pickOrders;
    }

}