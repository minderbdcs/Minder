<?php
/**
 * Transaction_PINVC
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



class Transaction_PINVC Extends Transaction_PINV
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

    protected $subTotalTax      = 0;
    protected $subTotalAmount   = 0;

    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode  = 'PINV';
        $this->transClass = 'C';
       
    }

    public function addPickItemDetail($pidRow) {
        $this->subTotalTax      += $pidRow['TAX_AMOUNT'];
        $this->subTotalAmount   += $pidRow['LINE_TOTAL'];
        return;
    }

    protected function _calculateInvoice($podRow, $subTotalAmount, $subTotalTax, $firstInvoice) {
        $result = array();
        $freightEveryInvoice = (isset($podRow['FREIGHT_EVERY_INVOICE']) && strtoupper($podRow['FREIGHT_EVERY_INVOICE'] == 'T'));

        if ($firstInvoice) {
            $result['OTHER_NUM1']           = (empty($podRow['OTHER_NUM1'])) ? 0 : $podRow['OTHER_NUM1'];
            $result['OTHER_NUM2']           = (empty($podRow['OTHER_NUM2'])) ? 0 : $podRow['OTHER_NUM2'];
            $result['AMOUNT_PAID']          = (empty($podRow['AMOUNT_PAID'])) ? 0 : $podRow['AMOUNT_PAID'];
            $result['FREIGHT']              = (empty($podRow['FREIGHT'])) ? 0 : $podRow['FREIGHT'];
            $result['ADMIN_FEE_AMOUNT']     = (empty($podRow['ADMIN_FEE_AMOUNT'])) ? 0 : $podRow['ADMIN_FEE_AMOUNT'];
        } else {
            $result['OTHER_NUM1']  = 0;
            $result['OTHER_NUM2']  = 0;
            $result['AMOUNT_PAID'] = 0;
            if ($freightEveryInvoice) {
                $result['FREIGHT'] = (empty($podRow['FREIGHT'])) ? 0 : $podRow['FREIGHT'];
            } else {
                $result['FREIGHT'] = 0;
            }
            $result['ADMIN_FEE_AMOUNT'] = 0;
        }

        $result['TAX_RATE']         = (empty($podRow['TAX_RATE'])) ? 0 : $podRow['TAX_RATE'];
        $result['ADMIN_FEE_RATE']   = (empty($podRow['ADMIN_FEE_RATE'])) ? 0 : $podRow['ADMIN_FEE_RATE'];

        /*
         * ---------------- start copied form UPDATE_PICK_ORDER_TIME trigger -----------
         */
        $result['FEES_AMOUNT']              = round($result['FREIGHT'] + $result['ADMIN_FEE_AMOUNT'] + $result['OTHER_NUM1'] + $result['OTHER_NUM2'], 2);
        $result['FEES_AMOUNT_TAX_AMOUNT']   = round($result['FEES_AMOUNT'] * $result['TAX_RATE'] / 100, 2);
        $result['TAX_AMOUNT']               = round($subTotalTax + $result['FEES_AMOUNT_TAX_AMOUNT'], 2);
        $result['FREIGHT_TAX_AMOUNT']       = round($result['FREIGHT'] * $result['TAX_RATE'] / 100, 2);
        $result['ADMIN_FEE_TAX_AMOUNT']     = round($result['ADMIN_FEE_AMOUNT'] * $result['TAX_RATE'] / 100, 2);
        $result['SUM_TOTAL_AMOUNT']         = round($subTotalAmount + $result['FEES_AMOUNT'] + $result['TAX_AMOUNT'], 2);
        $result['ADMIN_FEE_PERCENT_AMOUNT'] = round($result['SUM_TOTAL_AMOUNT'] * $result['ADMIN_FEE_RATE'] / 100, 2);
        $result['NET_TOTAL_AMOUNT']         = round($result['SUM_TOTAL_AMOUNT'] + $result['ADMIN_FEE_PERCENT_AMOUNT'], 2);
        $result['DUE_AMOUNT']               = round($result['NET_TOTAL_AMOUNT'] - $result['AMOUNT_PAID'], 2);
        /*
         * ---------------- end copied form UPDATE_PICK_ORDER_TIME trigger -----------
         */

        return $result;
    }

    /**
    * Fills transaction reference values using PICK_ORDER record.
    * Also fill orderNo as it is taken from PICK_ORDER
    * 
    * @param array $podRow - array with pick item details
    */
    public function fillValuesFromPickOrderDetail($podRow) {
        $invoice = $this->_calculateInvoice($podRow, $this->subTotalAmount, $this->subTotalTax, empty($podRow['LAST_INVOICE_NO']));

        $this->__set('PINV_FTA', $invoice['FREIGHT_TAX_AMOUNT']);
        $this->__set('PINV_AFA', $invoice['ADMIN_FEE_AMOUNT']);
        $this->__set('PINV_AFR', $invoice['ADMIN_FEE_RATE']);
        $this->__set('PINV_TAXRATE', $invoice['TAX_RATE']);
        $this->__set('PINV_FREIGHT', $invoice['FREIGHT']);
        $this->__set('PINV_ADFTA', $invoice['ADMIN_FEE_TAX_AMOUNT']);
        $this->__set('PINV_FEESA', $invoice['FEES_AMOUNT']);
        $this->__set('PINV_FEEST', $invoice['FEES_AMOUNT_TAX_AMOUNT']);
        $this->__set('PINV_TAX', $invoice['TAX_AMOUNT']);
        $this->__set('PINV_ADFPA', $invoice['ADMIN_FEE_PERCENT_AMOUNT']);
        $this->__set('PINV_TOTAL', $invoice['NET_TOTAL_AMOUNT']);
        $this->__set('PINV_NTA', $invoice['NET_TOTAL_AMOUNT']);
        $this->__set('PINV_STA2', $invoice['SUM_TOTAL_AMOUNT']);
        $this->__set('PINV_PAID', $invoice['AMOUNT_PAID']);
        $this->__set('PINV_DUE', $invoice['DUE_AMOUNT']);
        $this->__set('PINV_STA', $this->subTotalAmount);
        $this->__set('PINV_STT', $this->subTotalTax);

//        $this->__set('PINV_AWB', ''); //not sure where to get this from
        $this->__set('PINV_COMPANY', $podRow['COMPANY_ID']);
        $this->__set('PINV_DESPID', $podRow['DESPATCH_ID']);
//        $this->__set('PINV_IS', ''); //not sure where to get this from
        $this->__set('PINV_LLAFC', $podRow['LEGACY_LEDGER_ADMIN_FEE_CODE']);
        $this->__set('PINV_LLDC', $podRow['LEGACY_LEDGER_DEPOSIT_CODE']);
        $this->__set('PINV_LLFC', $podRow['LEGACY_LEDGER_FREIGHT_CODE']);
        $this->__set('PINV_SHIP', $podRow['SHIPPING_METHOD']);
        $this->__set('PINV_SHIP_METH', $podRow['SHIPPING_METHOD']);
        $this->__set('PINV_WH', $podRow['WH_ID']);

        $this->orderNo = $podRow['PICK_ORDER'];
        
        return;
    }

    public function setAwbConsignmentNo($value) {
        $this->__set('PINV_AWB', $value);
    }
}
