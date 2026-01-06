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
 * Invoice map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_Invoice {
    public $createdDate;
    public $lastModifiedDate;
    public $customForm; //NetSuite_RecordRef
    public $entity; //NetSuite_RecordRef
    public $tranDate;
    public $tranId;
    public $createdFrom; //NetSuite_RecordRef
    public $postingPeriod; //NetSuite_RecordRef
    public $opportunity; //NetSuite_RecordRef
    public $department; //NetSuite_RecordRef
    public $class; //NetSuite_RecordRef
    public $terms; //NetSuite_RecordRef
    public $location; //NetSuite_RecordRef
    public $dueDate;
    public $discountDate;
    public $discountAmount;
    public $salesRep; //NetSuite_RecordRef
    public $partner; //NetSuite_RecordRef
    public $leadSource; //NetSuite_RecordRef
    public $startDate;
    public $endDate;
    public $otherRefNum;
    public $memo;
    public $revRecSchedule; //NetSuite_RecordRef
    public $revRecStartDate;
    public $revRecEndDate;
    public $amountPaid;
    public $amountRemaining;
    public $balance;
    public $account; //NetSuite_RecordRef
    public $exchangeRate;
    public $currencyName;
    public $promoCode; //NetSuite_RecordRef
    public $discountItem; //NetSuite_RecordRef
    public $discountRate;
    public $isTaxable;
    public $taxItem; //NetSuite_RecordRef
    public $taxRate;
    public $toBePrinted;
    public $toBeEmailed;
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
    public $subTotal;
    public $syncSalesTeams;
    public $discountTotal;
    public $taxTotal;
    public $altShippingCost;
    public $altHandlingCost;
    public $total;
    public $status;
    public $billingSchedule; //NetSuite_RecordRef
    public $email;
    public $tax2Total;
    public $vatRegNum;
    public $expCostDiscount; //NetSuite_RecordRef
    public $itemCostDiscount; //NetSuite_RecordRef
    public $timeDiscount; //NetSuite_RecordRef
    public $expCostDiscRate;
    public $itemCostDiscRate;
    public $timeDiscRate;
    public $expCostDiscAmount;
    public $itemCostDiscAmount;
    public $timeDiscAmount;
    public $expCostDiscTaxable;
    public $itemCostDiscTaxable;
    public $timeDiscTaxable;
    public $expCostDiscPrint;
    public $salesTeamList; //NetSuite_InvoiceSalesTeamList
    public $itemList; //NetSuite_InvoiceItemList
    public $itemCostList; //NetSuite_InvoiceItemCostList
    public $expCostList; //NetSuite_InvoiceExpCostList
    public $timeList; //NetSuite_InvoiceTimeList
    public $customFieldList; //NetSuite_CustomFieldList
    public $internalId;
    public $externalId;

    public function __construct(  $createdDate, $lastModifiedDate, NetSuite_RecordRef $customForm, NetSuite_RecordRef $entity, $tranDate, $tranId, NetSuite_RecordRef $createdFrom, NetSuite_RecordRef $postingPeriod, NetSuite_RecordRef $opportunity, NetSuite_RecordRef $department, NetSuite_RecordRef $class, NetSuite_RecordRef $terms, NetSuite_RecordRef $location, $dueDate, $discountDate, $discountAmount, NetSuite_RecordRef $salesRep, NetSuite_RecordRef $partner, NetSuite_RecordRef $leadSource, $startDate, $endDate, $otherRefNum, $memo, NetSuite_RecordRef $revRecSchedule, $revRecStartDate, $revRecEndDate, $amountPaid, $amountRemaining, $balance, NetSuite_RecordRef $account, $exchangeRate, $currencyName, NetSuite_RecordRef $promoCode, NetSuite_RecordRef $discountItem, $discountRate, $isTaxable, NetSuite_RecordRef $taxItem, $taxRate, $toBePrinted, $toBeEmailed, $toBeFaxed, $fax, NetSuite_RecordRef $messageSel, $message, NetSuite_RecordRef $billAddressList, $billAddress, NetSuite_RecordRef $shipAddressList, $shipAddress, $fob, $shipDate, NetSuite_RecordRef $shipMethod, $shippingCost, $shippingTax1Rate, NetSuite_RecordRef $shippingTaxCode, NetSuite_RecordRef $handlingTaxCode, $handlingTax1Rate, $handlingCost, $trackingNumbers, NetSuite_RecordRef $salesGroup, $subTotal, $syncSalesTeams, $discountTotal, $taxTotal, $altShippingCost, $altHandlingCost, $total, $status, NetSuite_RecordRef $billingSchedule, $email, $tax2Total, $vatRegNum, NetSuite_RecordRef $expCostDiscount, NetSuite_RecordRef $itemCostDiscount, NetSuite_RecordRef $timeDiscount, $expCostDiscRate, $itemCostDiscRate, $timeDiscRate, $expCostDiscAmount, $itemCostDiscAmount, $timeDiscAmount, $expCostDiscTaxable, $itemCostDiscTaxable, $timeDiscTaxable, $expCostDiscPrint, NetSuite_InvoiceSalesTeamList $salesTeamList, NetSuite_InvoiceItemList $itemList, NetSuite_InvoiceItemCostList $itemCostList, NetSuite_InvoiceExpCostList $expCostList, NetSuite_InvoiceTimeList $timeList, NetSuite_CustomFieldList $customFieldList, $internalId, $externalId) {
        $this->createdDate = $createdDate;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->customForm = $customForm;
        $this->entity = $entity;
        $this->tranDate = $tranDate;
        $this->tranId = $tranId;
        $this->createdFrom = $createdFrom;
        $this->postingPeriod = $postingPeriod;
        $this->opportunity = $opportunity;
        $this->department = $department;
        $this->class = $class;
        $this->terms = $terms;
        $this->location = $location;
        $this->dueDate = $dueDate;
        $this->discountDate = $discountDate;
        $this->discountAmount = $discountAmount;
        $this->salesRep = $salesRep;
        $this->partner = $partner;
        $this->leadSource = $leadSource;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->otherRefNum = $otherRefNum;
        $this->memo = $memo;
        $this->revRecSchedule = $revRecSchedule;
        $this->revRecStartDate = $revRecStartDate;
        $this->revRecEndDate = $revRecEndDate;
        $this->amountPaid = $amountPaid;
        $this->amountRemaining = $amountRemaining;
        $this->balance = $balance;
        $this->account = $account;
        $this->exchangeRate = $exchangeRate;
        $this->currencyName = $currencyName;
        $this->promoCode = $promoCode;
        $this->discountItem = $discountItem;
        $this->discountRate = $discountRate;
        $this->isTaxable = $isTaxable;
        $this->taxItem = $taxItem;
        $this->taxRate = $taxRate;
        $this->toBePrinted = $toBePrinted;
        $this->toBeEmailed = $toBeEmailed;
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
        $this->subTotal = $subTotal;
        $this->syncSalesTeams = $syncSalesTeams;
        $this->discountTotal = $discountTotal;
        $this->taxTotal = $taxTotal;
        $this->altShippingCost = $altShippingCost;
        $this->altHandlingCost = $altHandlingCost;
        $this->total = $total;
        $this->status = $status;
        $this->billingSchedule = $billingSchedule;
        $this->email = $email;
        $this->tax2Total = $tax2Total;
        $this->vatRegNum = $vatRegNum;
        $this->expCostDiscount = $expCostDiscount;
        $this->itemCostDiscount = $itemCostDiscount;
        $this->timeDiscount = $timeDiscount;
        $this->expCostDiscRate = $expCostDiscRate;
        $this->itemCostDiscRate = $itemCostDiscRate;
        $this->timeDiscRate = $timeDiscRate;
        $this->expCostDiscAmount = $expCostDiscAmount;
        $this->itemCostDiscAmount = $itemCostDiscAmount;
        $this->timeDiscAmount = $timeDiscAmount;
        $this->expCostDiscTaxable = $expCostDiscTaxable;
        $this->itemCostDiscTaxable = $itemCostDiscTaxable;
        $this->timeDiscTaxable = $timeDiscTaxable;
        $this->expCostDiscPrint = $expCostDiscPrint;
        $this->salesTeamList = $salesTeamList;
        $this->itemList = $itemList;
        $this->itemCostList = $itemCostList;
        $this->expCostList = $expCostList;
        $this->timeList = $timeList;
        $this->customFieldList = $customFieldList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>