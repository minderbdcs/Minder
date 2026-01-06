<?php

class Transaction_NIPOA extends Transaction {

    public function __construct($ssnId, $purchaseOrder, $whId, $locnId)
    {
        $this->_ssnId = $ssnId;
        $this->_purchaseOrder = $purchaseOrder;
        $this->_whId = str_pad($whId, 2, '', STR_PAD_RIGHT);
        $this->_lochId = $locnId;
        $this->transCode = 'NIPO';
        $this->transClass = 'A';
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
        return $this->_whId . $this->_lochId;
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
        return $this->_purchaseOrder;
    }

    /**
     * @return string
     */
    public function getQuantity()
    {
        return '1';
    }

    /**
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }
}