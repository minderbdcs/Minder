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
 * CalendarEventSearchBasic map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_CalendarEventSearchBasic {
    public $attendee; //NetSuite_SearchMultiSelectField
    public $calendar; //NetSuite_SearchMultiSelectField
    public $completedDate; //NetSuite_SearchDateField
    public $createdDate; //NetSuite_SearchDateField
    public $externalId; //NetSuite_SearchMultiSelectField
    public $internalId; //NetSuite_SearchMultiSelectField
    public $isUpcomingEvent; //NetSuite_SearchBooleanField
    public $lastModifiedDate; //NetSuite_SearchDateField
    public $location; //NetSuite_SearchStringField
    public $organizer; //NetSuite_SearchMultiSelectField
    public $resource; //NetSuite_SearchMultiSelectField
    public $response; //NetSuite_SearchEnumMultiSelectField
    public $startDate; //NetSuite_SearchDateField
    public $status; //NetSuite_SearchEnumMultiSelectField
    public $title; //NetSuite_SearchStringField
    public $customFieldList; //NetSuite_SearchCustomFieldList

    public function __construct(  NetSuite_SearchMultiSelectField $attendee, NetSuite_SearchMultiSelectField $calendar, NetSuite_SearchDateField $completedDate, NetSuite_SearchDateField $createdDate, NetSuite_SearchMultiSelectField $externalId, NetSuite_SearchMultiSelectField $internalId, NetSuite_SearchBooleanField $isUpcomingEvent, NetSuite_SearchDateField $lastModifiedDate, NetSuite_SearchStringField $location, NetSuite_SearchMultiSelectField $organizer, NetSuite_SearchMultiSelectField $resource, NetSuite_SearchEnumMultiSelectField $response, NetSuite_SearchDateField $startDate, NetSuite_SearchEnumMultiSelectField $status, NetSuite_SearchStringField $title, NetSuite_SearchCustomFieldList $customFieldList) {
        $this->attendee = $attendee;
        $this->calendar = $calendar;
        $this->completedDate = $completedDate;
        $this->createdDate = $createdDate;
        $this->externalId = $externalId;
        $this->internalId = $internalId;
        $this->isUpcomingEvent = $isUpcomingEvent;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->location = $location;
        $this->organizer = $organizer;
        $this->resource = $resource;
        $this->response = $response;
        $this->startDate = $startDate;
        $this->status = $status;
        $this->title = $title;
        $this->customFieldList = $customFieldList;
    }
}?>