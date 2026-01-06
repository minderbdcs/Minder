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
 * TimeBillSearchBasic map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_TimeBillSearchBasic {
    public $approved; //NetSuite_SearchBooleanField
    public $billable; //NetSuite_SearchBooleanField
    public $class; //NetSuite_SearchMultiSelectField
    public $customer; //NetSuite_SearchMultiSelectField
    public $date; //NetSuite_SearchDateField
    public $dateCreated; //NetSuite_SearchDateField
    public $department; //NetSuite_SearchMultiSelectField
    public $duration; //NetSuite_SearchDoubleField
    public $employee; //NetSuite_SearchMultiSelectField
    public $internalId; //NetSuite_SearchMultiSelectField
    public $item; //NetSuite_SearchMultiSelectField
    public $lastModified; //NetSuite_SearchDateField
    public $location; //NetSuite_SearchMultiSelectField
    public $memo; //NetSuite_SearchStringField
    public $paidByPayroll; //NetSuite_SearchBooleanField
    public $paidExternally; //NetSuite_SearchBooleanField
    public $payItem; //NetSuite_SearchMultiSelectField
    public $status; //NetSuite_SearchBooleanField
    public $customFieldList; //NetSuite_SearchCustomFieldList

    public function __construct(  NetSuite_SearchBooleanField $approved, NetSuite_SearchBooleanField $billable, NetSuite_SearchMultiSelectField $class, NetSuite_SearchMultiSelectField $customer, NetSuite_SearchDateField $date, NetSuite_SearchDateField $dateCreated, NetSuite_SearchMultiSelectField $department, NetSuite_SearchDoubleField $duration, NetSuite_SearchMultiSelectField $employee, NetSuite_SearchMultiSelectField $internalId, NetSuite_SearchMultiSelectField $item, NetSuite_SearchDateField $lastModified, NetSuite_SearchMultiSelectField $location, NetSuite_SearchStringField $memo, NetSuite_SearchBooleanField $paidByPayroll, NetSuite_SearchBooleanField $paidExternally, NetSuite_SearchMultiSelectField $payItem, NetSuite_SearchBooleanField $status, NetSuite_SearchCustomFieldList $customFieldList) {
        $this->approved = $approved;
        $this->billable = $billable;
        $this->class = $class;
        $this->customer = $customer;
        $this->date = $date;
        $this->dateCreated = $dateCreated;
        $this->department = $department;
        $this->duration = $duration;
        $this->employee = $employee;
        $this->internalId = $internalId;
        $this->item = $item;
        $this->lastModified = $lastModified;
        $this->location = $location;
        $this->memo = $memo;
        $this->paidByPayroll = $paidByPayroll;
        $this->paidExternally = $paidExternally;
        $this->payItem = $payItem;
        $this->status = $status;
        $this->customFieldList = $customFieldList;
    }
}?>