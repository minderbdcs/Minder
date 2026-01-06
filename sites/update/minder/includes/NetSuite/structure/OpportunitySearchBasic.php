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
 * OpportunitySearchBasic map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_OpportunitySearchBasic {
    public $amount; //NetSuite_SearchDoubleField
    public $availableOffline; //NetSuite_SearchBooleanField
    public $class; //NetSuite_SearchMultiSelectField
    public $closeDate; //NetSuite_SearchDateField
    public $competitor; //NetSuite_SearchMultiSelectField
    public $contribution; //NetSuite_SearchLongField
    public $currencyName; //NetSuite_SearchMultiSelectField
    public $custType; //NetSuite_SearchMultiSelectField
    public $dateCreated; //NetSuite_SearchDateField
    public $daysOpen; //NetSuite_SearchLongField
    public $daysToClose; //NetSuite_SearchLongField
    public $department; //NetSuite_SearchMultiSelectField
    public $entity; //NetSuite_SearchMultiSelectField
    public $entityStatus; //NetSuite_SearchMultiSelectField
    public $expectedCloseDate; //NetSuite_SearchDateField
    public $externalId; //NetSuite_SearchMultiSelectField
    public $forecastType; //NetSuite_SearchMultiSelectField
    public $internalId; //NetSuite_SearchMultiSelectField
    public $item; //NetSuite_SearchMultiSelectField
    public $lastModifiedDate; //NetSuite_SearchDateField
    public $leadSource; //NetSuite_SearchMultiSelectField
    public $location; //NetSuite_SearchMultiSelectField
    public $memo; //NetSuite_SearchStringField
    public $number; //NetSuite_SearchLongField
    public $partner; //NetSuite_SearchMultiSelectField
    public $postingPeriod; //NetSuite_SearchMultiSelectField
    public $probability; //NetSuite_SearchLongField
    public $projAltSalesAmt; //NetSuite_SearchDoubleField
    public $projectedTotal; //NetSuite_SearchDoubleField
    public $rangeHigh; //NetSuite_SearchDoubleField
    public $rangeHighAlt; //NetSuite_SearchDoubleField
    public $rangeLow; //NetSuite_SearchDoubleField
    public $rangeLowAlt; //NetSuite_SearchDoubleField
    public $salesRep; //NetSuite_SearchMultiSelectField
    public $salesTeamMember; //NetSuite_SearchMultiSelectField
    public $salesTeamRole; //NetSuite_SearchMultiSelectField
    public $status; //NetSuite_SearchEnumMultiSelectField
    public $title; //NetSuite_SearchStringField
    public $tranDate; //NetSuite_SearchDateField
    public $tranId; //NetSuite_SearchStringField
    public $winLossReason; //NetSuite_SearchMultiSelectField
    public $wonBy; //NetSuite_SearchMultiSelectField
    public $customFieldList; //NetSuite_SearchCustomFieldList

    public function __construct(  NetSuite_SearchDoubleField $amount, NetSuite_SearchBooleanField $availableOffline, NetSuite_SearchMultiSelectField $class, NetSuite_SearchDateField $closeDate, NetSuite_SearchMultiSelectField $competitor, NetSuite_SearchLongField $contribution, NetSuite_SearchMultiSelectField $currencyName, NetSuite_SearchMultiSelectField $custType, NetSuite_SearchDateField $dateCreated, NetSuite_SearchLongField $daysOpen, NetSuite_SearchLongField $daysToClose, NetSuite_SearchMultiSelectField $department, NetSuite_SearchMultiSelectField $entity, NetSuite_SearchMultiSelectField $entityStatus, NetSuite_SearchDateField $expectedCloseDate, NetSuite_SearchMultiSelectField $externalId, NetSuite_SearchMultiSelectField $forecastType, NetSuite_SearchMultiSelectField $internalId, NetSuite_SearchMultiSelectField $item, NetSuite_SearchDateField $lastModifiedDate, NetSuite_SearchMultiSelectField $leadSource, NetSuite_SearchMultiSelectField $location, NetSuite_SearchStringField $memo, NetSuite_SearchLongField $number, NetSuite_SearchMultiSelectField $partner, NetSuite_SearchMultiSelectField $postingPeriod, NetSuite_SearchLongField $probability, NetSuite_SearchDoubleField $projAltSalesAmt, NetSuite_SearchDoubleField $projectedTotal, NetSuite_SearchDoubleField $rangeHigh, NetSuite_SearchDoubleField $rangeHighAlt, NetSuite_SearchDoubleField $rangeLow, NetSuite_SearchDoubleField $rangeLowAlt, NetSuite_SearchMultiSelectField $salesRep, NetSuite_SearchMultiSelectField $salesTeamMember, NetSuite_SearchMultiSelectField $salesTeamRole, NetSuite_SearchEnumMultiSelectField $status, NetSuite_SearchStringField $title, NetSuite_SearchDateField $tranDate, NetSuite_SearchStringField $tranId, NetSuite_SearchMultiSelectField $winLossReason, NetSuite_SearchMultiSelectField $wonBy, NetSuite_SearchCustomFieldList $customFieldList) {
        $this->amount = $amount;
        $this->availableOffline = $availableOffline;
        $this->class = $class;
        $this->closeDate = $closeDate;
        $this->competitor = $competitor;
        $this->contribution = $contribution;
        $this->currencyName = $currencyName;
        $this->custType = $custType;
        $this->dateCreated = $dateCreated;
        $this->daysOpen = $daysOpen;
        $this->daysToClose = $daysToClose;
        $this->department = $department;
        $this->entity = $entity;
        $this->entityStatus = $entityStatus;
        $this->expectedCloseDate = $expectedCloseDate;
        $this->externalId = $externalId;
        $this->forecastType = $forecastType;
        $this->internalId = $internalId;
        $this->item = $item;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->leadSource = $leadSource;
        $this->location = $location;
        $this->memo = $memo;
        $this->number = $number;
        $this->partner = $partner;
        $this->postingPeriod = $postingPeriod;
        $this->probability = $probability;
        $this->projAltSalesAmt = $projAltSalesAmt;
        $this->projectedTotal = $projectedTotal;
        $this->rangeHigh = $rangeHigh;
        $this->rangeHighAlt = $rangeHighAlt;
        $this->rangeLow = $rangeLow;
        $this->rangeLowAlt = $rangeLowAlt;
        $this->salesRep = $salesRep;
        $this->salesTeamMember = $salesTeamMember;
        $this->salesTeamRole = $salesTeamRole;
        $this->status = $status;
        $this->title = $title;
        $this->tranDate = $tranDate;
        $this->tranId = $tranId;
        $this->winLossReason = $winLossReason;
        $this->wonBy = $wonBy;
        $this->customFieldList = $customFieldList;
    }
}?>