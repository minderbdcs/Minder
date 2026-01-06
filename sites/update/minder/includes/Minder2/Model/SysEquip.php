<?php

/**
 * @property string $DEVICE_ID
 * @property string $EQUIPMENT_DESCRIPTION_CODE
 * @property string WH_ID
 * @property string LOCN_ID
 * @property string DEVICE_TYPE
 * @property string DEFAULT_LP_PRINTER
 * @property string SE_BRAND
 * @property string SE_MODEL
 *
 * @property boolean existed
 */
class Minder2_Model_SysEquip extends Minder2_Model {

    const DEFAULT_BRAND = 'DEFAULT';
    const DEFAULT_MODEL = 'DEFAULT';

    /**
     * @return string
     */
    function getName()
    {
        return $this->DEVICE_ID;
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
        return 'SYS_EQUIP-' . $this->getName();
    }

    function getLocation() {
        return $this->WH_ID . $this->LOCN_ID;
    }

    function getBrandOrDefault() {
        return empty($this->SE_BRAND) ? self::DEFAULT_BRAND : $this->SE_BRAND;
    }

    function getModelOrDefault() {
        return empty($this->SE_MODEL) ? self::DEFAULT_MODEL : $this->SE_MODEL;
    }
}