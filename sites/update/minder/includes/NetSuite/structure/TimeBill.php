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
 * TimeBill map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_TimeBill {
    public $customForm; //NetSuite_RecordRef
    public $employee; //NetSuite_RecordRef
    public $tranDate;
    public $customer; //NetSuite_RecordRef
    public $caseTaskEvent; //NetSuite_RecordRef
    public $isBillable;
    public $payrollItem; //NetSuite_RecordRef
    public $paidExternally;
    public $item; //NetSuite_RecordRef
    public $department; //NetSuite_RecordRef
    public $class; //NetSuite_RecordRef
    public $location; //NetSuite_RecordRef
    public $hours;
    public $price; //NetSuite_RecordRef
    public $rate;
    public $overrideRate;
    public $memo;
    public $supervisorApproval;
    public $createdDate;
    public $lastModifiedDate;
    public $status;
    public $customFieldList; //NetSuite_CustomFieldList
    public $internalId;
    public $externalId;

    public function __construct(  NetSuite_RecordRef $customForm, NetSuite_RecordRef $employee, $tranDate, NetSuite_RecordRef $customer, NetSuite_RecordRef $caseTaskEvent, $isBillable, NetSuite_RecordRef $payrollItem, $paidExternally, NetSuite_RecordRef $item, NetSuite_RecordRef $department, NetSuite_RecordRef $class, NetSuite_RecordRef $location, $hours, NetSuite_RecordRef $price, $rate, $overrideRate, $memo, $supervisorApproval, $createdDate, $lastModifiedDate, $status, NetSuite_CustomFieldList $customFieldList, $internalId, $externalId) {
        $this->customForm = $customForm;
        $this->employee = $employee;
        $this->tranDate = $tranDate;
        $this->customer = $customer;
        $this->caseTaskEvent = $caseTaskEvent;
        $this->isBillable = $isBillable;
        $this->payrollItem = $payrollItem;
        $this->paidExternally = $paidExternally;
        $this->item = $item;
        $this->department = $department;
        $this->class = $class;
        $this->location = $location;
        $this->hours = $hours;
        $this->price = $price;
        $this->rate = $rate;
        $this->overrideRate = $overrideRate;
        $this->memo = $memo;
        $this->supervisorApproval = $supervisorApproval;
        $this->createdDate = $createdDate;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->status = $status;
        $this->customFieldList = $customFieldList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>