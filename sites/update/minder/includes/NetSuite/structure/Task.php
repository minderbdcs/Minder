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
 * Task map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_Task {
    public $company; //NetSuite_RecordRef
    public $contact; //NetSuite_RecordRef
    public $supportCase; //NetSuite_RecordRef
    public $transaction; //NetSuite_RecordRef
    public $milestone; //NetSuite_RecordRef
    public $customForm; //NetSuite_RecordRef
    public $title;
    public $assigned; //NetSuite_RecordRef
    public $sendEmail;
    public $startDate;
    public $timedEvent;
    public $endDate;
    public $completedDate;
    public $priority;
    public $status;
    public $message;
    public $accessLevel;
    public $reminderType;
    public $reminderMinutes;
    public $createdDate;
    public $lastModifiedDate;
    public $owner; //NetSuite_RecordRef
    public $contactList; //NetSuite_TaskContactList
    public $customFieldList; //NetSuite_CustomFieldList
    public $internalId;
    public $externalId;

    public function __construct(  NetSuite_RecordRef $company, NetSuite_RecordRef $contact, NetSuite_RecordRef $supportCase, NetSuite_RecordRef $transaction, NetSuite_RecordRef $milestone, NetSuite_RecordRef $customForm, $title, NetSuite_RecordRef $assigned, $sendEmail, $startDate, $timedEvent, $endDate, $completedDate, $priority, $status, $message, $accessLevel, $reminderType, $reminderMinutes, $createdDate, $lastModifiedDate, NetSuite_RecordRef $owner, NetSuite_TaskContactList $contactList, NetSuite_CustomFieldList $customFieldList, $internalId, $externalId) {
        $this->company = $company;
        $this->contact = $contact;
        $this->supportCase = $supportCase;
        $this->transaction = $transaction;
        $this->milestone = $milestone;
        $this->customForm = $customForm;
        $this->title = $title;
        $this->assigned = $assigned;
        $this->sendEmail = $sendEmail;
        $this->startDate = $startDate;
        $this->timedEvent = $timedEvent;
        $this->endDate = $endDate;
        $this->completedDate = $completedDate;
        $this->priority = $priority;
        $this->status = $status;
        $this->message = $message;
        $this->accessLevel = $accessLevel;
        $this->reminderType = $reminderType;
        $this->reminderMinutes = $reminderMinutes;
        $this->createdDate = $createdDate;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->owner = $owner;
        $this->contactList = $contactList;
        $this->customFieldList = $customFieldList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>