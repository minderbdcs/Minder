<?php

class Transaction_NIBB extends Transaction {
    /**
     * @var
     */
    private $_newBestBefore;
    private $_issnCurrentQty;
    private $_ssnId;
    /**
     * @var
     */
    private $_whId;
    /**
     * @var
     */
    private $_locnId;

    /**
     * Initialise the transaction
     *
     * @param $ssnId
     * @param $newBestBefore
     * @param $currentQty
     * @param $whId
     * @param $locnId
     * @param $companyId
     */
    public function __construct($ssnId, $newBestBefore, $currentQty, $whId, $locnId, $companyId)
    {
        $this->transCode        = 'NIBB';
        $this->transClass       = '';
        $this->_ssnId           = $ssnId;
        $this->_issnCurrentQty  = $currentQty;
        $this->companyId        = $companyId;
        $this->_newBestBefore = $newBestBefore;
        $this->_whId = $whId;
        $this->_locnId = $locnId;
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
        return str_pad(substr($this->_whId, 0, 2), 2, '', STR_PAD_LEFT) . $this->_locnId;
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
        return $this->_newBestBefore;
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