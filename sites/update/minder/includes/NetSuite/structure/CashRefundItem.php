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
 * CashRefundItem map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_CashRefundItem {
    public $item; //NetSuite_RecordRef
    public $line;
    public $orderLine;
    public $quantity;
    public $units; //NetSuite_RecordRef
    public $binNumbers;
    public $serialNumbers;
    public $description;
    public $price; //NetSuite_RecordRef
    public $rate;
    public $amount;
    public $taxCode; //NetSuite_RecordRef
    public $taxRate1;
    public $taxRate2;
    public $tax1Amt;
    public $grossAmt;
    public $isTaxable;
    public $options; //NetSuite_CustomFieldList
    public $department; //NetSuite_RecordRef
    public $class; //NetSuite_RecordRef
    public $location; //NetSuite_RecordRef
    public $revRecSchedule; //NetSuite_RecordRef
    public $revRecStartDate;
    public $revRecEndDate;
    public $customFieldList; //NetSuite_CustomFieldList

    public function __construct(  NetSuite_RecordRef $item, $line, $orderLine, $quantity, NetSuite_RecordRef $units, $binNumbers, $serialNumbers, $description, NetSuite_RecordRef $price, $rate, $amount, NetSuite_RecordRef $taxCode, $taxRate1, $taxRate2, $tax1Amt, $grossAmt, $isTaxable, NetSuite_CustomFieldList $options, NetSuite_RecordRef $department, NetSuite_RecordRef $class, NetSuite_RecordRef $location, NetSuite_RecordRef $revRecSchedule, $revRecStartDate, $revRecEndDate, NetSuite_CustomFieldList $customFieldList) {
        $this->item = $item;
        $this->line = $line;
        $this->orderLine = $orderLine;
        $this->quantity = $quantity;
        $this->units = $units;
        $this->binNumbers = $binNumbers;
        $this->serialNumbers = $serialNumbers;
        $this->description = $description;
        $this->price = $price;
        $this->rate = $rate;
        $this->amount = $amount;
        $this->taxCode = $taxCode;
        $this->taxRate1 = $taxRate1;
        $this->taxRate2 = $taxRate2;
        $this->tax1Amt = $tax1Amt;
        $this->grossAmt = $grossAmt;
        $this->isTaxable = $isTaxable;
        $this->options = $options;
        $this->department = $department;
        $this->class = $class;
        $this->location = $location;
        $this->revRecSchedule = $revRecSchedule;
        $this->revRecStartDate = $revRecStartDate;
        $this->revRecEndDate = $revRecEndDate;
        $this->customFieldList = $customFieldList;
    }
}?>