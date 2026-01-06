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
 * CashSale map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_CashSale {
    public $createdDate;
    public $lastModifiedDate;
    public $customForm; //NetSuite_RecordRef
    public $entity; //NetSuite_RecordRef
    public $tranDate;
    public $tranId;
    public $postingPeriod; //NetSuite_RecordRef
    public $createdFrom; //NetSuite_RecordRef
    public $opportunity; //NetSuite_RecordRef
    public $department; //NetSuite_RecordRef
    public $class; //NetSuite_RecordRef
    public $location; //NetSuite_RecordRef
    public $salesRep; //NetSuite_RecordRef
    public $partner; //NetSuite_RecordRef
    public $leadSource; //NetSuite_RecordRef
    public $startDate;
    public $endDate;
    public $otherRefNum;
    public $memo;
    public $revRecSchedule; //NetSuite_RecordRef
    public $undepFunds;
    public $account; //NetSuite_RecordRef
    public $revRecStartDate;
    public $revRecEndDate;
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
    public $syncSalesTeams;
    public $paymentMethod; //NetSuite_RecordRef
    public $payPalStatus;
    public $creditCard; //NetSuite_RecordRef
    public $ccNumber;
    public $ccExpireDate;
    public $ccName;
    public $ccStreet;
    public $ccZipCode;
    public $creditCardProcessor; //NetSuite_RecordRef
    public $ccApproved;
    public $authCode;
    public $ccAvsStreetMatch;
    public $ccAvsZipMatch;
    public $payPalTranId;
    public $subTotal;
    public $discountTotal;
    public $taxTotal;
    public $altShippingCost;
    public $altHandlingCost;
    public $total;
    public $ccSecurityCode;
    public $ccSecurityCodeMatch;
    public $chargeIt;
    public $debitCardIssueNo;
    public $pnRefNum;
    public $status;
    public $billingSchedule; //NetSuite_RecordRef
    public $email;
    public $tax2Total;
    public $validFrom;
    public $vatRegNum;
    public $salesTeamList; //NetSuite_CashSaleSalesTeamList
    public $itemList; //NetSuite_CashSaleItemList
    public $itemCostList; //NetSuite_CashSaleItemCostList
    public $expCostList; //NetSuite_CashSaleExpCostList
    public $timeList; //NetSuite_CashSaleTimeList
    public $customFieldList; //NetSuite_CustomFieldList
    public $internalId;
    public $externalId;

    public function __construct(  $createdDate, $lastModifiedDate, NetSuite_RecordRef $customForm, NetSuite_RecordRef $entity, $tranDate, $tranId, NetSuite_RecordRef $postingPeriod, NetSuite_RecordRef $createdFrom, NetSuite_RecordRef $opportunity, NetSuite_RecordRef $department, NetSuite_RecordRef $class, NetSuite_RecordRef $location, NetSuite_RecordRef $salesRep, NetSuite_RecordRef $partner, NetSuite_RecordRef $leadSource, $startDate, $endDate, $otherRefNum, $memo, NetSuite_RecordRef $revRecSchedule, $undepFunds, NetSuite_RecordRef $account, $revRecStartDate, $revRecEndDate, $exchangeRate, $currencyName, NetSuite_RecordRef $promoCode, NetSuite_RecordRef $discountItem, $discountRate, $isTaxable, NetSuite_RecordRef $taxItem, $taxRate, $toBePrinted, $toBeEmailed, $toBeFaxed, $fax, NetSuite_RecordRef $messageSel, $message, NetSuite_RecordRef $billAddressList, $billAddress, NetSuite_RecordRef $shipAddressList, $shipAddress, $fob, $shipDate, NetSuite_RecordRef $shipMethod, $shippingCost, $shippingTax1Rate, NetSuite_RecordRef $shippingTaxCode, NetSuite_RecordRef $handlingTaxCode, $handlingTax1Rate, $handlingCost, $trackingNumbers, NetSuite_RecordRef $salesGroup, $syncSalesTeams, NetSuite_RecordRef $paymentMethod, $payPalStatus, NetSuite_RecordRef $creditCard, $ccNumber, $ccExpireDate, $ccName, $ccStreet, $ccZipCode, NetSuite_RecordRef $creditCardProcessor, $ccApproved, $authCode, $ccAvsStreetMatch, $ccAvsZipMatch, $payPalTranId, $subTotal, $discountTotal, $taxTotal, $altShippingCost, $altHandlingCost, $total, $ccSecurityCode, $ccSecurityCodeMatch, $chargeIt, $debitCardIssueNo, $pnRefNum, $status, NetSuite_RecordRef $billingSchedule, $email, $tax2Total, $validFrom, $vatRegNum, NetSuite_CashSaleSalesTeamList $salesTeamList, NetSuite_CashSaleItemList $itemList, NetSuite_CashSaleItemCostList $itemCostList, NetSuite_CashSaleExpCostList $expCostList, NetSuite_CashSaleTimeList $timeList, NetSuite_CustomFieldList $customFieldList, $internalId, $externalId) {
        $this->createdDate = $createdDate;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->customForm = $customForm;
        $this->entity = $entity;
        $this->tranDate = $tranDate;
        $this->tranId = $tranId;
        $this->postingPeriod = $postingPeriod;
        $this->createdFrom = $createdFrom;
        $this->opportunity = $opportunity;
        $this->department = $department;
        $this->class = $class;
        $this->location = $location;
        $this->salesRep = $salesRep;
        $this->partner = $partner;
        $this->leadSource = $leadSource;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->otherRefNum = $otherRefNum;
        $this->memo = $memo;
        $this->revRecSchedule = $revRecSchedule;
        $this->undepFunds = $undepFunds;
        $this->account = $account;
        $this->revRecStartDate = $revRecStartDate;
        $this->revRecEndDate = $revRecEndDate;
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
        $this->syncSalesTeams = $syncSalesTeams;
        $this->paymentMethod = $paymentMethod;
        $this->payPalStatus = $payPalStatus;
        $this->creditCard = $creditCard;
        $this->ccNumber = $ccNumber;
        $this->ccExpireDate = $ccExpireDate;
        $this->ccName = $ccName;
        $this->ccStreet = $ccStreet;
        $this->ccZipCode = $ccZipCode;
        $this->creditCardProcessor = $creditCardProcessor;
        $this->ccApproved = $ccApproved;
        $this->authCode = $authCode;
        $this->ccAvsStreetMatch = $ccAvsStreetMatch;
        $this->ccAvsZipMatch = $ccAvsZipMatch;
        $this->payPalTranId = $payPalTranId;
        $this->subTotal = $subTotal;
        $this->discountTotal = $discountTotal;
        $this->taxTotal = $taxTotal;
        $this->altShippingCost = $altShippingCost;
        $this->altHandlingCost = $altHandlingCost;
        $this->total = $total;
        $this->ccSecurityCode = $ccSecurityCode;
        $this->ccSecurityCodeMatch = $ccSecurityCodeMatch;
        $this->chargeIt = $chargeIt;
        $this->debitCardIssueNo = $debitCardIssueNo;
        $this->pnRefNum = $pnRefNum;
        $this->status = $status;
        $this->billingSchedule = $billingSchedule;
        $this->email = $email;
        $this->tax2Total = $tax2Total;
        $this->validFrom = $validFrom;
        $this->vatRegNum = $vatRegNum;
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