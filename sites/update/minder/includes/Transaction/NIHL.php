<?php

class Transaction_NIHL extends Transaction {
    protected $_ssnId;
    protected $_homeLocation;
    protected $_issnCurrentQty;

    public function __construct($ssnId, $newHomeLocation, $issnCurrentQty = 1, $companyId = '')
    {
        $this->transCode        = 'NIHL';
        $this->transClass       = '';
        $this->_ssnId           = $ssnId;
        $this->_homeLocation    = $newHomeLocation;
        $this->_issnCurrentQty  = $issnCurrentQty;
        $this->companyId        = $companyId;
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
        return $this->_homeLocation;
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
        //return substr($this->_homeLocation, 2);
        return substr($this->_homeLocation, 2);
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
