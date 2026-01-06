<?php

class Transaction_DSUC extends Transaction {
    protected $_despatchId;
    protected $_carrierId;
    protected $_serviceId;

    public function __construct($pickDespatch, $carrierId, $serviceId, PickOrder $pickOrder)
    {
        $this->transCode  = 'DSUC';
        $this->transClass = '';

        $this->_despatchId = $pickDespatch;
        $this->_carrierId = $carrierId;
        $this->_serviceId = $serviceId;
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
            '',
            $this->_carrierId,
            $this->_serviceId,
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