<?php

class Transaction_DSACK extends Transaction {
    protected $_deviceTo;
    protected $_outSscc;

    public function __construct($deviceTo, $outSscc)
    {
        parent::__construct();

        $this->_deviceTo    = $deviceTo;
        $this->_outSscc     = $outSscc;
        $this->transCode    = 'DSAC';
        $this->transClass   = 'K';
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
        return $this->_deviceTo;
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
        return '';
    }

    /**
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }
}