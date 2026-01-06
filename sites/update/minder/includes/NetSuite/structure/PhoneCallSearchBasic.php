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
 * PhoneCallSearchBasic map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_PhoneCallSearchBasic {
    public $assigned; //NetSuite_SearchMultiSelectField
    public $company; //NetSuite_SearchMultiSelectField
    public $completedDate; //NetSuite_SearchDateField
    public $contact; //NetSuite_SearchMultiSelectField
    public $createdBy; //NetSuite_SearchMultiSelectField
    public $createdDate; //NetSuite_SearchDateField
    public $externalId; //NetSuite_SearchMultiSelectField
    public $internalId; //NetSuite_SearchMultiSelectField
    public $isPrivate; //NetSuite_SearchBooleanField
    public $lastModifiedDate; //NetSuite_SearchDateField
    public $owner; //NetSuite_SearchBooleanField
    public $phone; //NetSuite_SearchStringField
    public $priority; //NetSuite_SearchEnumMultiSelectField
    public $startDate; //NetSuite_SearchDateField
    public $status; //NetSuite_SearchEnumMultiSelectField
    public $title; //NetSuite_SearchStringField
    public $customFieldList; //NetSuite_SearchCustomFieldList

    public function __construct(  NetSuite_SearchMultiSelectField $assigned, NetSuite_SearchMultiSelectField $company, NetSuite_SearchDateField $completedDate, NetSuite_SearchMultiSelectField $contact, NetSuite_SearchMultiSelectField $createdBy, NetSuite_SearchDateField $createdDate, NetSuite_SearchMultiSelectField $externalId, NetSuite_SearchMultiSelectField $internalId, NetSuite_SearchBooleanField $isPrivate, NetSuite_SearchDateField $lastModifiedDate, NetSuite_SearchBooleanField $owner, NetSuite_SearchStringField $phone, NetSuite_SearchEnumMultiSelectField $priority, NetSuite_SearchDateField $startDate, NetSuite_SearchEnumMultiSelectField $status, NetSuite_SearchStringField $title, NetSuite_SearchCustomFieldList $customFieldList) {
        $this->assigned = $assigned;
        $this->company = $company;
        $this->completedDate = $completedDate;
        $this->contact = $contact;
        $this->createdBy = $createdBy;
        $this->createdDate = $createdDate;
        $this->externalId = $externalId;
        $this->internalId = $internalId;
        $this->isPrivate = $isPrivate;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->owner = $owner;
        $this->phone = $phone;
        $this->priority = $priority;
        $this->startDate = $startDate;
        $this->status = $status;
        $this->title = $title;
        $this->customFieldList = $customFieldList;
    }
}?>