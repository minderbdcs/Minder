<?php

class Transaction_DSUSZ extends Transaction {

    protected $_connoteNo = '';
    protected $_accountNo = '';
    protected $_despatchId = '';
    protected $_carrierId = '';
    protected $_carrierDepotId = '';

    public function __construct($connoteNo, $accountNo, $despatchId, $carrierId, $depotId)
    {
        $this->_connoteNo = $connoteNo;
        $this->_accountNo = $accountNo;
        $this->_despatchId = $despatchId;
        $this->_carrierId = $carrierId;
        $this->_carrierDepotId = $depotId;

        parent::__construct();

        $this->transClass = 'Z';
        $this->transCode = 'DSUS';
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
        return $this->_connoteNo . $this->_accountNo;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->_carrierDepotId . '|';
    }

    /**
     * @return string
     */
    public function getQuantity()
    {
        return $this->_despatchId;
    }

    /**
     * @return string
     */
    public function getSubLocation()
    {
        $this->_carrierId;
    }
}