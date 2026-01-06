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
 * CustomerSearchBasic map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_CustomerSearchBasic {
    public $accountNumber; //NetSuite_SearchStringField
    public $address; //NetSuite_SearchStringField
    public $availableOffline; //NetSuite_SearchBooleanField
    public $balance; //NetSuite_SearchDoubleField
    public $boughtAmount; //NetSuite_SearchDoubleField
    public $boughtDate; //NetSuite_SearchDateField
    public $category; //NetSuite_SearchMultiSelectField
    public $ccCustomerCode; //NetSuite_SearchStringField
    public $ccDefault; //NetSuite_SearchBooleanField
    public $ccExpDate; //NetSuite_SearchDateField
    public $ccHolderName; //NetSuite_SearchStringField
    public $ccNumber; //NetSuite_SearchStringField
    public $ccType; //NetSuite_SearchMultiSelectField
    public $city; //NetSuite_SearchStringField
    public $classBought; //NetSuite_SearchMultiSelectField
    public $comments; //NetSuite_SearchStringField
    public $contact; //NetSuite_SearchStringField
    public $contribution; //NetSuite_SearchLongField
    public $country; //NetSuite_SearchEnumMultiSelectField
    public $county; //NetSuite_SearchStringField
    public $creditHoldOverride; //NetSuite_SearchBooleanField
    public $creditLimit; //NetSuite_SearchDoubleField
    public $currency; //NetSuite_SearchMultiSelectField
    public $custStage; //NetSuite_SearchMultiSelectField
    public $custStatus; //NetSuite_SearchMultiSelectField
    public $dateClosed; //NetSuite_SearchDateField
    public $dateCreated; //NetSuite_SearchDateField
    public $daysOverdue; //NetSuite_SearchLongField
    public $deptBought; //NetSuite_SearchMultiSelectField
    public $email; //NetSuite_SearchStringField
    public $endDate; //NetSuite_SearchDateField
    public $entityId; //NetSuite_SearchStringField
    public $entityStatus; //NetSuite_SearchMultiSelectField
    public $externalId; //NetSuite_SearchMultiSelectField
    public $fax; //NetSuite_SearchStringField
    public $firstName; //NetSuite_SearchStringField
    public $firstSaleDate; //NetSuite_SearchDateField
    public $giveAccess; //NetSuite_SearchBooleanField
    public $group; //NetSuite_SearchMultiSelectField
    public $hasDuplicates; //NetSuite_SearchBooleanField
    public $image; //NetSuite_SearchStringField
    public $internalId; //NetSuite_SearchMultiSelectField
    public $isInactive; //NetSuite_SearchBooleanField
    public $isPerson; //NetSuite_SearchBooleanField
    public $isReportedLead; //NetSuite_SearchBooleanField
    public $isShipAddress; //NetSuite_SearchBooleanField
    public $itemsBought; //NetSuite_SearchMultiSelectField
    public $itemsOrdered; //NetSuite_SearchMultiSelectField
    public $language; //NetSuite_SearchStringField
    public $lastModifiedDate; //NetSuite_SearchDateField
    public $lastName; //NetSuite_SearchStringField
    public $leadSource; //NetSuite_SearchMultiSelectField
    public $locationBought; //NetSuite_SearchMultiSelectField
    public $manualCreditHold; //NetSuite_SearchBooleanField
    public $merchantAccount; //NetSuite_SearchMultiSelectField
    public $middleName; //NetSuite_SearchStringField
    public $onCreditHold; //NetSuite_SearchBooleanField
    public $overdueBalance; //NetSuite_SearchDoubleField
    public $parent; //NetSuite_SearchMultiSelectField
    public $parentItemsBought; //NetSuite_SearchMultiSelectField
    public $partner; //NetSuite_SearchMultiSelectField
    public $pec; //NetSuite_SearchStringField
    public $phone; //NetSuite_SearchStringField
    public $phoneticName; //NetSuite_SearchStringField
    public $priceLevel; //NetSuite_SearchMultiSelectField
    public $prospectDate; //NetSuite_SearchDateField
    public $pstExempt; //NetSuite_SearchBooleanField
    public $reminderDate; //NetSuite_SearchDateField
    public $resaleNumber; //NetSuite_SearchStringField
    public $salesRep; //NetSuite_SearchMultiSelectField
    public $salesTeamMember; //NetSuite_SearchMultiSelectField
    public $salesTeamRole; //NetSuite_SearchMultiSelectField
    public $salutation; //NetSuite_SearchStringField
    public $shipComplete; //NetSuite_SearchBooleanField
    public $shippingItem; //NetSuite_SearchMultiSelectField
    public $stage; //NetSuite_SearchEnumMultiSelectField
    public $startDate; //NetSuite_SearchDateField
    public $state; //NetSuite_SearchStringField
    public $taxable; //NetSuite_SearchBooleanField
    public $territory; //NetSuite_SearchMultiSelectField
    public $unsubscribe; //NetSuite_SearchBooleanField
    public $url; //NetSuite_SearchStringField
    public $webLead; //NetSuite_SearchBooleanField
    public $zipCode; //NetSuite_SearchStringField
    public $customFieldList; //NetSuite_SearchCustomFieldList

    public function __construct(  NetSuite_SearchStringField $accountNumber, NetSuite_SearchStringField $address, NetSuite_SearchBooleanField $availableOffline, NetSuite_SearchDoubleField $balance, NetSuite_SearchDoubleField $boughtAmount, NetSuite_SearchDateField $boughtDate, NetSuite_SearchMultiSelectField $category, NetSuite_SearchStringField $ccCustomerCode, NetSuite_SearchBooleanField $ccDefault, NetSuite_SearchDateField $ccExpDate, NetSuite_SearchStringField $ccHolderName, NetSuite_SearchStringField $ccNumber, NetSuite_SearchMultiSelectField $ccType, NetSuite_SearchStringField $city, NetSuite_SearchMultiSelectField $classBought, NetSuite_SearchStringField $comments, NetSuite_SearchStringField $contact, NetSuite_SearchLongField $contribution, NetSuite_SearchEnumMultiSelectField $country, NetSuite_SearchStringField $county, NetSuite_SearchBooleanField $creditHoldOverride, NetSuite_SearchDoubleField $creditLimit, NetSuite_SearchMultiSelectField $currency, NetSuite_SearchMultiSelectField $custStage, NetSuite_SearchMultiSelectField $custStatus, NetSuite_SearchDateField $dateClosed, NetSuite_SearchDateField $dateCreated, NetSuite_SearchLongField $daysOverdue, NetSuite_SearchMultiSelectField $deptBought, NetSuite_SearchStringField $email, NetSuite_SearchDateField $endDate, NetSuite_SearchStringField $entityId, NetSuite_SearchMultiSelectField $entityStatus, NetSuite_SearchMultiSelectField $externalId, NetSuite_SearchStringField $fax, NetSuite_SearchStringField $firstName, NetSuite_SearchDateField $firstSaleDate, NetSuite_SearchBooleanField $giveAccess, NetSuite_SearchMultiSelectField $group, NetSuite_SearchBooleanField $hasDuplicates, NetSuite_SearchStringField $image, NetSuite_SearchMultiSelectField $internalId, NetSuite_SearchBooleanField $isInactive, NetSuite_SearchBooleanField $isPerson, NetSuite_SearchBooleanField $isReportedLead, NetSuite_SearchBooleanField $isShipAddress, NetSuite_SearchMultiSelectField $itemsBought, NetSuite_SearchMultiSelectField $itemsOrdered, NetSuite_SearchStringField $language, NetSuite_SearchDateField $lastModifiedDate, NetSuite_SearchStringField $lastName, NetSuite_SearchMultiSelectField $leadSource, NetSuite_SearchMultiSelectField $locationBought, NetSuite_SearchBooleanField $manualCreditHold, NetSuite_SearchMultiSelectField $merchantAccount, NetSuite_SearchStringField $middleName, NetSuite_SearchBooleanField $onCreditHold, NetSuite_SearchDoubleField $overdueBalance, NetSuite_SearchMultiSelectField $parent, NetSuite_SearchMultiSelectField $parentItemsBought, NetSuite_SearchMultiSelectField $partner, NetSuite_SearchStringField $pec, NetSuite_SearchStringField $phone, NetSuite_SearchStringField $phoneticName, NetSuite_SearchMultiSelectField $priceLevel, NetSuite_SearchDateField $prospectDate, NetSuite_SearchBooleanField $pstExempt, NetSuite_SearchDateField $reminderDate, NetSuite_SearchStringField $resaleNumber, NetSuite_SearchMultiSelectField $salesRep, NetSuite_SearchMultiSelectField $salesTeamMember, NetSuite_SearchMultiSelectField $salesTeamRole, NetSuite_SearchStringField $salutation, NetSuite_SearchBooleanField $shipComplete, NetSuite_SearchMultiSelectField $shippingItem, NetSuite_SearchEnumMultiSelectField $stage, NetSuite_SearchDateField $startDate, NetSuite_SearchStringField $state, NetSuite_SearchBooleanField $taxable, NetSuite_SearchMultiSelectField $territory, NetSuite_SearchBooleanField $unsubscribe, NetSuite_SearchStringField $url, NetSuite_SearchBooleanField $webLead, NetSuite_SearchStringField $zipCode, NetSuite_SearchCustomFieldList $customFieldList) {
        $this->accountNumber = $accountNumber;
        $this->address = $address;
        $this->availableOffline = $availableOffline;
        $this->balance = $balance;
        $this->boughtAmount = $boughtAmount;
        $this->boughtDate = $boughtDate;
        $this->category = $category;
        $this->ccCustomerCode = $ccCustomerCode;
        $this->ccDefault = $ccDefault;
        $this->ccExpDate = $ccExpDate;
        $this->ccHolderName = $ccHolderName;
        $this->ccNumber = $ccNumber;
        $this->ccType = $ccType;
        $this->city = $city;
        $this->classBought = $classBought;
        $this->comments = $comments;
        $this->contact = $contact;
        $this->contribution = $contribution;
        $this->country = $country;
        $this->county = $county;
        $this->creditHoldOverride = $creditHoldOverride;
        $this->creditLimit = $creditLimit;
        $this->currency = $currency;
        $this->custStage = $custStage;
        $this->custStatus = $custStatus;
        $this->dateClosed = $dateClosed;
        $this->dateCreated = $dateCreated;
        $this->daysOverdue = $daysOverdue;
        $this->deptBought = $deptBought;
        $this->email = $email;
        $this->endDate = $endDate;
        $this->entityId = $entityId;
        $this->entityStatus = $entityStatus;
        $this->externalId = $externalId;
        $this->fax = $fax;
        $this->firstName = $firstName;
        $this->firstSaleDate = $firstSaleDate;
        $this->giveAccess = $giveAccess;
        $this->group = $group;
        $this->hasDuplicates = $hasDuplicates;
        $this->image = $image;
        $this->internalId = $internalId;
        $this->isInactive = $isInactive;
        $this->isPerson = $isPerson;
        $this->isReportedLead = $isReportedLead;
        $this->isShipAddress = $isShipAddress;
        $this->itemsBought = $itemsBought;
        $this->itemsOrdered = $itemsOrdered;
        $this->language = $language;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->lastName = $lastName;
        $this->leadSource = $leadSource;
        $this->locationBought = $locationBought;
        $this->manualCreditHold = $manualCreditHold;
        $this->merchantAccount = $merchantAccount;
        $this->middleName = $middleName;
        $this->onCreditHold = $onCreditHold;
        $this->overdueBalance = $overdueBalance;
        $this->parent = $parent;
        $this->parentItemsBought = $parentItemsBought;
        $this->partner = $partner;
        $this->pec = $pec;
        $this->phone = $phone;
        $this->phoneticName = $phoneticName;
        $this->priceLevel = $priceLevel;
        $this->prospectDate = $prospectDate;
        $this->pstExempt = $pstExempt;
        $this->reminderDate = $reminderDate;
        $this->resaleNumber = $resaleNumber;
        $this->salesRep = $salesRep;
        $this->salesTeamMember = $salesTeamMember;
        $this->salesTeamRole = $salesTeamRole;
        $this->salutation = $salutation;
        $this->shipComplete = $shipComplete;
        $this->shippingItem = $shippingItem;
        $this->stage = $stage;
        $this->startDate = $startDate;
        $this->state = $state;
        $this->taxable = $taxable;
        $this->territory = $territory;
        $this->unsubscribe = $unsubscribe;
        $this->url = $url;
        $this->webLead = $webLead;
        $this->zipCode = $zipCode;
        $this->customFieldList = $customFieldList;
    }
}?>