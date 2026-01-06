<?php

/**
 * @property string $WH_ID
 * @property string $DESCRIPTION
 */
class Minder2_Model_Warehouse extends Minder2_Model {
    /**
     * @return string
     */
    function getName()
    {
        return $this->WH_ID;
    }

    /**
     * @return string
     */
    function getDescription()
    {
        return $this->DESCRIPTION;
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
        return 'WAREHOUSE-' . $this->getName();
    }

    /**
     * @return Minder2_Model_Mapper_SysEquip
     */
    protected function _getSysEquipMapper() {
        return new Minder2_Model_Mapper_SysEquip();
    }

    /**
     * @return array(Minder2_Model_SysEquip)
     */
    function getPrinterList() {
        return $this->_getSysEquipMapper()->selectWarehousePrinterList($this);
    }
}