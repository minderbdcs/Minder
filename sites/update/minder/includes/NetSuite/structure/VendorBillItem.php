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
 * VendorBillItem map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_VendorBillItem {
    public $item; //NetSuite_RecordRef
    public $line;
    public $orderLine;
    public $quantity;
    public $units; //NetSuite_RecordRef
    public $description;
    public $serialNumbers;
    public $binNumbers;
    public $expirationDate;
    public $rate;
    public $amount;
    public $options; //NetSuite_CustomFieldList
    public $department; //NetSuite_RecordRef
    public $class; //NetSuite_RecordRef
    public $location; //NetSuite_RecordRef
    public $customer; //NetSuite_RecordRef
    public $landedCostCategory; //NetSuite_RecordRef
    public $isBillable;
    public $amortizationSched; //NetSuite_RecordRef
    public $amortizStartDate;
    public $amortizationEndDate;
    public $amortizationResidual;
    public $customFieldList; //NetSuite_CustomFieldList

    public function __construct(  NetSuite_RecordRef $item, $line, $orderLine, $quantity, NetSuite_RecordRef $units, $description, $serialNumbers, $binNumbers, $expirationDate, $rate, $amount, NetSuite_CustomFieldList $options, NetSuite_RecordRef $department, NetSuite_RecordRef $class, NetSuite_RecordRef $location, NetSuite_RecordRef $customer, NetSuite_RecordRef $landedCostCategory, $isBillable, NetSuite_RecordRef $amortizationSched, $amortizStartDate, $amortizationEndDate, $amortizationResidual, NetSuite_CustomFieldList $customFieldList) {
        $this->item = $item;
        $this->line = $line;
        $this->orderLine = $orderLine;
        $this->quantity = $quantity;
        $this->units = $units;
        $this->description = $description;
        $this->serialNumbers = $serialNumbers;
        $this->binNumbers = $binNumbers;
        $this->expirationDate = $expirationDate;
        $this->rate = $rate;
        $this->amount = $amount;
        $this->options = $options;
        $this->department = $department;
        $this->class = $class;
        $this->location = $location;
        $this->customer = $customer;
        $this->landedCostCategory = $landedCostCategory;
        $this->isBillable = $isBillable;
        $this->amortizationSched = $amortizationSched;
        $this->amortizStartDate = $amortizStartDate;
        $this->amortizationEndDate = $amortizationEndDate;
        $this->amortizationResidual = $amortizationResidual;
        $this->customFieldList = $customFieldList;
    }
}?>