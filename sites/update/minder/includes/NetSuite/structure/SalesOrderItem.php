<?php
/**
 * Minder
 *
 * PHP version 5.2.4
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 *
 */

/**
 * SalesOrderItem map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_SalesOrderItem {
    public $item; //NetSuite_RecordRef
    public $quantityAvailable;
    public $quantityOnHand;
    public $quantity;
    public $units; //NetSuite_RecordRef
    public $description;
    public $price; //NetSuite_RecordRef
    public $rate;
    public $serialNumbers; //NetSuite_RecordRef
    public $amount;
    public $isTaxable;
    public $commitInventory;
    public $licenseCode;
    public $options; //NetSuite_CustomFieldList
    public $department; //NetSuite_RecordRef
    public $class; //NetSuite_RecordRef
    public $location; //NetSuite_RecordRef
    public $createPo;
    public $createdPo; //NetSuite_RecordRef
    public $altSalesAmt;
    public $poVendor; //NetSuite_RecordRef
    public $poCurrency;
    public $poRate;
    public $revRecSchedule; //NetSuite_RecordRef
    public $revRecStartDate;
    public $revRecTermInMonths;
    public $revRecEndDate;
    public $deferRevRec;
    public $isClosed;
    public $billingSchedule; //NetSuite_RecordRef
    public $grossAmt;
    public $line;
    public $percentComplete;
    public $quantityBackOrdered;
    public $quantityBilled;
    public $quantityCommitted;
    public $quantityFulfilled;
    public $quantityPacked;
    public $quantityPicked;
    public $tax1Amt;
    public $taxCode; //NetSuite_RecordRef
    public $taxRate1;
    public $taxRate2;
    public $customFieldList; //NetSuite_CustomFieldList

    public function __construct(  NetSuite_RecordRef $item, $quantityAvailable, $quantityOnHand, $quantity, NetSuite_RecordRef $units, $description, NetSuite_RecordRef $price, $rate, NetSuite_RecordRef $serialNumbers, $amount, $isTaxable, $commitInventory, $licenseCode, NetSuite_CustomFieldList $options, NetSuite_RecordRef $department, NetSuite_RecordRef $class, NetSuite_RecordRef $location, $createPo, NetSuite_RecordRef $createdPo, $altSalesAmt, NetSuite_RecordRef $poVendor, $poCurrency, $poRate, NetSuite_RecordRef $revRecSchedule, $revRecStartDate, $revRecTermInMonths, $revRecEndDate, $deferRevRec, $isClosed, NetSuite_RecordRef $billingSchedule, $grossAmt, $line, $percentComplete, $quantityBackOrdered, $quantityBilled, $quantityCommitted, $quantityFulfilled, $quantityPacked, $quantityPicked, $tax1Amt, NetSuite_RecordRef $taxCode, $taxRate1, $taxRate2, NetSuite_CustomFieldList $customFieldList) {
        $this->item = $item;
        $this->quantityAvailable = $quantityAvailable;
        $this->quantityOnHand = $quantityOnHand;
        $this->quantity = $quantity;
        $this->units = $units;
        $this->description = $description;
        $this->price = $price;
        $this->rate = $rate;
        $this->serialNumbers = $serialNumbers;
        $this->amount = $amount;
        $this->isTaxable = $isTaxable;
        $this->commitInventory = $commitInventory;
        $this->licenseCode = $licenseCode;
        $this->options = $options;
        $this->department = $department;
        $this->class = $class;
        $this->location = $location;
        $this->createPo = $createPo;
        $this->createdPo = $createdPo;
        $this->altSalesAmt = $altSalesAmt;
        $this->poVendor = $poVendor;
        $this->poCurrency = $poCurrency;
        $this->poRate = $poRate;
        $this->revRecSchedule = $revRecSchedule;
        $this->revRecStartDate = $revRecStartDate;
        $this->revRecTermInMonths = $revRecTermInMonths;
        $this->revRecEndDate = $revRecEndDate;
        $this->deferRevRec = $deferRevRec;
        $this->isClosed = $isClosed;
        $this->billingSchedule = $billingSchedule;
        $this->grossAmt = $grossAmt;
        $this->line = $line;
        $this->percentComplete = $percentComplete;
        $this->quantityBackOrdered = $quantityBackOrdered;
        $this->quantityBilled = $quantityBilled;
        $this->quantityCommitted = $quantityCommitted;
        $this->quantityFulfilled = $quantityFulfilled;
        $this->quantityPacked = $quantityPacked;
        $this->quantityPicked = $quantityPicked;
        $this->tax1Amt = $tax1Amt;
        $this->taxCode = $taxCode;
        $this->taxRate1 = $taxRate1;
        $this->taxRate2 = $taxRate2;
        $this->customFieldList = $customFieldList;
    }
}?>