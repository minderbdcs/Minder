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
 * CalendarEvent map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_CalendarEvent {
    public $company; //NetSuite_RecordRef
    public $contact; //NetSuite_RecordRef
    public $supportCase; //NetSuite_RecordRef
    public $transaction; //NetSuite_RecordRef
    public $isRecurringEvent;
    public $period;
    public $frequency;
    public $seriesStartDate;
    public $endByDate;
    public $noEndDate;
    public $sendEmail;
    public $customForm; //NetSuite_RecordRef
    public $title;
    public $location;
    public $startDate;
    public $allDayEvent;
    public $timedEvent;
    public $reminderType;
    public $reminderMinutes;
    public $status;
    public $accessLevel;
    public $organizer; //NetSuite_RecordRef
    public $message;
    public $createdDate;
    public $endDate;
    public $exclusionDateList; //NetSuite_ExclusionDateList
    public $lastModifiedDate;
    public $owner; //NetSuite_RecordRef
    public $attendeeList; //NetSuite_CalendarEventAttendeeList
    public $resourceList; //NetSuite_CalendarEventResourceList
    public $customFieldList; //NetSuite_CustomFieldList
    public $internalId;
    public $externalId;

    public function __construct(  NetSuite_RecordRef $company, NetSuite_RecordRef $contact, NetSuite_RecordRef $supportCase, NetSuite_RecordRef $transaction, $isRecurringEvent, $period, $frequency, $seriesStartDate, $endByDate, $noEndDate, $sendEmail, NetSuite_RecordRef $customForm, $title, $location, $startDate, $allDayEvent, $timedEvent, $reminderType, $reminderMinutes, $status, $accessLevel, NetSuite_RecordRef $organizer, $message, $createdDate, $endDate, NetSuite_ExclusionDateList $exclusionDateList, $lastModifiedDate, NetSuite_RecordRef $owner, NetSuite_CalendarEventAttendeeList $attendeeList, NetSuite_CalendarEventResourceList $resourceList, NetSuite_CustomFieldList $customFieldList, $internalId, $externalId) {
        $this->company = $company;
        $this->contact = $contact;
        $this->supportCase = $supportCase;
        $this->transaction = $transaction;
        $this->isRecurringEvent = $isRecurringEvent;
        $this->period = $period;
        $this->frequency = $frequency;
        $this->seriesStartDate = $seriesStartDate;
        $this->endByDate = $endByDate;
        $this->noEndDate = $noEndDate;
        $this->sendEmail = $sendEmail;
        $this->customForm = $customForm;
        $this->title = $title;
        $this->location = $location;
        $this->startDate = $startDate;
        $this->allDayEvent = $allDayEvent;
        $this->timedEvent = $timedEvent;
        $this->reminderType = $reminderType;
        $this->reminderMinutes = $reminderMinutes;
        $this->status = $status;
        $this->accessLevel = $accessLevel;
        $this->organizer = $organizer;
        $this->message = $message;
        $this->createdDate = $createdDate;
        $this->endDate = $endDate;
        $this->exclusionDateList = $exclusionDateList;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->owner = $owner;
        $this->attendeeList = $attendeeList;
        $this->resourceList = $resourceList;
        $this->customFieldList = $customFieldList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>