<?php

//todo: add checks for field length

require_once 'Transaction.php';

class Transaction_PPSNP Extends Transaction
{
    public $productCode     = '';
    public $supplierNo      = '';
    public $supplierPrefer  = '';
    public $supplierId      = '';
    public $supplierProdId  = '';

    public function __construct()
    {
        $this->transCode      = 'PPSN';
        $this->transClass     = 'P';
    }
    
    public function getObjectId()
    {
        return $this->productCode;
    }

    
    
    public function getReference()
    {
        return $this->supplierProdId;
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
        return $this->supplierNo;
    }


    public function getLocation()
    {
        return $this->supplierPrefer;
    }


    public function getSubLocation()
    {
        return $this->supplierId;
    }
    
    public function fillFromProdProfileRow($row) {
        $this->productCode     = $row['PROD_ID'];
        $this->supplierPrefer  = $row['SUPPLIER_PREFER'];

        switch (intval($this->supplierNo)) {
            case 1:
                $this->supplierId     = $row['SUPPLIER_NO1'];
                $this->supplierProdId = $row['SUPPLIER_NO1_PROD'];
                break;
            case 2:
                $this->supplierId     = $row['SUPPLIER_NO2'];
                $this->supplierProdId = $row['SUPPLIER_NO2_PROD'];
                break;
            case 3:
                $this->supplierId     = $row['SUPPLIER_NO3'];
                $this->supplierProdId = $row['SUPPLIER_NO3_PROD'];
                break;
            default:
                throw new Minder_Exception('Bad SUPPLIER_NO: "' . $this->supplierNo . '". Valid are: 1, 2, 3.');
        }
    }
}
