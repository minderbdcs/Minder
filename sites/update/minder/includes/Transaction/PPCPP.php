<?php

//todo: add checks for field length

require_once 'Transaction.php';

class Transaction_PPCPP Extends Transaction
{
    public $productCode        = '';
    public $prodRetrieveStatus = '';
    public $companyId          = '';

    public function __construct()
    {
        $this->transCode      = 'PPCP';
        $this->transClass     = 'P';
    }
    
    public function getObjectId()
    {
        return $this->productCode;
    }
    
    public function getReference()
    {
        return $this->companyId . str_repeat(' ', 10 - strlen($this->companyId)) . '|' . $this->prodRetrieveStatus;
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
        return 1;
    }


    public function getLocation()
    {
        return '';
    }


    public function getSubLocation()
    {
        return '';
    }
    
    public function fillFromProdProfileRow($row) {
        $this->productCode        = $row['PROD_ID'];
        $this->companyId          = $row['COMPANY_ID'];
        $this->prodRetrieveStatus = $row['PROD_RETRIEVE_STATUS'];
    }
}
