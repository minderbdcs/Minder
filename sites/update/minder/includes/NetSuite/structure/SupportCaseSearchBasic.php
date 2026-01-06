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
 * SupportCaseSearchBasic map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_SupportCaseSearchBasic {
    public $assigned; //NetSuite_SearchMultiSelectField
    public $awaitingReply; //NetSuite_SearchBooleanField
    public $caseNumber; //NetSuite_SearchStringField
    public $category; //NetSuite_SearchMultiSelectField
    public $closedDate; //NetSuite_SearchDateField
    public $company; //NetSuite_SearchStringField
    public $contact; //NetSuite_SearchStringField
    public $createdDate; //NetSuite_SearchDateField
    public $email; //NetSuite_SearchStringField
    public $escalateTo; //NetSuite_SearchMultiSelectField
    public $externalId; //NetSuite_SearchMultiSelectField
    public $helpDesk; //NetSuite_SearchBooleanField
    public $inboundEmail; //NetSuite_SearchStringField
    public $internalId; //NetSuite_SearchMultiSelectField
    public $isInactive; //NetSuite_SearchBooleanField
    public $issue; //NetSuite_SearchMultiSelectField
    public $item; //NetSuite_SearchMultiSelectField
    public $lastMessage; //NetSuite_SearchBooleanField
    public $lastModifiedDate; //NetSuite_SearchDateField
    public $lastReopenedDate; //NetSuite_SearchDateField
    public $locked; //NetSuite_SearchBooleanField
    public $message; //NetSuite_SearchStringField
    public $messageAuthor; //NetSuite_SearchMultiSelectField
    public $messageDate; //NetSuite_SearchDateField
    public $messageType; //NetSuite_SearchBooleanField
    public $module; //NetSuite_SearchMultiSelectField
    public $number; //NetSuite_SearchLongField
    public $origin; //NetSuite_SearchMultiSelectField
    public $priority; //NetSuite_SearchMultiSelectField
    public $product; //NetSuite_SearchMultiSelectField
    public $stage; //NetSuite_SearchEnumMultiSelectField
    public $status; //NetSuite_SearchMultiSelectField
    public $title; //NetSuite_SearchStringField
    public $type; //NetSuite_SearchEnumMultiSelectField
    public $customFieldList; //NetSuite_SearchCustomFieldList

    public function __construct(  NetSuite_SearchMultiSelectField $assigned, NetSuite_SearchBooleanField $awaitingReply, NetSuite_SearchStringField $caseNumber, NetSuite_SearchMultiSelectField $category, NetSuite_SearchDateField $closedDate, NetSuite_SearchStringField $company, NetSuite_SearchStringField $contact, NetSuite_SearchDateField $createdDate, NetSuite_SearchStringField $email, NetSuite_SearchMultiSelectField $escalateTo, NetSuite_SearchMultiSelectField $externalId, NetSuite_SearchBooleanField $helpDesk, NetSuite_SearchStringField $inboundEmail, NetSuite_SearchMultiSelectField $internalId, NetSuite_SearchBooleanField $isInactive, NetSuite_SearchMultiSelectField $issue, NetSuite_SearchMultiSelectField $item, NetSuite_SearchBooleanField $lastMessage, NetSuite_SearchDateField $lastModifiedDate, NetSuite_SearchDateField $lastReopenedDate, NetSuite_SearchBooleanField $locked, NetSuite_SearchStringField $message, NetSuite_SearchMultiSelectField $messageAuthor, NetSuite_SearchDateField $messageDate, NetSuite_SearchBooleanField $messageType, NetSuite_SearchMultiSelectField $module, NetSuite_SearchLongField $number, NetSuite_SearchMultiSelectField $origin, NetSuite_SearchMultiSelectField $priority, NetSuite_SearchMultiSelectField $product, NetSuite_SearchEnumMultiSelectField $stage, NetSuite_SearchMultiSelectField $status, NetSuite_SearchStringField $title, NetSuite_SearchEnumMultiSelectField $type, NetSuite_SearchCustomFieldList $customFieldList) {
        $this->assigned = $assigned;
        $this->awaitingReply = $awaitingReply;
        $this->caseNumber = $caseNumber;
        $this->category = $category;
        $this->closedDate = $closedDate;
        $this->company = $company;
        $this->contact = $contact;
        $this->createdDate = $createdDate;
        $this->email = $email;
        $this->escalateTo = $escalateTo;
        $this->externalId = $externalId;
        $this->helpDesk = $helpDesk;
        $this->inboundEmail = $inboundEmail;
        $this->internalId = $internalId;
        $this->isInactive = $isInactive;
        $this->issue = $issue;
        $this->item = $item;
        $this->lastMessage = $lastMessage;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->lastReopenedDate = $lastReopenedDate;
        $this->locked = $locked;
        $this->message = $message;
        $this->messageAuthor = $messageAuthor;
        $this->messageDate = $messageDate;
        $this->messageType = $messageType;
        $this->module = $module;
        $this->number = $number;
        $this->origin = $origin;
        $this->priority = $priority;
        $this->product = $product;
        $this->stage = $stage;
        $this->status = $status;
        $this->title = $title;
        $this->type = $type;
        $this->customFieldList = $customFieldList;
    }
}?>