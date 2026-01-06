<?php

/**
 * @property $carrierId
 * @property $postCodeDepotId
 */
class Carrier extends AbstractModel {

    protected $_carrierId;
    protected $_postCodeDepotId;

    /**
     * @return boolean
     */
    public function existedRecord()
    {
        return !empty($this->_carrierId);
    }

    /**
     * @return Carrier_NullRecord
     */
    public function getNullObject()
    {
        return new Carrier_NullRecord();
    }

    /**
     * @param mixed $source
     * @return Carrier
     */
    public function getNewObject($source)
    {
        return new Carrier($source);
    }
}