<?php

class Minder_OtcProcess_ToolTransaction extends Transaction {

    protected $_ssnId;
    protected $_reference;
    protected $_location;
    protected $_currentQty;

    public function __construct(Minder_OtcProcess_State $processState)
    {
        $this->transCode    = $processState->toolTransaction->type;
        $this->transClass   = 'A';
        $this->_ssnId       = $processState->item->id;
        $this->_reference   = $processState->toolTransaction->reference;
        $this->_location    = $processState->item->getLocationId();
        $this->_currentQty  = $processState->item->currentQty;
        $this->companyId    = $processState->item->companyId;
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
        return $this->_reference;
    }

    /**
     * @return string
     */
    public function getQuantity()
    {
        return $this->_currentQty;
    }

    /**
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }
}