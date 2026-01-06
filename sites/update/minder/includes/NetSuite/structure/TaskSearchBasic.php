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
 * TaskSearchBasic map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_TaskSearchBasic {
    public $assigned; //NetSuite_SearchMultiSelectField
    public $company; //NetSuite_SearchMultiSelectField
    public $completedDate; //NetSuite_SearchDateField
    public $contact; //NetSuite_SearchMultiSelectField
    public $createdDate; //NetSuite_SearchDateField
    public $endDate; //NetSuite_SearchDateField
    public $estimatedTime; //NetSuite_SearchDoubleField
    public $estimatedTimeOverride; //NetSuite_SearchDoubleField
    public $externalId; //NetSuite_SearchMultiSelectField
    public $internalId; //NetSuite_SearchMultiSelectField
    public $isPrivate; //NetSuite_SearchBooleanField
    public $lastModifiedDate; //NetSuite_SearchDateField
    public $milestone; //NetSuite_SearchLongField
    public $owner; //NetSuite_SearchMultiSelectField
    public $percentComplete; //NetSuite_SearchLongField
    public $percentTimeComplete; //NetSuite_SearchLongField
    public $priority; //NetSuite_SearchEnumMultiSelectField
    public $startDate; //NetSuite_SearchDateField
    public $status; //NetSuite_SearchEnumMultiSelectField
    public $title; //NetSuite_SearchStringField
    public $customFieldList; //NetSuite_SearchCustomFieldList

    public function __construct(  NetSuite_SearchMultiSelectField $assigned, NetSuite_SearchMultiSelectField $company, NetSuite_SearchDateField $completedDate, NetSuite_SearchMultiSelectField $contact, NetSuite_SearchDateField $createdDate, NetSuite_SearchDateField $endDate, NetSuite_SearchDoubleField $estimatedTime, NetSuite_SearchDoubleField $estimatedTimeOverride, NetSuite_SearchMultiSelectField $externalId, NetSuite_SearchMultiSelectField $internalId, NetSuite_SearchBooleanField $isPrivate, NetSuite_SearchDateField $lastModifiedDate, NetSuite_SearchLongField $milestone, NetSuite_SearchMultiSelectField $owner, NetSuite_SearchLongField $percentComplete, NetSuite_SearchLongField $percentTimeComplete, NetSuite_SearchEnumMultiSelectField $priority, NetSuite_SearchDateField $startDate, NetSuite_SearchEnumMultiSelectField $status, NetSuite_SearchStringField $title, NetSuite_SearchCustomFieldList $customFieldList) {
        $this->assigned = $assigned;
        $this->company = $company;
        $this->completedDate = $completedDate;
        $this->contact = $contact;
        $this->createdDate = $createdDate;
        $this->endDate = $endDate;
        $this->estimatedTime = $estimatedTime;
        $this->estimatedTimeOverride = $estimatedTimeOverride;
        $this->externalId = $externalId;
        $this->internalId = $internalId;
        $this->isPrivate = $isPrivate;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->milestone = $milestone;
        $this->owner = $owner;
        $this->percentComplete = $percentComplete;
        $this->percentTimeComplete = $percentTimeComplete;
        $this->priority = $priority;
        $this->startDate = $startDate;
        $this->status = $status;
        $this->title = $title;
        $this->customFieldList = $customFieldList;
    }
}?>