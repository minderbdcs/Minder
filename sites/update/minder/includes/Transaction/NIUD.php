<?php

class Transaction_NIUD extends Transaction {
    /**
     * @var
     */
    private $_newUseByDate;
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
     * @param $newUseByDate
     * @param $currentQty
     * @param $whId
     * @param $locnId
     * @param $companyId
     */
    public function __construct($ssnId, $newUseByDate, $currentQty, $whId, $locnId, $companyId)
    {
        $this->transCode        = 'NIUD';
        $this->transClass       = '';
        $this->_ssnId           = $ssnId;
        $this->_issnCurrentQty  = $currentQty;
        $this->companyId        = $companyId;
        $this->_newUseByDate = $newUseByDate;
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
        return $this->_newUseByDate;
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