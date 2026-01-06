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
 * SupportCase map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_SupportCase {
    public $escalationMessage;
    public $lastReopenedDate;
    public $incomingMessage;
    public $messageNew;
    public $outgoingMessage;
    public $emailForm;
    public $internalOnly;
    public $customForm; //NetSuite_RecordRef
    public $title;
    public $caseNumber;
    public $startDate;
    public $createdDate;
    public $lastModifiedDate;
    public $lastMessageDate;
    public $company; //NetSuite_RecordRef
    public $contact; //NetSuite_RecordRef
    public $email;
    public $phone;
    public $item; //NetSuite_RecordRef
    public $serialNumber; //NetSuite_RecordRef
    public $inboundEmail;
    public $issue; //NetSuite_RecordRef
    public $status; //NetSuite_RecordRef
    public $isInactive;
    public $priority; //NetSuite_RecordRef
    public $origin; //NetSuite_RecordRef
    public $category; //NetSuite_RecordRef
    public $assigned; //NetSuite_RecordRef
    public $helpDesk;
    public $emailEmployeesList; //NetSuite_EmailEmployeesList
    public $escalateToList; //NetSuite_SupportCaseEscalateToList
    public $solutionsList; //NetSuite_SupportCaseSolutionsList
    public $customFieldList; //NetSuite_CustomFieldList
    public $internalId;
    public $externalId;

    public function __construct(  $escalationMessage, $lastReopenedDate, $incomingMessage, $messageNew, $outgoingMessage, $emailForm, $internalOnly, NetSuite_RecordRef $customForm, $title, $caseNumber, $startDate, $createdDate, $lastModifiedDate, $lastMessageDate, NetSuite_RecordRef $company, NetSuite_RecordRef $contact, $email, $phone, NetSuite_RecordRef $item, NetSuite_RecordRef $serialNumber, $inboundEmail, NetSuite_RecordRef $issue, NetSuite_RecordRef $status, $isInactive, NetSuite_RecordRef $priority, NetSuite_RecordRef $origin, NetSuite_RecordRef $category, NetSuite_RecordRef $assigned, $helpDesk, NetSuite_EmailEmployeesList $emailEmployeesList, NetSuite_SupportCaseEscalateToList $escalateToList, NetSuite_SupportCaseSolutionsList $solutionsList, NetSuite_CustomFieldList $customFieldList, $internalId, $externalId) {
        $this->escalationMessage = $escalationMessage;
        $this->lastReopenedDate = $lastReopenedDate;
        $this->incomingMessage = $incomingMessage;
        $this->messageNew = $messageNew;
        $this->outgoingMessage = $outgoingMessage;
        $this->emailForm = $emailForm;
        $this->internalOnly = $internalOnly;
        $this->customForm = $customForm;
        $this->title = $title;
        $this->caseNumber = $caseNumber;
        $this->startDate = $startDate;
        $this->createdDate = $createdDate;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->lastMessageDate = $lastMessageDate;
        $this->company = $company;
        $this->contact = $contact;
        $this->email = $email;
        $this->phone = $phone;
        $this->item = $item;
        $this->serialNumber = $serialNumber;
        $this->inboundEmail = $inboundEmail;
        $this->issue = $issue;
        $this->status = $status;
        $this->isInactive = $isInactive;
        $this->priority = $priority;
        $this->origin = $origin;
        $this->category = $category;
        $this->assigned = $assigned;
        $this->helpDesk = $helpDesk;
        $this->emailEmployeesList = $emailEmployeesList;
        $this->escalateToList = $escalateToList;
        $this->solutionsList = $solutionsList;
        $this->customFieldList = $customFieldList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>