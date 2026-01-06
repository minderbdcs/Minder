<?php

class Minder_Db_Adapter_Sysscreen_Mapper_Abstract {
    /**
     * @var Zend_Db_Table_Abstract
     */
    protected $_dbTable = null;

    /**
     * @return Minder2_Model_Company
     */
    protected function _getCurrentCompany() {
        return Minder2_Environment::getInstance()->getCurrentCompany();
    }

    /**
     * @return Minder2_Model_Warehouse
     */
    protected function _getCurrentWarehouse() {
        return Minder2_Environment::getCurrentWarehouse();
    }

    /**
     * @return Minder2_Model_SysEquip
     */
    protected function _getCurrentDevice() {
        return Minder2_Environment::getCurrentDevice();
    }

    protected function _getCurrentUser() {
        return Minder2_Environment::getCurrentUser();
    }
}