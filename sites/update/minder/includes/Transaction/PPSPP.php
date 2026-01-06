<?php

//todo: add checks for field length

require_once 'Transaction.php';

class Transaction_PPSPP Extends Transaction
{
    public $productCode     = '';
    
    public $palletCfgC      = ''; //VARCHAR(2) taken from PPPK transaction referrence, as it updates the same field
    public $permLevel       = ''; //VARCHAR(1) taken from PPPK transaction referrence, as it updates the same field
    public $togC            = ''; //VARCHAR(2) taken from PPPK transaction referrence, as it updates the same field
    public $netWeight       = 0; //assuming VARCHAR(6)
    public $maxQty          = 0; //assuming VARCHAR(8)
    public $minQty          = 0; //assuming VARCHAR(8)
    public $reorderQty      = 0; //assuming VARCHAR(8)
    public $maxIssueQty     = 0; //assuming VARCHAR(8)
    public $defaultIssueQty = 0; //assuming VARCHAR(8)

    public $salePrice       = 0;


    /**
    * PPSP transaction uses long parameter string wich splitetd into 
    * LOCATION, SUBLOCATION and REFERRENCE transactions fields.
    * Thim method build requirered parameter string.
    * 
    * @return string
    * 
    */
    protected function compileParameterString() {
        
        $parameterString = str_repeat(' ', 2 - strlen($this->palletCfgC)) . $this->palletCfgC . '|' .             
                           str_repeat(' ', 1 - strlen($this->permLevel)) . $this->permLevel . '|' .             
                           str_repeat(' ', 2 - strlen($this->togC)) . $this->togC . '|' .             
                           str_repeat(' ', 6 - strlen($this->netWeight)) . $this->netWeight . '|' .             //NET_WEIGHT
                           str_repeat(' ', 8 - strlen($this->maxQty)) . $this->maxQty . '|' .                   //MAX_QTY
                           str_repeat(' ', 8 - strlen($this->minQty)) . $this->minQty . '|' .                   //MIN_QTY
                           str_repeat(' ', 8 - strlen($this->reorderQty)) . $this->reorderQty . '|' .           //REORDER_QTY
                           str_repeat(' ', 8 - strlen($this->maxIssueQty)) . $this->maxIssueQty . '|' .         //MAX_ISSUE_QTY
                           str_repeat(' ', 8 - strlen($this->defaultIssueQty)) . $this->defaultIssueQty . '|';  //DEFAULT_ISSUE_QTY
        
        return $parameterString;
    }
    
    public function __construct()
    {
        $this->transCode      = 'PPSP';
        $this->transClass     = 'P';
    }
    
    public function getObjectId()
    {
        return $this->productCode;
    }

    
    
    public function getReference()
    {
        return substr($this->compileParameterString(), 20, 40);
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
        //look in RUN_TRANSACTION_PPSP: SALE_PRICE = QTY / 100, so adjusting to use in transaction
        return round($this->salePrice * 100);
    }


    public function getLocation()
    {
        return substr($this->compileParameterString(), 0, 10);
    }


    public function getSubLocation()
    {
        return substr($this->compileParameterString(), 10, 10);
    }
    
    public function fillFromProdProfileRow($row) {
        $this->productCode     = $row['PROD_ID'];
        $this->palletCfgInner  = $row['PALLET_CFG_INNER'];
        $this->permLevel       = $row['PERM_LEVEL'];
        $this->togC            = $row['TOG_C'];
        $this->netWeight       = empty($row['NET_WEIGHT'])        ? 0 : $row['NET_WEIGHT'];
        $this->maxQty          = empty($row['MAX_QTY'])           ? 0 : $row['MAX_QTY'];
        $this->minQty          = empty($row['MIN_QTY'])           ? 0 : $row['MIN_QTY'];
        $this->reorderQty      = empty($row['REORDER_QTY'])       ? 0 : $row['REORDER_QTY'];
        $this->maxIssueQty     = empty($row['MAX_ISSUE_QTY'])     ? 0 : $row['MAX_ISSUE_QTY'];
        $this->defaultIssueQty = empty($row['DEFAULT_ISSUE_QTY']) ? 0 : $row['DEFAULT_ISSUE_QTY'];
        $this->salePrice       = empty($row['SALE_PRICE'])        ? 0 : $row['SALE_PRICE'];
    }
}
