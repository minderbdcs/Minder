<?php

class Transaction_STISS extends Transaction {
    protected $_ssnId;
    protected $_whId;
    protected $_locnId;

    protected $_qty = 1;
    protected $_stockRecord = '';
    protected $_comment = '';

    public function __construct($ssnId, $whId, $locnId)
    {
        $this->transCode    = 'STIS';
        $this->transClass   = 'S';
        $this->_ssnId       = $ssnId;
        $this->_whId        = $whId;
        $this->_locnId      = $locnId;
    }

    /**
     * Returns the ISSN which should be updated
     * Max 12 characters
     *
     * @return string
     */
    public function getObjectId()
    {
        return $this->_ssnId;
    }

    /**
     * Returns the reference value for update
     *
     * @return string
     */
    public function getReference()
    {
        return implode('|', array($this->_qty, $this->_stockRecord, $this->_comment));
    }

    /**
     * Returns the quantity for inserting into the database
     *
     * The quantity is always the value 1
     *
     * @return string
     */
    public function getQuantity()
    {
        return $this->_qty;
    }

    /**
     * Returns the location for inserting into the database
     *
     * @return string
     */
    public function getLocation()
    {
        return str_pad($this->_whId, 2 - strlen($this->_whId), '', STR_PAD_RIGHT)  . $this->_locnId;
    }

    /**
     * Returns the sublocation for inserting into the database
     *
     * The sublocation for the STIS transaction is empty string
     *
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }

}