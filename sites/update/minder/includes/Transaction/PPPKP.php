<?php

//todo: add checks for field length

require_once 'Transaction.php';

class Transaction_PPPKP Extends Transaction
{
    public $productCode       = '';
    
    public $togC              = ''; //VARCHAR(2)
    public $permLevel         = ''; //VARCHAR(1)
    public $palletCfgInner    = ''; //VARCHAR(2)
    public $palletCfgC        = ''; //VARCHAR(2)
    public $issue             = ''; //VARCHAR(10)
    public $issueUom          = ''; //VARCHAR(2)
    public $netWeight         = ''; //VARCHAR(10)
                                    //??? NET_WEIGHT also updated in PPSP transaction (as specified in "Visio-Product_Details.pdf" and "Visio-WM_TRANSACTIONS_231008.pdf")
                                    //ask Glen about this collision
    public $netWeightUom      = ''; //VARCHAR(2)
    public $issuePerInnerUnit = ''; //VARCHAR(10)
    public $innerUom          = ''; //VARCHAR(2)
    public $innerWeight       = ''; //VARCHAR(10)
    public $innerWeightUom    = ''; //VARCHAR(2)
    public $issuePerOrderUnit = ''; //VARCHAR(10)
    public $orderUom          = ''; //VARCHAR(2)
    public $orderWeightUom    = ''; //VARCHAR(2)
    
    public $orderWeight       = ''; //INTEGER



    /**
    * PPPK transaction uses long parameter string wich splitetd into 
    * LOCATION, SUBLOCATION and REFERRENCE transactions fields.
    * Thim method build requirered parameter string.
    * 
    * @return string
    * 
    */
    protected function compileParameterString() {
        
        $parameterString = str_repeat(' ', 2 - strlen($this->togC)) . $this->togC . '|' .             
                           $this->permLevel . '|' .             
                           str_repeat(' ', 2 - strlen($this->palletCfgInner)) . $this->palletCfgInner . '|' .             
                           str_repeat(' ', 2 - strlen($this->palletCfgC)) . $this->palletCfgC . '|' .             
                           str_repeat(' ', 10 - strlen($this->issue)) . $this->issue . '|' .             
                           str_repeat(' ', 2 - strlen($this->issueUom)) . $this->issueUom . '|' .             
                           str_repeat(' ', 10 - strlen($this->netWeight)) . $this->netWeight . '|' .             
                           str_repeat(' ', 2 - strlen($this->netWeightUom)) . $this->netWeightUom . '|' .             
                           str_repeat(' ', 10 - strlen($this->issuePerInnerUnit)) . $this->issuePerInnerUnit . '|' .             
                           str_repeat(' ', 2 - strlen($this->innerUom)) . $this->innerUom . '|' .             
                           str_repeat(' ', 10 - strlen($this->innerWeight)) . $this->innerWeight . '|' .             
                           str_repeat(' ', 2 - strlen($this->innerWeightUom)) . $this->innerWeightUom . '|' .             
                           str_repeat(' ', 10 - strlen($this->issuePerOrderUnit)) . $this->issuePerOrderUnit . '|' .             
                           str_repeat(' ', 2 - strlen($this->orderUom)) . $this->orderUom . '|' .
                           str_repeat(' ', 2 - strlen($this->orderWeightUom)) . $this->orderWeightUom . '|';

        return $parameterString;
    }
    
    public function __construct()
    {
        $this->transCode      = 'PPPK';
        $this->transClass     = 'P';
    }
    
    public function getObjectId()
    {
        return $this->productCode;
    }

    
    
    public function getReference()
    {
        return substr($this->compileParameterString(), 20, 255);
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
        return $this->orderWeight;
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
        $this->productCode       = $row['PROD_ID'];
        $this->togC              = $row['TOG_C'];
        $this->permLevel         = $row['PERM_LEVEL'];
        $this->palletCfgInner    = $row['PALLET_CFG_INNER'];
        $this->palletCfgC        = $row['PALLET_CFG_C'];
        $this->issue             = $row['ISSUE'];
        $this->issueUom          = $row['ISSUE_UOM'];
        $this->netWeight         = $row['NET_WEIGHT'];
        $this->netWeightUom      = $row['NET_WEIGHT_UOM'];
        $this->issuePerInnerUnit = $row['ISSUE_PER_INNER_UNIT'];
        $this->innerUom          = $row['INNER_UOM'];
        $this->innerWeight       = $row['INNER_WEIGHT'];
        $this->innerWeightUom    = $row['INNER_WEIGHT_UOM'];
        $this->issuePerOrderUnit = $row['ISSUE_PER_ORDER_UNIT'];
        $this->orderUom          = $row['ORDER_UOM'];
        $this->orderWeightUom    = $row['ORDER_WEIGHT_UOM'];
        $this->orderWeight       = $row['ORDER_WEIGHT'];
    }
}
