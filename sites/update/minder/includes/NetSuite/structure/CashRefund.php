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
 * CashRefund map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_CashRefund {
    public $createdDate;
    public $lastModifiedDate;
    public $customForm; //NetSuite_RecordRef
    public $entity; //NetSuite_RecordRef
    public $vatRegNum;
    public $tranDate;
    public $tranId;
    public $createdFrom; //NetSuite_RecordRef
    public $postingPeriod; //NetSuite_RecordRef
    public $department; //NetSuite_RecordRef
    public $class; //NetSuite_RecordRef
    public $location; //NetSuite_RecordRef
    public $salesRep; //NetSuite_RecordRef
    public $partner; //NetSuite_RecordRef
    public $otherRefNum;
    public $memo;
    public $refundCheck;
    public $toPrint2;
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
    public $email;
    public $toBeFaxed;
    public $fax;
    public $messageSel; //NetSuite_RecordRef
    public $message;
    public $billAddressList; //NetSuite_RecordRef
    public $billAddress;
    public $shipMethod; //NetSuite_RecordRef
    public $shippingTaxCode; //NetSuite_RecordRef
    public $shippingTax1Rate;
    public $shippingCost;
    public $handlingTaxCode; //NetSuite_RecordRef
    public $handlingTax1Rate;
    public $handlingCost;
    public $salesGroup; //NetSuite_RecordRef
    public $syncSalesTeams;
    public $paymentMethod; //NetSuite_RecordRef
    public $creditCard; //NetSuite_RecordRef
    public $chargeIt;
    public $ccNumber;
    public $ccExpireDate;
    public $ccName;
    public $ccStreet;
    public $ccZipCode;
    public $ccApproved;
    public $creditCardProcessor; //NetSuite_RecordRef
    public $debitCardIssueNo;
    public $pnRefNum;
    public $validFrom;
    public $subTotal;
    public $discountTotal;
    public $taxTotal;
    public $tax2Total;
    public $altShippingCost;
    public $altHandlingCost;
    public $total;
    public $status;
    public $itemList; //NetSuite_CashRefundItemList
    public $salesTeamList; //NetSuite_CashRefundSalesTeamList
    public $customFieldList; //NetSuite_CustomFieldList
    public $internalId;
    public $externalId;

    public function __construct(  $createdDate, $lastModifiedDate, NetSuite_RecordRef $customForm, NetSuite_RecordRef $entity, $vatRegNum, $tranDate, $tranId, NetSuite_RecordRef $createdFrom, NetSuite_RecordRef $postingPeriod, NetSuite_RecordRef $department, NetSuite_RecordRef $class, NetSuite_RecordRef $location, NetSuite_RecordRef $salesRep, NetSuite_RecordRef $partner, $otherRefNum, $memo, $refundCheck, $toPrint2, NetSuite_RecordRef $account, $exchangeRate, $currencyName, NetSuite_RecordRef $promoCode, NetSuite_RecordRef $discountItem, $discountRate, $isTaxable, NetSuite_RecordRef $taxItem, $taxRate, $toBePrinted, $toBeEmailed, $email, $toBeFaxed, $fax, NetSuite_RecordRef $messageSel, $message, NetSuite_RecordRef $billAddressList, $billAddress, NetSuite_RecordRef $shipMethod, NetSuite_RecordRef $shippingTaxCode, $shippingTax1Rate, $shippingCost, NetSuite_RecordRef $handlingTaxCode, $handlingTax1Rate, $handlingCost, NetSuite_RecordRef $salesGroup, $syncSalesTeams, NetSuite_RecordRef $paymentMethod, NetSuite_RecordRef $creditCard, $chargeIt, $ccNumber, $ccExpireDate, $ccName, $ccStreet, $ccZipCode, $ccApproved, NetSuite_RecordRef $creditCardProcessor, $debitCardIssueNo, $pnRefNum, $validFrom, $subTotal, $discountTotal, $taxTotal, $tax2Total, $altShippingCost, $altHandlingCost, $total, $status, NetSuite_CashRefundItemList $itemList, NetSuite_CashRefundSalesTeamList $salesTeamList, NetSuite_CustomFieldList $customFieldList, $internalId, $externalId) {
        $this->createdDate = $createdDate;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->customForm = $customForm;
        $this->entity = $entity;
        $this->vatRegNum = $vatRegNum;
        $this->tranDate = $tranDate;
        $this->tranId = $tranId;
        $this->createdFrom = $createdFrom;
        $this->postingPeriod = $postingPeriod;
        $this->department = $department;
        $this->class = $class;
        $this->location = $location;
        $this->salesRep = $salesRep;
        $this->partner = $partner;
        $this->otherRefNum = $otherRefNum;
        $this->memo = $memo;
        $this->refundCheck = $refundCheck;
        $this->toPrint2 = $toPrint2;
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
        $this->email = $email;
        $this->toBeFaxed = $toBeFaxed;
        $this->fax = $fax;
        $this->messageSel = $messageSel;
        $this->message = $message;
        $this->billAddressList = $billAddressList;
        $this->billAddress = $billAddress;
        $this->shipMethod = $shipMethod;
        $this->shippingTaxCode = $shippingTaxCode;
        $this->shippingTax1Rate = $shippingTax1Rate;
        $this->shippingCost = $shippingCost;
        $this->handlingTaxCode = $handlingTaxCode;
        $this->handlingTax1Rate = $handlingTax1Rate;
        $this->handlingCost = $handlingCost;
        $this->salesGroup = $salesGroup;
        $this->syncSalesTeams = $syncSalesTeams;
        $this->paymentMethod = $paymentMethod;
        $this->creditCard = $creditCard;
        $this->chargeIt = $chargeIt;
        $this->ccNumber = $ccNumber;
        $this->ccExpireDate = $ccExpireDate;
        $this->ccName = $ccName;
        $this->ccStreet = $ccStreet;
        $this->ccZipCode = $ccZipCode;
        $this->ccApproved = $ccApproved;
        $this->creditCardProcessor = $creditCardProcessor;
        $this->debitCardIssueNo = $debitCardIssueNo;
        $this->pnRefNum = $pnRefNum;
        $this->validFrom = $validFrom;
        $this->subTotal = $subTotal;
        $this->discountTotal = $discountTotal;
        $this->taxTotal = $taxTotal;
        $this->tax2Total = $tax2Total;
        $this->altShippingCost = $altShippingCost;
        $this->altHandlingCost = $altHandlingCost;
        $this->total = $total;
        $this->status = $status;
        $this->itemList = $itemList;
        $this->salesTeamList = $salesTeamList;
        $this->customFieldList = $customFieldList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>