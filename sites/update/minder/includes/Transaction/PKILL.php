<?php

class Transaction_PKILL extends Transaction {

    public $fromWhId;
    public $fromLocnId;
    public $toWhId;
    public $toLocnId;

    public function __construct()
    {
        parent::__construct();
        $this->transCode  = 'PKIL';
        $this->transClass = 'L';
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
        return $this->toWhId . $this->toLocnId;
    }

    /**
     * @return string
     */
    public function getObjectId()
    {
        return $this->orderNo;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return implode('|', array($this->fromWhId, $this->fromLocnId));
    }

    /**
     * @return string
     */
    public function getQuantity()
    {
        return 1;
    }

    /**
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }
}