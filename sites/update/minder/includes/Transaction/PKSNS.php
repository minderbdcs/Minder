<?php

class Transaction_PKSNS extends Transaction {
    private $_pickLabelNo;
    private $_serialNumber;
    private $_whId;
    private $_locnId;
    private $_reference;

    public function __construct($pickLabelNo, $serialNumber, $whId, $locnId, $reference, $prodId)
    {
        $this->transCode  = 'PKSN';
        $this->transClass = 'S';
        $this->transFamily = '';

        $this->_pickLabelNo = $pickLabelNo;
        $this->_serialNumber = $serialNumber;
        $this->_whId = str_pad($whId, 2, STR_PAD_LEFT);
        $this->_locnId = $locnId;
        $this->_reference = $reference;
        $this->prodId = $prodId;
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
        return $this->_whId . $this->_locnId;
    }

    /**
     * @return string
     */
    public function getObjectId()
    {
        return $this->_serialNumber;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->_reference;
    }

    /**
     * @return string
     */
    public function getQuantity()
    {
        return '1';
    }

    /**
     * @return string
     */
    public function getSubLocation()
    {
        return $this->_pickLabelNo;
    }
}