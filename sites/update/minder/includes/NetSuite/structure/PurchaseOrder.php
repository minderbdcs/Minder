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
 * PurchaseOrder map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_PurchaseOrder {
    public $createdDate;
    public $lastModifiedDate;
    public $customForm; //NetSuite_RecordRef
    public $entity; //NetSuite_RecordRef
    public $employee; //NetSuite_RecordRef
    public $supervisorApproval;
    public $tranDate;
    public $tranId;
    public $createdFrom; //NetSuite_RecordRef
    public $terms; //NetSuite_RecordRef
    public $dueDate;
    public $otherRefNum;
    public $memo;
    public $exchangeRate;
    public $currencyName;
    public $toBePrinted;
    public $toBeEmailed;
    public $email;
    public $toBeFaxed;
    public $fax;
    public $message;
    public $billAddressList; //NetSuite_RecordRef
    public $billAddress;
    public $shipTo; //NetSuite_RecordRef
    public $shipAddressList; //NetSuite_RecordRef
    public $shipAddress;
    public $fob;
    public $shipDate;
    public $shipMethod; //NetSuite_RecordRef
    public $trackingNumbers;
    public $total;
    public $class; //NetSuite_RecordRef
    public $department; //NetSuite_RecordRef
    public $location; //NetSuite_RecordRef
    public $status;
    public $itemList; //NetSuite_PurchaseOrderItemList
    public $expenseList; //NetSuite_PurchaseOrderExpenseList
    public $customFieldList; //NetSuite_CustomFieldList
    public $internalId;
    public $externalId;

    public function __construct(  $createdDate, $lastModifiedDate, NetSuite_RecordRef $customForm, NetSuite_RecordRef $entity, NetSuite_RecordRef $employee, $supervisorApproval, $tranDate, $tranId, NetSuite_RecordRef $createdFrom, NetSuite_RecordRef $terms, $dueDate, $otherRefNum, $memo, $exchangeRate, $currencyName, $toBePrinted, $toBeEmailed, $email, $toBeFaxed, $fax, $message, NetSuite_RecordRef $billAddressList, $billAddress, NetSuite_RecordRef $shipTo, NetSuite_RecordRef $shipAddressList, $shipAddress, $fob, $shipDate, NetSuite_RecordRef $shipMethod, $trackingNumbers, $total, NetSuite_RecordRef $class, NetSuite_RecordRef $department, NetSuite_RecordRef $location, $status, NetSuite_PurchaseOrderItemList $itemList, NetSuite_PurchaseOrderExpenseList $expenseList, NetSuite_CustomFieldList $customFieldList, $internalId, $externalId) {
        $this->createdDate = $createdDate;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->customForm = $customForm;
        $this->entity = $entity;
        $this->employee = $employee;
        $this->supervisorApproval = $supervisorApproval;
        $this->tranDate = $tranDate;
        $this->tranId = $tranId;
        $this->createdFrom = $createdFrom;
        $this->terms = $terms;
        $this->dueDate = $dueDate;
        $this->otherRefNum = $otherRefNum;
        $this->memo = $memo;
        $this->exchangeRate = $exchangeRate;
        $this->currencyName = $currencyName;
        $this->toBePrinted = $toBePrinted;
        $this->toBeEmailed = $toBeEmailed;
        $this->email = $email;
        $this->toBeFaxed = $toBeFaxed;
        $this->fax = $fax;
        $this->message = $message;
        $this->billAddressList = $billAddressList;
        $this->billAddress = $billAddress;
        $this->shipTo = $shipTo;
        $this->shipAddressList = $shipAddressList;
        $this->shipAddress = $shipAddress;
        $this->fob = $fob;
        $this->shipDate = $shipDate;
        $this->shipMethod = $shipMethod;
        $this->trackingNumbers = $trackingNumbers;
        $this->total = $total;
        $this->class = $class;
        $this->department = $department;
        $this->location = $location;
        $this->status = $status;
        $this->itemList = $itemList;
        $this->expenseList = $expenseList;
        $this->customFieldList = $customFieldList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>