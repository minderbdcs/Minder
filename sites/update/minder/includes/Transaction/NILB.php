<?php

class Transaction_NILB extends Transaction {
    /**
     * @var
     */
    private $_newLotBatchNo;
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
     * @param $newLotBatchNo
     * @param $currentQty
     * @param $whId
     * @param $locnId
     * @param $companyId
     */
    public function __construct($ssnId, $newLotBatchNo, $currentQty, $whId, $locnId, $companyId)
    {
        $this->transCode        = 'NILB';
        $this->transClass       = '';
        $this->_ssnId           = $ssnId;
        $this->_issnCurrentQty  = $currentQty;
        $this->companyId        = $companyId;
        $this->_newLotBatchNo   = $newLotBatchNo;
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
        return $this->_newLotBatchNo;
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