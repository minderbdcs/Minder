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
 * ReturnAuthorization map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_ReturnAuthorization {
    public $createdDate;
    public $lastModifiedDate;
    public $customForm; //NetSuite_RecordRef
    public $entity; //NetSuite_RecordRef
    public $tranDate;
    public $tranId;
    public $department; //NetSuite_RecordRef
    public $class; //NetSuite_RecordRef
    public $location; //NetSuite_RecordRef
    public $partner; //NetSuite_RecordRef
    public $otherRefNum;
    public $memo;
    public $createdFrom; //NetSuite_RecordRef
    public $revRecSchedule; //NetSuite_RecordRef
    public $revRecStartDate;
    public $revRecEndDate;
    public $excludeCommission;
    public $exchangeRate;
    public $currencyName;
    public $discountItem; //NetSuite_RecordRef
    public $discountRate;
    public $taxItem; //NetSuite_RecordRef
    public $taxRate;
    public $toBePrinted;
    public $toBeEmailed;
    public $toBeFaxed;
    public $messageSel; //NetSuite_RecordRef
    public $message;
    public $billAddressList; //NetSuite_RecordRef
    public $billAddress;
    public $shipAddressList; //NetSuite_RecordRef
    public $shipAddress;
    public $salesGroup; //NetSuite_RecordRef
    public $syncSalesTeams;
    public $paymentMethod; //NetSuite_RecordRef
    public $creditCard; //NetSuite_RecordRef
    public $ccNumber;
    public $ccExpireDate;
    public $ccName;
    public $ccStreet;
    public $ccZipCode;
    public $ccApproved;
    public $pnRefNum;
    public $subTotal;
    public $discountTotal;
    public $total;
    public $itemList; //NetSuite_ReturnAuthorizationItemList
    public $salesTeamList; //NetSuite_ReturnAuthorizationSalesTeamList
    public $creditCardProcessor; //NetSuite_RecordRef
    public $customFieldList; //NetSuite_CustomFieldList
    public $email;
    public $fax;
    public $debitCardIssueNo;
    public $isTaxable;
    public $promoCode; //NetSuite_RecordRef
    public $status;
    public $taxTotal;
    public $tax2Total;
    public $validFrom;
    public $orderStatus;
    public $salesRep; //NetSuite_RecordRef
    public $internalId;
    public $externalId;

    public function __construct(  $createdDate, $lastModifiedDate, NetSuite_RecordRef $customForm, NetSuite_RecordRef $entity, $tranDate, $tranId, NetSuite_RecordRef $department, NetSuite_RecordRef $class, NetSuite_RecordRef $location, NetSuite_RecordRef $partner, $otherRefNum, $memo, NetSuite_RecordRef $createdFrom, NetSuite_RecordRef $revRecSchedule, $revRecStartDate, $revRecEndDate, $excludeCommission, $exchangeRate, $currencyName, NetSuite_RecordRef $discountItem, $discountRate, NetSuite_RecordRef $taxItem, $taxRate, $toBePrinted, $toBeEmailed, $toBeFaxed, NetSuite_RecordRef $messageSel, $message, NetSuite_RecordRef $billAddressList, $billAddress, NetSuite_RecordRef $shipAddressList, $shipAddress, NetSuite_RecordRef $salesGroup, $syncSalesTeams, NetSuite_RecordRef $paymentMethod, NetSuite_RecordRef $creditCard, $ccNumber, $ccExpireDate, $ccName, $ccStreet, $ccZipCode, $ccApproved, $pnRefNum, $subTotal, $discountTotal, $total, NetSuite_ReturnAuthorizationItemList $itemList, NetSuite_ReturnAuthorizationSalesTeamList $salesTeamList, NetSuite_RecordRef $creditCardProcessor, NetSuite_CustomFieldList $customFieldList, $email, $fax, $debitCardIssueNo, $isTaxable, NetSuite_RecordRef $promoCode, $status, $taxTotal, $tax2Total, $validFrom, $orderStatus, NetSuite_RecordRef $salesRep, $internalId, $externalId) {
        $this->createdDate = $createdDate;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->customForm = $customForm;
        $this->entity = $entity;
        $this->tranDate = $tranDate;
        $this->tranId = $tranId;
        $this->department = $department;
        $this->class = $class;
        $this->location = $location;
        $this->partner = $partner;
        $this->otherRefNum = $otherRefNum;
        $this->memo = $memo;
        $this->createdFrom = $createdFrom;
        $this->revRecSchedule = $revRecSchedule;
        $this->revRecStartDate = $revRecStartDate;
        $this->revRecEndDate = $revRecEndDate;
        $this->excludeCommission = $excludeCommission;
        $this->exchangeRate = $exchangeRate;
        $this->currencyName = $currencyName;
        $this->discountItem = $discountItem;
        $this->discountRate = $discountRate;
        $this->taxItem = $taxItem;
        $this->taxRate = $taxRate;
        $this->toBePrinted = $toBePrinted;
        $this->toBeEmailed = $toBeEmailed;
        $this->toBeFaxed = $toBeFaxed;
        $this->messageSel = $messageSel;
        $this->message = $message;
        $this->billAddressList = $billAddressList;
        $this->billAddress = $billAddress;
        $this->shipAddressList = $shipAddressList;
        $this->shipAddress = $shipAddress;
        $this->salesGroup = $salesGroup;
        $this->syncSalesTeams = $syncSalesTeams;
        $this->paymentMethod = $paymentMethod;
        $this->creditCard = $creditCard;
        $this->ccNumber = $ccNumber;
        $this->ccExpireDate = $ccExpireDate;
        $this->ccName = $ccName;
        $this->ccStreet = $ccStreet;
        $this->ccZipCode = $ccZipCode;
        $this->ccApproved = $ccApproved;
        $this->pnRefNum = $pnRefNum;
        $this->subTotal = $subTotal;
        $this->discountTotal = $discountTotal;
        $this->total = $total;
        $this->itemList = $itemList;
        $this->salesTeamList = $salesTeamList;
        $this->creditCardProcessor = $creditCardProcessor;
        $this->customFieldList = $customFieldList;
        $this->email = $email;
        $this->fax = $fax;
        $this->debitCardIssueNo = $debitCardIssueNo;
        $this->isTaxable = $isTaxable;
        $this->promoCode = $promoCode;
        $this->status = $status;
        $this->taxTotal = $taxTotal;
        $this->tax2Total = $tax2Total;
        $this->validFrom = $validFrom;
        $this->orderStatus = $orderStatus;
        $this->salesRep = $salesRep;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>