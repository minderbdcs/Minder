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
 * Check map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_Check {
    public $createdDate;
    public $lastModifiedDate;
    public $customForm; //NetSuite_RecordRef
    public $account; //NetSuite_RecordRef
    public $entity; //NetSuite_RecordRef
    public $address;
    public $postingPeriod; //NetSuite_RecordRef
    public $tranDate;
    public $currency; //NetSuite_RecordRef
    public $exchangeRate;
    public $toBePrinted;
    public $tranId;
    public $memo;
    public $department; //NetSuite_RecordRef
    public $class; //NetSuite_RecordRef
    public $location; //NetSuite_RecordRef
    public $userTotal;
    public $expenseList; //NetSuite_CheckExpenseList
    public $itemList; //NetSuite_CheckItemList
    public $customFieldList; //NetSuite_CustomFieldList
    public $billPay;
    public $internalId;
    public $externalId;

    public function __construct(  $createdDate, $lastModifiedDate, NetSuite_RecordRef $customForm, NetSuite_RecordRef $account, NetSuite_RecordRef $entity, $address, NetSuite_RecordRef $postingPeriod, $tranDate, NetSuite_RecordRef $currency, $exchangeRate, $toBePrinted, $tranId, $memo, NetSuite_RecordRef $department, NetSuite_RecordRef $class, NetSuite_RecordRef $location, $userTotal, NetSuite_CheckExpenseList $expenseList, NetSuite_CheckItemList $itemList, NetSuite_CustomFieldList $customFieldList, $billPay, $internalId, $externalId) {
        $this->createdDate = $createdDate;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->customForm = $customForm;
        $this->account = $account;
        $this->entity = $entity;
        $this->address = $address;
        $this->postingPeriod = $postingPeriod;
        $this->tranDate = $tranDate;
        $this->currency = $currency;
        $this->exchangeRate = $exchangeRate;
        $this->toBePrinted = $toBePrinted;
        $this->tranId = $tranId;
        $this->memo = $memo;
        $this->department = $department;
        $this->class = $class;
        $this->location = $location;
        $this->userTotal = $userTotal;
        $this->expenseList = $expenseList;
        $this->itemList = $itemList;
        $this->customFieldList = $customFieldList;
        $this->billPay = $billPay;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>