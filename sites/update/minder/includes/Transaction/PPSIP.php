<?php

//todo: add checks for field length

require_once 'Transaction.php';

class Transaction_PPSIP Extends Transaction
{
    public $productCode = '';
    public $specialInstructions = '';

    public function __construct()
    {
        $this->transCode      = 'PPSI';
        $this->transClass     = 'P';
    }
    
    public function getObjectId()
    {
        return $this->productCode;
    }

    
    
    public function getReference()
    {
        return substr($this->specialInstructions, 10, 40);
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
        return '';
    }


    public function getSubLocation()
    {
        return substr($this->specialInstructions, 0, 10);;
    }
    
    public function fillFromProdProfileRow($row) {
        $this->productCode = $row['PROD_ID'];
        $this->specialInstructions = $row['SPECIAL_INSTR'];
    }
}
