<?php

//todo: add checks for field length

require_once 'Transaction.php';

class Transaction_PPAIP Extends Transaction
{
    public $productCode = '';
    public $location    = '';
    public $alternateId = '';

    public function __construct()
    {
        $this->transCode      = 'PPAI';
        $this->transClass     = 'P';
    }
    
    public function getObjectId()
    {
        return $this->productCode;
    }

    
    
    public function getReference()
    {
        return $this->alternateId;
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
        return '1';
    }


    public function getLocation()
    {
        return $this->location;
    }


    public function getSubLocation()
    {
        return '';
    }
    
    public function fillFromProdProfileRow($row) {
        $this->productCode = $row['PROD_ID'];
        $this->alternateId = $row['ALTERNATE_ID'];
        $this->location    = $row['HOME_LOCN_ID'];
    }
}
