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
 * Opportunity map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_Opportunity {
    public $customForm; //NetSuite_RecordRef
    public $entity; //NetSuite_RecordRef
    public $title;
    public $tranId;
    public $salesRep; //NetSuite_RecordRef
    public $partner; //NetSuite_RecordRef
    public $salesGroup; //NetSuite_RecordRef
    public $syncSalesTeams;
    public $leadSource; //NetSuite_RecordRef
    public $entityStatus; //NetSuite_RecordRef
    public $probability;
    public $tranDate;
    public $expectedCloseDate;
    public $forecastType; //NetSuite_RecordRef
    public $currencyName;
    public $exchangeRate;
    public $projectedTotal;
    public $rangeLow;
    public $rangeHigh;
    public $projAltSalesAmt;
    public $altSalesRangeLow;
    public $altSalesRangeHigh;
    public $weightedTotal;
    public $actionItem;
    public $winLossReason; //NetSuite_RecordRef
    public $memo;
    public $billAddressList; //NetSuite_RecordRef
    public $billAddress;
    public $shipAddressList; //NetSuite_RecordRef
    public $shipAddress;
    public $class; //NetSuite_RecordRef
    public $closeDate;
    public $createdDate;
    public $lastModifiedDate;
    public $department; //NetSuite_RecordRef
    public $location; //NetSuite_RecordRef
    public $status;
    public $vatRegNum;
    public $salesTeamList; //NetSuite_OpportunitySalesTeamList
    public $itemList; //NetSuite_OpportunityItemList
    public $competitorsList; //NetSuite_OpportunityCompetitorsList
    public $customFieldList; //NetSuite_CustomFieldList
    public $internalId;
    public $externalId;

    public function __construct(  NetSuite_RecordRef $customForm, NetSuite_RecordRef $entity, $title, $tranId, NetSuite_RecordRef $salesRep, NetSuite_RecordRef $partner, NetSuite_RecordRef $salesGroup, $syncSalesTeams, NetSuite_RecordRef $leadSource, NetSuite_RecordRef $entityStatus, $probability, $tranDate, $expectedCloseDate, NetSuite_RecordRef $forecastType, $currencyName, $exchangeRate, $projectedTotal, $rangeLow, $rangeHigh, $projAltSalesAmt, $altSalesRangeLow, $altSalesRangeHigh, $weightedTotal, $actionItem, NetSuite_RecordRef $winLossReason, $memo, NetSuite_RecordRef $billAddressList, $billAddress, NetSuite_RecordRef $shipAddressList, $shipAddress, NetSuite_RecordRef $class, $closeDate, $createdDate, $lastModifiedDate, NetSuite_RecordRef $department, NetSuite_RecordRef $location, $status, $vatRegNum, NetSuite_OpportunitySalesTeamList $salesTeamList, NetSuite_OpportunityItemList $itemList, NetSuite_OpportunityCompetitorsList $competitorsList, NetSuite_CustomFieldList $customFieldList, $internalId, $externalId) {
        $this->customForm = $customForm;
        $this->entity = $entity;
        $this->title = $title;
        $this->tranId = $tranId;
        $this->salesRep = $salesRep;
        $this->partner = $partner;
        $this->salesGroup = $salesGroup;
        $this->syncSalesTeams = $syncSalesTeams;
        $this->leadSource = $leadSource;
        $this->entityStatus = $entityStatus;
        $this->probability = $probability;
        $this->tranDate = $tranDate;
        $this->expectedCloseDate = $expectedCloseDate;
        $this->forecastType = $forecastType;
        $this->currencyName = $currencyName;
        $this->exchangeRate = $exchangeRate;
        $this->projectedTotal = $projectedTotal;
        $this->rangeLow = $rangeLow;
        $this->rangeHigh = $rangeHigh;
        $this->projAltSalesAmt = $projAltSalesAmt;
        $this->altSalesRangeLow = $altSalesRangeLow;
        $this->altSalesRangeHigh = $altSalesRangeHigh;
        $this->weightedTotal = $weightedTotal;
        $this->actionItem = $actionItem;
        $this->winLossReason = $winLossReason;
        $this->memo = $memo;
        $this->billAddressList = $billAddressList;
        $this->billAddress = $billAddress;
        $this->shipAddressList = $shipAddressList;
        $this->shipAddress = $shipAddress;
        $this->class = $class;
        $this->closeDate = $closeDate;
        $this->createdDate = $createdDate;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->department = $department;
        $this->location = $location;
        $this->status = $status;
        $this->vatRegNum = $vatRegNum;
        $this->salesTeamList = $salesTeamList;
        $this->itemList = $itemList;
        $this->competitorsList = $competitorsList;
        $this->customFieldList = $customFieldList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>