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
 * EstimateItem map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_EstimateItem {
    public $item; //NetSuite_RecordRef
    public $line;
    public $quantityAvailable;
    public $quantityOnHand;
    public $quantity;
    public $units; //NetSuite_RecordRef
    public $description;
    public $serialNumbers;
    public $price; //NetSuite_RecordRef
    public $rate;
    public $amount;
    public $options; //NetSuite_CustomFieldList
    public $revRecTermInMonths;
    public $department; //NetSuite_RecordRef
    public $class; //NetSuite_RecordRef
    public $location; //NetSuite_RecordRef
    public $isTaxable;
    public $altSalesAmt;
    public $grossAmt;
    public $tax1Amt;
    public $taxCode; //NetSuite_RecordRef
    public $taxRate1;
    public $taxRate2;
    public $customFieldList; //NetSuite_CustomFieldList

    public function __construct(  NetSuite_RecordRef $item, $line, $quantityAvailable, $quantityOnHand, $quantity, NetSuite_RecordRef $units, $description, $serialNumbers, NetSuite_RecordRef $price, $rate, $amount, NetSuite_CustomFieldList $options, $revRecTermInMonths, NetSuite_RecordRef $department, NetSuite_RecordRef $class, NetSuite_RecordRef $location, $isTaxable, $altSalesAmt, $grossAmt, $tax1Amt, NetSuite_RecordRef $taxCode, $taxRate1, $taxRate2, NetSuite_CustomFieldList $customFieldList) {
        $this->item = $item;
        $this->line = $line;
        $this->quantityAvailable = $quantityAvailable;
        $this->quantityOnHand = $quantityOnHand;
        $this->quantity = $quantity;
        $this->units = $units;
        $this->description = $description;
        $this->serialNumbers = $serialNumbers;
        $this->price = $price;
        $this->rate = $rate;
        $this->amount = $amount;
        $this->options = $options;
        $this->revRecTermInMonths = $revRecTermInMonths;
        $this->department = $department;
        $this->class = $class;
        $this->location = $location;
        $this->isTaxable = $isTaxable;
        $this->altSalesAmt = $altSalesAmt;
        $this->grossAmt = $grossAmt;
        $this->tax1Amt = $tax1Amt;
        $this->taxCode = $taxCode;
        $this->taxRate1 = $taxRate1;
        $this->taxRate2 = $taxRate2;
        $this->customFieldList = $customFieldList;
    }
}?>