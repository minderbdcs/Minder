<?php
/**
 * Transaction_PILNC
 *
 * PHP version 5.2.4
 *
 * @category  Minder
 * @package   Minder
 *
 */

/**
 * Include the definition of the Transaction base class
 */
require_once 'Transaction.php';



class Transaction_PILNC Extends Transaction
{
    public static $orderNoMaxLen       = 10;
    public static $invoiceNoMaxLen     = 10;
    public static $locationIdMaxLen    = 10;
    public static $whIdMaxLen          = 2;
    
    public static $calcFieldsPrecision = 3;
    
    public $orderNo         =   ''; //which Invoice is for
    public $invoiceNo       =   ''; //PICK_INVOICE.INVOICE_NO
    public $invoiceQty      =   ''; //Invoice Qty = PICK_ITEM_DETAIL.QTY_PICKED helps with future Reporting
    public $whId            =   ''; //WH_ID of Device ID
    public $locationId      =   ''; //LOCN_ID of the Device_ID of the PC creating/updating - not really important
    public $invoiceType     =   '';
    
    protected $referenceFields = array();
    
    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode  = 'PILN';
        $this->transClass = 'C';
       
    }
    
    /**
    * Example: S000000317|R100001
    * PICK_ORDER.PICK_ORDER | PICK_INVOICE.INVOICE_NO
    *
    * @return string
    */
    public function getObjectId()
    {
        //add some checks
        
        $this->_checkMaxLenLimit('orderNo', self::$orderNoMaxLen);
        $this->_checkMaxLenLimit('invoiceNo', self::$invoiceNoMaxLen);
        
        return $this->orderNo . '|' . $this->invoiceNo;
    }

    /**
     *
     * @return string
     */
    public function getReference()
    {
        $reference = '';
        
        foreach ($this->referenceFields as $key => $field) {
            switch ($key) {
                case 'PILN_TOTALSALE':
                case 'PILN_TAXRATE':
                case 'PILN_TOTALCOST':
                    $field = (empty($field)) ? 0 : $field;
                    $field = round($field, self::$calcFieldsPrecision);
                    break;
            }
            
            $reference .= $key . '=' . $field . '|';
        }
        
        return $reference;
    }

    /**
     * Returns the quantity for inserting into the database
     *
     * Invoice Qty = PICK_ITEM_DETAIL.QTY_PICKED helps with future Reporting
     *
     * @return string
     */
    public function getQuantity()
    {
        return $this->invoiceQty;
    }
    
    /**
     * Returns the location for inserting into the database
     *
     * The location for the PINVC transaction is WH_ID of Device ID + LOCN_ID of the Device_ID of the PC - not important
     *
     * @return string
     */
    public function getLocation()
    {
        $this->_checkMaxLenLimit('whId', self::$locationIdMaxLen);
        $this->_checkMaxLenLimit('locationId', self::$locationIdMaxLen);
        
        return $this->whId . $this->locationId;
    }

    /**
     * Returns the sublocation for inserting into the database
     *
     * The sublocation for the PINVC transaction is empty string
     *
     * @return string
     */
    public function getSubLocation()
    {
        return $this->invoiceType;
    }
    
    public function __set($key, $value) {
        $this->referenceFields[$key] = $value;
    }
    
    public function __get($key) {
        return isset($this->referenceFields[$key]) ? $this->referenceFields[$key] : null ;
    }
    
    
    /**
    * Fills transaction reference values using PICK_ITEM_DETAIL record.
    * Also fill invoiceQty as it is taken from PICK_ITEM_DETAIL
    * 
    * @param array $pidRow - array with pick item details
    */
    public function fillValuesFromPickItemDetail($pidRow) {
        
        $pilnId   = (empty($pidRow['PROD_ID']) ? $pidRow['SSN_ID'] : $pidRow['PROD_ID'] );
        $pilnType = '';


        switch ($pilnId) {
            case $pidRow['PROD_ID']: 
                $pilnType = 'P';
                break;
            case $pidRow['SSN_ID']: 
                $pilnType = 'I';
                break;
        }
        
        $pilnUnitSale  = (empty($pidRow['SALE_PRICE'])) ? 0 : $pidRow['SALE_PRICE'];
        $pilnDiscount  = (empty($pidRow['DISCOUNT'])) ? 0 : $pidRow['DISCOUNT'];
        $pilnQtyPicked = (empty($pidRow['QTY_PICKED'])) ? 0 : $pidRow['QTY_PICKED'];
        
        $pilnTotalSale = $pilnUnitSale * (100 - $pilnDiscount) * $pilnQtyPicked / 100;
        $pilnTaxRate   = (empty($pidRow['TAX_RATE'])) ? 0 : $pidRow['TAX_RATE'];
        $pilnTaxAmount = $pilnUnitSale * (100 - $pilnDiscount) * $pilnQtyPicked * $pilnTaxRate / 10000;
        $pilnUnitCost  = (empty($pidRow['PROD_ID'])) ?  $pidRow['PURCHASE_PRICE'] : $pidRow['STANDARD_COST'];
        $pilnUnitCost  = (empty($pilnUnitCost)) ? 0 : $pilnUnitCost;
        $pilnTotalCost = $pilnUnitCost * $pilnQtyPicked;
        $pilnTotalTax  = $pilnTotalCost * $pilnTaxRate / 100;
        $pilnDefaultUnitSale = (empty($pidRow['PROD_PROFILE_SALE_PRICE'])) ? 0 : $pidRow['PROD_PROFILE_SALE_PRICE'];
        
//        $this->__set('PILN_BOQTY', ''); //not sure where to get this from
        $this->__set('PILN_DISC', $pilnDiscount);
        $this->__set('PILN_ID', $pilnId);
        $this->__set('PILN_LLC', $pidRow['LEGACY_LEDGER_SALE_CODE']);
        $this->__set('PILN_LTTAX', $pilnTotalTax);
        $this->__set('PILN_ORD_QTY', $pidRow['PICK_ORDER_QTY']);
//        $this->__set('PILN_PICKTYPE', ''); //not sure where to get this from
        $this->__set('PILN_PLN', $pidRow['PICK_LABEL_NO']);
        $this->__set('PILN_QTY', $pilnQtyPicked);
        $this->__set('PILN_SALE_PRICE', $pilnUnitSale);
        $this->__set('PILN_TAX', $pilnTaxAmount);
        $this->__set('PILN_TAXRATE', $pilnTaxRate);
        $this->__set('PILN_TOTALSALE', $pilnTotalSale);
        $this->__set('PILN_TOTALCOST', $pilnTotalCost);
        $this->__set('PILN_TYPE', $pilnType);
        $this->__set('PILN_UNITSALE', $pilnDefaultUnitSale);
        $this->__set('PILN_UNITCOST', $pilnUnitCost);
        $this->__set('PILN_WC', $pidRow['WARRANTY_TERM']);

        
        $this->invoiceQty = $pilnQtyPicked;
        $this->orderNo    = $pidRow['PICK_ORDER'];
        
        return;
    }
}
