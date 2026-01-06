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
 * CashSaleItem map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_CashSaleItem {
    public $item; //NetSuite_RecordRef
    public $line;
    public $quantityAvailable;
    public $quantityOnHand;
    public $quantity;
    public $units; //NetSuite_RecordRef
    public $serialNumbers;
    public $binNumbers;
    public $description;
    public $price; //NetSuite_RecordRef
    public $rate;
    public $amount;
    public $orderLine;
    public $licenseCode;
    public $isTaxable;
    public $options; //NetSuite_CustomFieldList
    public $deferRevRec;
    public $currentPercent;
    public $department; //NetSuite_RecordRef
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

    public function __construct(  NetSuite_RecordRef $item, $line, $quantityAvailable, $quantityOnHand, $quantity, NetSuite_RecordRef $units, $serialNumbers, $binNumbers, $description, NetSuite_RecordRef $price, $rate, $amount, $orderLine, $licenseCode, $isTaxable, NetSuite_CustomFieldList $options, $deferRevRec, $currentPercent, NetSuite_RecordRef $department, NetSuite_RecordRef $class, NetSuite_RecordRef $location, NetSuite_RecordRef $revRecSchedule, $revRecStartDate, $revRecEndDate, $grossAmt, $tax1Amt, NetSuite_RecordRef $taxCode, $taxRate1, $taxRate2, NetSuite_CustomFieldList $customFieldList) {
        $this->item = $item;
        $this->line = $line;
        $this->quantityAvailable = $quantityAvailable;
        $this->quantityOnHand = $quantityOnHand;
        $this->quantity = $quantity;
        $this->units = $units;
        $this->serialNumbers = $serialNumbers;
        $this->binNumbers = $binNumbers;
        $this->description = $description;
        $this->price = $price;
        $this->rate = $rate;
        $this->amount = $amount;
        $this->orderLine = $orderLine;
        $this->licenseCode = $licenseCode;
        $this->isTaxable = $isTaxable;
        $this->options = $options;
        $this->deferRevRec = $deferRevRec;
        $this->currentPercent = $currentPercent;
        $this->department = $department;
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