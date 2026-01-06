<?php

class Minder2_View_Helper_WarehouseSelect extends Zend_View_Helper_Abstract {

    /**
     * @param Minder2_Model_Warehouse $whA
     * @param Minder2_Model_Warehouse $whB
     * @return int
     */
    protected function _sorter($whA, $whB) {
        if ($whA->WH_ID > $whB->WH_ID)
            return 1;

        if ($whA->WH_ID < $whB->WH_ID)
            return -1;

        return 0;
    }

    protected function _getWarehouseLimitList() {
        $result = array('all' => 'All');

        try {
            $defaultWarehouse = Minder2_Environment::getInstance()->getSystemControls()->getWarehouse();
            $warehouseList = Minder2_Environment::getWarehouseList();

            if (isset($warehouseList[$defaultWarehouse->WH_ID]))
                unset($warehouseList[$defaultWarehouse->WH_ID]); //will add later

            usort($warehouseList, array($this, '_sorter'));
            array_unshift($warehouseList, $defaultWarehouse);

            /**
             * @var Minder2_Model_Warehouse $warehouse
             */
            foreach ($warehouseList as $warehouse) {
                $result[$warehouse->WH_ID] = $warehouse->DESCRIPTION;
            }
        } catch (Exception $e) {

        }

        return $result;
    }

    public function warehouseSelect($name, $value, $attribs = null, $listsep = "<br />\n") {
        return $this->view->formSelect($name, $value, $attribs, $this->_getWarehouseLimitList(), $listsep);
    }
}