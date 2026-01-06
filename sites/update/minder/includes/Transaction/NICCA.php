<?php

class Transaction_NICCA extends Transaction {
    protected $_ssnId;
    protected $_costCenter;
    protected $_issnCurrentQty;

    public function __construct($ssnId, $costCenter, $location, $issnCurrentQty)
    {
        $this->transCode    = 'NICC';
        $this->transClass   = 'A';
        $this->transFamily  = '';

        $this->_ssnId       = $ssnId;
        $this->_costCenter  = $costCenter;
        $this->_location    = $location;
        $this->_issnCurrentQty = $issnCurrentQty;
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
        return $this->_location;
    }

    /**
     * @return string
     */
    public function getObjectId()
    {
        return $this->_ssnId;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->_costCenter;
    }

    /**
     * @return string
     */
    public function getQuantity()
    {
        return $this->_issnCurrentQty;
    }

    /**
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }
}