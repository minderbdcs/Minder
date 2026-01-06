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
 * VendorBill map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_VendorBill {
    public $createdDate;
    public $lastModifiedDate;
    public $customForm; //NetSuite_RecordRef
    public $entity; //NetSuite_RecordRef
    public $vatRegNum;
    public $postingPeriod; //NetSuite_RecordRef
    public $tranDate;
    public $currencyName;
    public $exchangeRate;
    public $terms; //NetSuite_RecordRef
    public $dueDate;
    public $discountDate;
    public $tranId;
    public $userTotal;
    public $discountAmount;
    public $memo;
    public $creditLimit;
    public $class; //NetSuite_RecordRef
    public $department; //NetSuite_RecordRef
    public $location; //NetSuite_RecordRef
    public $status;
    public $expenseList; //NetSuite_VendorBillExpenseList
    public $itemList; //NetSuite_VendorBillItemList
    public $customFieldList; //NetSuite_CustomFieldList
    public $internalId;
    public $externalId;

    public function __construct(  $createdDate, $lastModifiedDate, NetSuite_RecordRef $customForm, NetSuite_RecordRef $entity, $vatRegNum, NetSuite_RecordRef $postingPeriod, $tranDate, $currencyName, $exchangeRate, NetSuite_RecordRef $terms, $dueDate, $discountDate, $tranId, $userTotal, $discountAmount, $memo, $creditLimit, NetSuite_RecordRef $class, NetSuite_RecordRef $department, NetSuite_RecordRef $location, $status, NetSuite_VendorBillExpenseList $expenseList, NetSuite_VendorBillItemList $itemList, NetSuite_CustomFieldList $customFieldList, $internalId, $externalId) {
        $this->createdDate = $createdDate;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->customForm = $customForm;
        $this->entity = $entity;
        $this->vatRegNum = $vatRegNum;
        $this->postingPeriod = $postingPeriod;
        $this->tranDate = $tranDate;
        $this->currencyName = $currencyName;
        $this->exchangeRate = $exchangeRate;
        $this->terms = $terms;
        $this->dueDate = $dueDate;
        $this->discountDate = $discountDate;
        $this->tranId = $tranId;
        $this->userTotal = $userTotal;
        $this->discountAmount = $discountAmount;
        $this->memo = $memo;
        $this->creditLimit = $creditLimit;
        $this->class = $class;
        $this->department = $department;
        $this->location = $location;
        $this->status = $status;
        $this->expenseList = $expenseList;
        $this->itemList = $itemList;
        $this->customFieldList = $customFieldList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>