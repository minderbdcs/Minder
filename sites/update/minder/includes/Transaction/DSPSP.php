<?php

class Transaction_DSPSP extends Transaction {

    public $ssccId;
    public $printerId;
    public $labelCopies = 1;

    public function __construct()
    {
        $this->transCode  = 'DSPS';
        $this->transClass = 'P';
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
        return $this->ssccId;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return implode('|', array(
            implode('=', array('SYS_EQUIP.DEVICE_ID', $this->printerId)),
        ));
    }

    /**
     * @return string
     */
    public function getQuantity()
    {
        return $this->labelCopies;
    }

    /**
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }
}