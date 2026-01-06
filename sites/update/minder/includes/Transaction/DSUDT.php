<?php

class Transaction_DSUDT extends Transaction {
    protected $_serviceRecordId;
    protected $_serviceType;
    protected $_despatchId;

    public function __construct($pickDespatch, $carrierService, PickOrder $pickOrder)
    {
        $this->transCode  = 'DSUD';
        $this->transClass = 'T';
        $this->transFamily = '';
        $this->_despatchId = $pickDespatch['DESPATCH_ID'];
        $this->_serviceRecordId = $carrierService['RECORD_ID'];
        $this->_serviceType = $carrierService['SERVICE_TYPE'];

        $this->setPickOrder($pickOrder);
    }

    /**
     * Returns the location for inserting into the database
     *
     * This function should be defined by subclasses of Transaction
     *
     * @return string
     */
    public function getLocation()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getObjectId()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return implode('|', array(
            $this->_serviceType,
            $this->_serviceRecordId
        ));
    }

    /**
     * @return string
     */
    public function getQuantity()
    {
        return $this->_despatchId;
    }

    /**
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }
}