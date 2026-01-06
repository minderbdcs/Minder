<?php

class Transaction_DSACU extends Transaction {
    protected $_outSscc;
    protected $_deviceId;

    public function __construct($outSscc, $deviceId)
    {
        $this->_outSscc = $outSscc;
        $this->_deviceId = $deviceId;
        $this->transCode    = 'DSAC';
        $this->transClass   = 'U';
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
        return $this->_deviceId;
    }

    /**
     * @return string
     */
    public function getObjectId()
    {
        return $this->_outSscc;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getQuantity()
    {
        return 0;
    }

    /**
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }
}