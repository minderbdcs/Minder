<?php

/**
 * @property string $RECORD_ID
 * @property string $COMPANY_ID
 * @property string $DEFAULT_WH_ID
 * @property string $PICK_DIRECT_DELIVERY_LOCATION
 * @property int $RECEIVE_ISSN_ORIGINAL_QTY
 * @property string RECEIVE_DIRECT_DELIVERY
 * @property string LOAN_PERIOD_NO_1
 */
class Minder2_Model_Control extends Minder2_Model {

    /**
     * @var Minder2_Model_Company
     */
    protected $_company = null;

    /**
     * @var Minder2_Model_Warehouse
     */
    protected $_warehouse = null;

    /**
     * @return string
     */
    function getName()
    {
        return $this->RECORD_ID;
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
        return $this->getName();
    }

    /**
     * @return Minder2_Model_Mapper_Company
     */
    protected function _getCompanyMapper() {
        return new Minder2_Model_Mapper_Company();
    }

    /**
     * @return Minder2_Model_Company
     */
    public function getCompany() {
        if (is_null($this->_company))
            $this->_company = $this->_getCompanyMapper()->find($this->COMPANY_ID);

        return $this->_company;
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
        if (is_null($this->_warehouse))
            $this->_warehouse = $this->_getWarehouseMapper()->find($this->DEFAULT_WH_ID);

        return $this->_warehouse;
    }
}