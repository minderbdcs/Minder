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
 * Estimate map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_Estimate {
    public $createdDate;
    public $lastModifiedDate;
    public $entity; //NetSuite_RecordRef
    public $job; //NetSuite_RecordRef
    public $tranDate;
    public $tranId;
    public $customForm; //NetSuite_RecordRef
    public $entityStatus; //NetSuite_RecordRef
    public $probability;
    public $forecastType; //NetSuite_RecordRef
    public $opportunity; //NetSuite_RecordRef
    public $department; //NetSuite_RecordRef
    public $class; //NetSuite_RecordRef
    public $terms; //NetSuite_RecordRef
    public $dueDate;
    public $location; //NetSuite_RecordRef
    public $status;
    public $salesRep; //NetSuite_RecordRef
    public $partner; //NetSuite_RecordRef
    public $leadSource; //NetSuite_RecordRef
    public $expectedCloseDate;
    public $otherRefNum;
    public $memo;
    public $endDate;
    public $startDate;
    public $createdFrom; //NetSuite_RecordRef
    public $exchangeRate;
    public $currencyName;
    public $promoCode; //NetSuite_RecordRef
    public $discountItem; //NetSuite_RecordRef
    public $discountRate;
    public $isTaxable;
    public $taxItem; //NetSuite_RecordRef
    public $taxRate;
    public $vatRegNum;
    public $toBePrinted;
    public $toBeEmailed;
    public $email;
    public $toBeFaxed;
    public $fax;
    public $messageSel; //NetSuite_RecordRef
    public $message;
    public $billAddressList; //NetSuite_RecordRef
    public $billAddress;
    public $shipAddressList; //NetSuite_RecordRef
    public $shipAddress;
    public $fob;
    public $shipDate;
    public $shipMethod; //NetSuite_RecordRef
    public $shippingCost;
    public $shippingTax1Rate;
    public $shippingTaxCode; //NetSuite_RecordRef
    public $handlingTaxCode; //NetSuite_RecordRef
    public $handlingTax1Rate;
    public $handlingCost;
    public $trackingNumbers;
    public $salesGroup; //NetSuite_RecordRef
    public $syncSalesTeams;
    public $altSalesTotal;
    public $subTotal;
    public $discountTotal;
    public $taxTotal;
    public $altShippingCost;
    public $altHandlingCost;
    public $total;
    public $itemList; //NetSuite_EstimateItemList
    public $salesTeamList; //NetSuite_EstimateSalesTeamList
    public $customFieldList; //NetSuite_CustomFieldList
    public $internalId;
    public $externalId;

    public function __construct(  $createdDate, $lastModifiedDate, NetSuite_RecordRef $entity, NetSuite_RecordRef $job, $tranDate, $tranId, NetSuite_RecordRef $customForm, NetSuite_RecordRef $entityStatus, $probability, NetSuite_RecordRef $forecastType, NetSuite_RecordRef $opportunity, NetSuite_RecordRef $department, NetSuite_RecordRef $class, NetSuite_RecordRef $terms, $dueDate, NetSuite_RecordRef $location, $status, NetSuite_RecordRef $salesRep, NetSuite_RecordRef $partner, NetSuite_RecordRef $leadSource, $expectedCloseDate, $otherRefNum, $memo, $endDate, $startDate, NetSuite_RecordRef $createdFrom, $exchangeRate, $currencyName, NetSuite_RecordRef $promoCode, NetSuite_RecordRef $discountItem, $discountRate, $isTaxable, NetSuite_RecordRef $taxItem, $taxRate, $vatRegNum, $toBePrinted, $toBeEmailed, $email, $toBeFaxed, $fax, NetSuite_RecordRef $messageSel, $message, NetSuite_RecordRef $billAddressList, $billAddress, NetSuite_RecordRef $shipAddressList, $shipAddress, $fob, $shipDate, NetSuite_RecordRef $shipMethod, $shippingCost, $shippingTax1Rate, NetSuite_RecordRef $shippingTaxCode, NetSuite_RecordRef $handlingTaxCode, $handlingTax1Rate, $handlingCost, $trackingNumbers, NetSuite_RecordRef $salesGroup, $syncSalesTeams, $altSalesTotal, $subTotal, $discountTotal, $taxTotal, $altShippingCost, $altHandlingCost, $total, NetSuite_EstimateItemList $itemList, NetSuite_EstimateSalesTeamList $salesTeamList, NetSuite_CustomFieldList $customFieldList, $internalId, $externalId) {
        $this->createdDate = $createdDate;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->entity = $entity;
        $this->job = $job;
        $this->tranDate = $tranDate;
        $this->tranId = $tranId;
        $this->customForm = $customForm;
        $this->entityStatus = $entityStatus;
        $this->probability = $probability;
        $this->forecastType = $forecastType;
        $this->opportunity = $opportunity;
        $this->department = $department;
        $this->class = $class;
        $this->terms = $terms;
        $this->dueDate = $dueDate;
        $this->location = $location;
        $this->status = $status;
        $this->salesRep = $salesRep;
        $this->partner = $partner;
        $this->leadSource = $leadSource;
        $this->expectedCloseDate = $expectedCloseDate;
        $this->otherRefNum = $otherRefNum;
        $this->memo = $memo;
        $this->endDate = $endDate;
        $this->startDate = $startDate;
        $this->createdFrom = $createdFrom;
        $this->exchangeRate = $exchangeRate;
        $this->currencyName = $currencyName;
        $this->promoCode = $promoCode;
        $this->discountItem = $discountItem;
        $this->discountRate = $discountRate;
        $this->isTaxable = $isTaxable;
        $this->taxItem = $taxItem;
        $this->taxRate = $taxRate;
        $this->vatRegNum = $vatRegNum;
        $this->toBePrinted = $toBePrinted;
        $this->toBeEmailed = $toBeEmailed;
        $this->email = $email;
        $this->toBeFaxed = $toBeFaxed;
        $this->fax = $fax;
        $this->messageSel = $messageSel;
        $this->message = $message;
        $this->billAddressList = $billAddressList;
        $this->billAddress = $billAddress;
        $this->shipAddressList = $shipAddressList;
        $this->shipAddress = $shipAddress;
        $this->fob = $fob;
        $this->shipDate = $shipDate;
        $this->shipMethod = $shipMethod;
        $this->shippingCost = $shippingCost;
        $this->shippingTax1Rate = $shippingTax1Rate;
        $this->shippingTaxCode = $shippingTaxCode;
        $this->handlingTaxCode = $handlingTaxCode;
        $this->handlingTax1Rate = $handlingTax1Rate;
        $this->handlingCost = $handlingCost;
        $this->trackingNumbers = $trackingNumbers;
        $this->salesGroup = $salesGroup;
        $this->syncSalesTeams = $syncSalesTeams;
        $this->altSalesTotal = $altSalesTotal;
        $this->subTotal = $subTotal;
        $this->discountTotal = $discountTotal;
        $this->taxTotal = $taxTotal;
        $this->altShippingCost = $altShippingCost;
        $this->altHandlingCost = $altHandlingCost;
        $this->total = $total;
        $this->itemList = $itemList;
        $this->salesTeamList = $salesTeamList;
        $this->customFieldList = $customFieldList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>