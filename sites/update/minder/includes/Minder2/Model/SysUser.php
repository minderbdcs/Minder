<?php

/**
 * @property string $USER_ID
 * @property string $DEVICE_ID
 * @property string $DEFAULT_WH_ID
 * @property string $COMPANY_ID
 * @property string $USER_CATEGORY
 *
 *
 * @property boolean $SYS_ADMIN
 * @property boolean $isLogged
 */
class Minder2_Model_SysUser extends Minder2_Model {
    function __get($name)
    {
        switch ($name) {
            case 'SYS_ADMIN':
            case 'isLogged':
                return $this->_getBooleanFieldsValue($name);
        }
        return parent::__get($name);
    }

    function __set($name, $value)
    {
        switch ($name) {
            case 'SYS_ADMIN':
            case 'isLogged':
                return $this->_setBooleanFieldValue($name, $value);
        }
        return parent::__set($name, $value);
    }


    /**
     * @return string
     */
    function getName()
    {
        return $this->USER_ID;
    }

    /**
     * @return int
     */
    function getOrder()
    {
        return 0;
    }

    /**
     * @return string
     */
    function getStateId()
    {
        return 'SYS_USER-' . $this->getName();
    }

    public function isSuperAdmin() {
        return $this->USER_ID == 'Admin';
    }

    public function isAdmin() {
        return $this->isSuperAdmin() || $this->SYS_ADMIN;
    }

    /**
     * @return Minder2_Model_Mapper_SysEquip
     */
    protected function _getSysEquipMapper() {
        return new Minder2_Model_Mapper_SysEquip();
    }

    /**
     * @return Minder2_Model_SysEquip
     */
    public function getDevice() {
        return $this->_getSysEquipMapper()->find($this->DEVICE_ID);
    }

    /**
     * @return Minder2_Model_Mapper_Warehouse
     */
    protected function _getWarehouseMapper() {
        return new Minder2_Model_Mapper_Warehouse();
    }

    /**
     * @return Minder2_Model_Warehouse
     */
    public function getWarehouse() {
        return $this->_getWarehouseMapper()->find($this->DEFAULT_WH_ID);
    }

    /**
     * @return Minder2_Model_Mapper_Company
     */
    protected function _getCompanyMapper() {
        return new Minder2_Model_Mapper_Company();
    }

    /**
     * @return Minder2_Model_Company[]
     */
    public function getAccessCompanyList() {
        return $this->_getCompanyMapper()->selectUsersAccessCompanyList($this);
    }

    /**
     * @return Minder2_Model_Warehouse[]
     */
    public function getAccessWarehouseList() {
        return $this->_getWarehouseMapper()->selectUsersAccessWarehouseList($this);
    }

    /**
     * @return Minder2_Model_SysEquip[]
     */
    public function getAccessPrinterList() {
        return $this->_getSysEquipMapper()->selectUsersAccessPrinteList($this);
    }
}