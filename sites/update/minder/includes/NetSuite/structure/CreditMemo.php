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
 * CreditMemo map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_CreditMemo {
    public $createdDate;
    public $lastModifiedDate;
    public $customForm; //NetSuite_RecordRef
    public $entity; //NetSuite_RecordRef
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
    public $unApplied;
    public $autoApply;
    public $applied;
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
    public $shippingCost;
    public $shippingTax1Rate;
    public $shippingTaxCode; //NetSuite_RecordRef
    public $handlingTaxCode; //NetSuite_RecordRef
    public $handlingTax1Rate;
    public $handlingCost;
    public $subTotal;
    public $discountTotal;
    public $taxTotal;
    public $tax2Total;
    public $altShippingCost;
    public $altHandlingCost;
    public $total;
    public $itemList; //NetSuite_CreditMemoItemList
    public $applyList; //NetSuite_CreditMemoApplyList
    public $customFieldList; //NetSuite_CustomFieldList
    public $salesGroup; //NetSuite_RecordRef
    public $syncSalesTeams;
    public $salesTeamList; //NetSuite_CreditMemoSalesTeamList
    public $status;
    public $internalId;
    public $externalId;

    public function __construct(  $createdDate, $lastModifiedDate, NetSuite_RecordRef $customForm, NetSuite_RecordRef $entity, $tranDate, $tranId, NetSuite_RecordRef $createdFrom, NetSuite_RecordRef $postingPeriod, NetSuite_RecordRef $department, NetSuite_RecordRef $class, NetSuite_RecordRef $location, NetSuite_RecordRef $salesRep, NetSuite_RecordRef $partner, $otherRefNum, $memo, $balance, NetSuite_RecordRef $account, $exchangeRate, $currencyName, NetSuite_RecordRef $promoCode, NetSuite_RecordRef $discountItem, $discountRate, $isTaxable, NetSuite_RecordRef $taxItem, $taxRate, $unApplied, $autoApply, $applied, $toBePrinted, $toBeEmailed, $email, $toBeFaxed, $fax, NetSuite_RecordRef $messageSel, $message, NetSuite_RecordRef $billAddressList, $billAddress, NetSuite_RecordRef $shipMethod, $shippingCost, $shippingTax1Rate, NetSuite_RecordRef $shippingTaxCode, NetSuite_RecordRef $handlingTaxCode, $handlingTax1Rate, $handlingCost, $subTotal, $discountTotal, $taxTotal, $tax2Total, $altShippingCost, $altHandlingCost, $total, NetSuite_CreditMemoItemList $itemList, NetSuite_CreditMemoApplyList $applyList, NetSuite_CustomFieldList $customFieldList, NetSuite_RecordRef $salesGroup, $syncSalesTeams, NetSuite_CreditMemoSalesTeamList $salesTeamList, $status, $internalId, $externalId) {
        $this->createdDate = $createdDate;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->customForm = $customForm;
        $this->entity = $entity;
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
        $this->unApplied = $unApplied;
        $this->autoApply = $autoApply;
        $this->applied = $applied;
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
        $this->shippingCost = $shippingCost;
        $this->shippingTax1Rate = $shippingTax1Rate;
        $this->shippingTaxCode = $shippingTaxCode;
        $this->handlingTaxCode = $handlingTaxCode;
        $this->handlingTax1Rate = $handlingTax1Rate;
        $this->handlingCost = $handlingCost;
        $this->subTotal = $subTotal;
        $this->discountTotal = $discountTotal;
        $this->taxTotal = $taxTotal;
        $this->tax2Total = $tax2Total;
        $this->altShippingCost = $altShippingCost;
        $this->altHandlingCost = $altHandlingCost;
        $this->total = $total;
        $this->itemList = $itemList;
        $this->applyList = $applyList;
        $this->customFieldList = $customFieldList;
        $this->salesGroup = $salesGroup;
        $this->syncSalesTeams = $syncSalesTeams;
        $this->salesTeamList = $salesTeamList;
        $this->status = $status;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>