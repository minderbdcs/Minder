<?php
/**
 * Transaction_PINV
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



class Transaction_PINV Extends Transaction
{
    public static $orderNoMaxLen       = 10;
    public static $invoiceTypeMaxLen   = 2;
    public static $printerDeviceMaxLen = 2;
    public static $reportFormatMaxLen  = 2;
    public static $locationIdMaxLen    = 10;
    public static $whIdMaxLen          = 2;
    
    public static $calcFieldsPrecision = 3;
    
    public $orderNo         =   ''; //which Invoice is for
    public $invoiceType     =   ''; //invoice type
    public $printerDevice   =   ''; //Printer Device ID
    public $reportFormat    =   ''; //JasperReports Output type
    public $invoiceQty      =   ''; //copies of Invoices
    public $whId            =   ''; //WH_ID of Device ID
    public $locationId      =   ''; //LOCN_ID of the Device_ID of the PC creating/updating - not really important

    protected $referenceFields = array();
    
    public $_fieldsMap      = array();

    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode  = 'PINV';
    }
    
    /**
    * Example: S000000317|TI|PF|PDF|
    * PICK_ORDER # which Invoice is for plus INVOICE_TYPE - 'TI' =Tax Invoice, 'QT' = Quotation, 
    * 'PF||'=Printer Device ID or 'JR|PDF' - JasperReports Output type
    *
    * @return string
    */
    public function getObjectId()
    {
        //add some checks
        
        $this->_checkMaxLenLimit('orderNo', self::$orderNoMaxLen);
        $this->_checkMaxLenLimit('invoiceType', self::$invoiceTypeMaxLen);
        $this->_checkMaxLenLimit('printerDevice', self::$printerDeviceMaxLen);
        $this->_checkMaxLenLimit('reportFormat', self::$reportFormatMaxLen);
        
        return $this->orderNo . '|' . $this->invoiceType . '|' . $this->printerDevice . '|' . $this->reportFormat . '|';
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
                case 'PINV_STA':
                case 'PINV_STT':
                case 'PINV_FTA':
                case 'PINV_TAX':
                case 'PINV_DUE':
                case 'PINV_TOTAL':
                case 'PINV_PAID':
                case 'PINV_NTA':
                case 'PINV_STA2':
                case 'PINV_FEESA':
                case 'PINV_FEEST':
                case 'PINV_ADFPA':
                case 'PINV_ADFTA':
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
     * Returns copies of Invoices
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
     * The location for the PINVC transaction is LOCN_ID of the Device_ID of the PC creating/updating - not really important
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
        return '';
    }
    
    /**
     * @deprecated
     * @todo This method should be rewritten to match Transaction::parseResponse() (i.e. it should return Transaction_Response object)
     *
    * Parse PINV transaction response returned with doTransactionResponse() method.
    * 
    * @param string $transactionResponse - response string
    * @return array(
    *           0            => <RESPONSE_STRING>, 
    *           1            => <ACTION>, 
    *           2            => <INVOICE_NO>, 
    *           3            => <STATUS>, 
    *           'RESPONSE'   => <RESPONSE_STRING>, 
    *           'ACTION'     => <ACTION>, 
    *           'INVOICE_NO' => <INVOICE_NO>, 
    *           'STATUS'     => <STATUS>);
    */
    public function parseResponse($transactionResponse) {
        $pattern = '/^(\w+)\s([\w\d]+)\|(.*)$/';
        $matches = array();
        if (!preg_match($pattern, $transactionResponse, $matches))
            throw new Minder_Exception('Bad PINV transaction response: "' . $transactionResponse . '"');
        $matches = array_merge($matches, array('RESPONSE' => $matches[0], 'ACTION' => $matches[1], 'INVOICE_NO' => $matches[2], 'STATUS' => $matches[3]));
        return $matches;
    }
    
    
    public function __set($key, $value) { 
        $this->referenceFields[$key] = $value;
    }
    
    public function __get($key) {
        return isset($this->referenceFields[$key]) ? $this->referenceFields[$key] : null ;
    }
    
    
    public function calculateInvoiceTotals() {
        
    }
    
}