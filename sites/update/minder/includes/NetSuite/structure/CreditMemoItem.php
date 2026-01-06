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
 * CreditMemoItem map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_CreditMemoItem {
    public $item; //NetSuite_RecordRef
    public $orderLine;
    public $line;
    public $quantity;
    public $description;
    public $price; //NetSuite_RecordRef
    public $rate;
    public $amount;
    public $isTaxable;
    public $options; //NetSuite_CustomFieldList
    public $customFieldList; //NetSuite_CustomFieldList
    public $taxCode; //NetSuite_RecordRef
    public $taxRate1;
    public $taxRate2;
    public $tax1Amt;
    public $grossAmt;
    public $department; //NetSuite_RecordRef
    public $class; //NetSuite_RecordRef
    public $location; //NetSuite_RecordRef
    public $revRecSchedule; //NetSuite_RecordRef
    public $revRecStartDate;
    public $revRecTermInMonths;
    public $revRecEndDate;
    public $units; //NetSuite_RecordRef
    public $serialNumbers; //NetSuite_RecordRef
    public $deferRevRec;

    public function __construct(  NetSuite_RecordRef $item, $orderLine, $line, $quantity, $description, NetSuite_RecordRef $price, $rate, $amount, $isTaxable, NetSuite_CustomFieldList $options, NetSuite_CustomFieldList $customFieldList, NetSuite_RecordRef $taxCode, $taxRate1, $taxRate2, $tax1Amt, $grossAmt, NetSuite_RecordRef $department, NetSuite_RecordRef $class, NetSuite_RecordRef $location, NetSuite_RecordRef $revRecSchedule, $revRecStartDate, $revRecTermInMonths, $revRecEndDate, NetSuite_RecordRef $units, NetSuite_RecordRef $serialNumbers, $deferRevRec) {
        $this->item = $item;
        $this->orderLine = $orderLine;
        $this->line = $line;
        $this->quantity = $quantity;
        $this->description = $description;
        $this->price = $price;
        $this->rate = $rate;
        $this->amount = $amount;
        $this->isTaxable = $isTaxable;
        $this->options = $options;
        $this->customFieldList = $customFieldList;
        $this->taxCode = $taxCode;
        $this->taxRate1 = $taxRate1;
        $this->taxRate2 = $taxRate2;
        $this->tax1Amt = $tax1Amt;
        $this->grossAmt = $grossAmt;
        $this->department = $department;
        $this->class = $class;
        $this->location = $location;
        $this->revRecSchedule = $revRecSchedule;
        $this->revRecStartDate = $revRecStartDate;
        $this->revRecTermInMonths = $revRecTermInMonths;
        $this->revRecEndDate = $revRecEndDate;
        $this->units = $units;
        $this->serialNumbers = $serialNumbers;
        $this->deferRevRec = $deferRevRec;
    }
}?>