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
 * ReturnAuthorizationItem map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_ReturnAuthorizationItem {
    public $item; //NetSuite_RecordRef
    public $orderLine;
    public $line;
    public $quantity;
    public $units; //NetSuite_RecordRef
    public $description;
    public $serialNumbers;
    public $price; //NetSuite_RecordRef
    public $rate;
    public $amount;
    public $options; //NetSuite_CustomFieldList
    public $revRecTermInMonths;
    public $deferRevRec;
    public $isClosed;
    public $department; //NetSuite_RecordRef
    public $class; //NetSuite_RecordRef
    public $location; //NetSuite_RecordRef
    public $revRecSchedule; //NetSuite_RecordRef
    public $revRecStartDate;
    public $revRecEndDate;
    public $taxCode; //NetSuite_RecordRef
    public $taxRate1;
    public $taxRate2;
    public $tax1Amt;
    public $grossAmt;
    public $isTaxable;
    public $customFieldList; //NetSuite_CustomFieldList

    public function __construct(  NetSuite_RecordRef $item, $orderLine, $line, $quantity, NetSuite_RecordRef $units, $description, $serialNumbers, NetSuite_RecordRef $price, $rate, $amount, NetSuite_CustomFieldList $options, $revRecTermInMonths, $deferRevRec, $isClosed, NetSuite_RecordRef $department, NetSuite_RecordRef $class, NetSuite_RecordRef $location, NetSuite_RecordRef $revRecSchedule, $revRecStartDate, $revRecEndDate, NetSuite_RecordRef $taxCode, $taxRate1, $taxRate2, $tax1Amt, $grossAmt, $isTaxable, NetSuite_CustomFieldList $customFieldList) {
        $this->item = $item;
        $this->orderLine = $orderLine;
        $this->line = $line;
        $this->quantity = $quantity;
        $this->units = $units;
        $this->description = $description;
        $this->serialNumbers = $serialNumbers;
        $this->price = $price;
        $this->rate = $rate;
        $this->amount = $amount;
        $this->options = $options;
        $this->revRecTermInMonths = $revRecTermInMonths;
        $this->deferRevRec = $deferRevRec;
        $this->isClosed = $isClosed;
        $this->department = $department;
        $this->class = $class;
        $this->location = $location;
        $this->revRecSchedule = $revRecSchedule;
        $this->revRecStartDate = $revRecStartDate;
        $this->revRecEndDate = $revRecEndDate;
        $this->taxCode = $taxCode;
        $this->taxRate1 = $taxRate1;
        $this->taxRate2 = $taxRate2;
        $this->tax1Amt = $tax1Amt;
        $this->grossAmt = $grossAmt;
        $this->isTaxable = $isTaxable;
        $this->customFieldList = $customFieldList;
    }
}?>