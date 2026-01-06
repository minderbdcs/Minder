<?php


require_once 'Transaction.php';


class Transaction_PPPDP Extends Transaction
{
    public $productType;
    public $uom;
    public $issueUom;
    public $orderUom;
    public $stockFlag = 'Y';
    public $ssnTrackFlag = 'Y';
    public $prodId;
    public $shortDesc;
    public $standardCost = 0;

    public function __construct()
    {
        $this->transCode      = 'PPPD';
        $this->transClass     = 'P';
    }

    
    
    public function getObjectId()
    {
        return $this->prodId;
    }

    
    
    public function getReference()
    {
        return substr($this->shortDesc, 10);
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
        $tmpQty = round($this->standardCost * 100);
        return empty($tmpQty) ? '0' : $tmpQty;
    }


    public function getLocation()
    {
        return sprintf(
                '%-2s%-2s%-2s%-2s%-1s%-1s',
                substr($this->productType, 0, 2),
                substr($this->uom, 0, 2),
                substr($this->issueUom, 0, 2),
                substr($this->orderUom, 0, 2),
                substr($this->stockFlag, 0, 1),
                substr($this->ssnTrackFlag, 0, 1)
        );
    }

    public function getSubLocation()
    {
        return substr($this->shortDesc, 0, 10);
    }
    
    public function fillFromProdProfileRow($row) {
        $this->standardCost = empty($row['STANDARD_COST']) ? 0 : $row['STANDARD_COST']; // look in RUN_TRANSACTION_PPPD
        $this->prodId       = $row['PROD_ID'];
        $this->productType  = $row['PROD_TYPE'];
        $this->uom          = $row['UOM'];
        $this->issueUom     = $row['ISSUE_UOM']; //should be moved to another transaction, but still updating in RUN_TRANSACTION_PPPD
        $this->orderUom     = $row['ORDER_UOM']; //should be moved to another transaction, but still updating in RUN_TRANSACTION_PPPD
        $this->stockFlag    = $row['STOCK'];
        $this->ssnTrackFlag = $row['SSN_TRACK'];
        $this->shortDesc    = $row['SHORT_DESC'];
    }
}
