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
 * InvoiceItem map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_InvoiceItem {
    public $item; //NetSuite_RecordRef
    public $line;
    public $description;
    public $amount;
    public $isTaxable;
    public $options; //NetSuite_CustomFieldList
    public $deferRevRec;
    public $quantity;
    public $currentPercent;
    public $units; //NetSuite_RecordRef
    public $serialNumbers;
    public $binNumbers;
    public $price; //NetSuite_RecordRef
    public $rate;
    public $percentComplete;
    public $quantityOnHand;
    public $quantityAvailable;
    public $quantityOrdered;
    public $quantityRemaining;
    public $quantityFulfilled;
    public $amountOrdered;
    public $department; //NetSuite_RecordRef
    public $orderLine;
    public $licenseCode;
    public $class; //NetSuite_RecordRef
    public $location; //NetSuite_RecordRef
    public $revRecSchedule; //NetSuite_RecordRef
    public $revRecStartDate;
    public $revRecEndDate;
    public $grossAmt;
    public $tax1Amt;
    public $taxCode; //NetSuite_RecordRef
    public $taxRate1;
    public $taxRate2;
    public $customFieldList; //NetSuite_CustomFieldList

    public function __construct(  NetSuite_RecordRef $item, $line, $description, $amount, $isTaxable, NetSuite_CustomFieldList $options, $deferRevRec, $quantity, $currentPercent, NetSuite_RecordRef $units, $serialNumbers, $binNumbers, NetSuite_RecordRef $price, $rate, $percentComplete, $quantityOnHand, $quantityAvailable, $quantityOrdered, $quantityRemaining, $quantityFulfilled, $amountOrdered, NetSuite_RecordRef $department, $orderLine, $licenseCode, NetSuite_RecordRef $class, NetSuite_RecordRef $location, NetSuite_RecordRef $revRecSchedule, $revRecStartDate, $revRecEndDate, $grossAmt, $tax1Amt, NetSuite_RecordRef $taxCode, $taxRate1, $taxRate2, NetSuite_CustomFieldList $customFieldList) {
        $this->item = $item;
        $this->line = $line;
        $this->description = $description;
        $this->amount = $amount;
        $this->isTaxable = $isTaxable;
        $this->options = $options;
        $this->deferRevRec = $deferRevRec;
        $this->quantity = $quantity;
        $this->currentPercent = $currentPercent;
        $this->units = $units;
        $this->serialNumbers = $serialNumbers;
        $this->binNumbers = $binNumbers;
        $this->price = $price;
        $this->rate = $rate;
        $this->percentComplete = $percentComplete;
        $this->quantityOnHand = $quantityOnHand;
        $this->quantityAvailable = $quantityAvailable;
        $this->quantityOrdered = $quantityOrdered;
        $this->quantityRemaining = $quantityRemaining;
        $this->quantityFulfilled = $quantityFulfilled;
        $this->amountOrdered = $amountOrdered;
        $this->department = $department;
        $this->orderLine = $orderLine;
        $this->licenseCode = $licenseCode;
        $this->class = $class;
        $this->location = $location;
        $this->revRecSchedule = $revRecSchedule;
        $this->revRecStartDate = $revRecStartDate;
        $this->revRecEndDate = $revRecEndDate;
        $this->grossAmt = $grossAmt;
        $this->tax1Amt = $tax1Amt;
        $this->taxCode = $taxCode;
        $this->taxRate1 = $taxRate1;
        $this->taxRate2 = $taxRate2;
        $this->customFieldList = $customFieldList;
    }
}?>