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
 * CheckExpense map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_CheckExpense {
    public $line;
    public $category; //NetSuite_RecordRef
    public $account; //NetSuite_RecordRef
    public $amount;
    public $memo;
    public $department; //NetSuite_RecordRef
    public $class; //NetSuite_RecordRef
    public $location; //NetSuite_RecordRef
    public $customer; //NetSuite_RecordRef
    public $isBillable;
    public $taxCode; //NetSuite_RecordRef
    public $taxRate1;
    public $taxRate2;
    public $customFieldList; //NetSuite_CustomFieldList

    public function __construct(  $line, NetSuite_RecordRef $category, NetSuite_RecordRef $account, $amount, $memo, NetSuite_RecordRef $department, NetSuite_RecordRef $class, NetSuite_RecordRef $location, NetSuite_RecordRef $customer, $isBillable, NetSuite_RecordRef $taxCode, $taxRate1, $taxRate2, NetSuite_CustomFieldList $customFieldList) {
        $this->line = $line;
        $this->category = $category;
        $this->account = $account;
        $this->amount = $amount;
        $this->memo = $memo;
        $this->department = $department;
        $this->class = $class;
        $this->location = $location;
        $this->customer = $customer;
        $this->isBillable = $isBillable;
        $this->taxCode = $taxCode;
        $this->taxRate1 = $taxRate1;
        $this->taxRate2 = $taxRate2;
        $this->customFieldList = $customFieldList;
    }
}?>