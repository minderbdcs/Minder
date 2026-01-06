<?php

class Transaction_DSPSD extends Transaction {

    protected $_dcNo;
    protected $_printerId;
    protected $_labelCopies = 1;
    protected $_pickOrder = '';

    public function __construct($orderNo, $dcNo, $companyId, $printerId, $labelsCopy = 1)
    {
        $this->transCode        = 'DSPS';
        $this->transClass       = 'D';
        $this->_printerId       = $printerId;
        $this->_labelCopies     = $labelsCopy;
        $this->_pickOrder       = $orderNo;
        $this->companyId        = $companyId;
        $this->_dcNo            = $dcNo;
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
        return $this->_dcNo;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return implode('|', array(
            implode('=', array('SYS_EQUIP.DEVICE_ID', $this->_printerId)),
        ));
    }

    /**
     * @return string
     */
    public function getQuantity()
    {
        return $this->_labelCopies;
    }

    /**
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }

    public function getOrderNo()
    {
        return $this->_pickOrder;
    }
}