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
 * Customer map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_Customer {
    public $customForm; //NetSuite_RecordRef
    public $entityId;
    public $isPerson;
    public $salutation;
    public $firstName;
    public $middleName;
    public $lastName;
    public $companyName;
    public $entityStatus; //NetSuite_RecordRef
    public $parent; //NetSuite_RecordRef
    public $phone;
    public $fax;
    public $email;
    public $url;
    public $defaultAddress;
    public $isInactive;
    public $category; //NetSuite_RecordRef
    public $title;
    public $printOnCheckAs;
    public $altPhone;
    public $homePhone;
    public $mobilePhone;
    public $altEmail;
    public $language;
    public $comments;
    public $dateCreated;
    public $image; //NetSuite_RecordRef
    public $emailPreference;
    public $salesRep; //NetSuite_RecordRef
    public $territory; //NetSuite_RecordRef
    public $partner; //NetSuite_RecordRef
    public $salesGroup; //NetSuite_RecordRef
    public $vatRegNumber;
    public $accountNumber;
    public $terms; //NetSuite_RecordRef
    public $creditLimit;
    public $creditHoldOverride;
    public $balance;
    public $overdueBalance;
    public $daysOverdue;
    public $priceLevel; //NetSuite_RecordRef
    public $currency; //NetSuite_RecordRef
    public $prefCCProcessor; //NetSuite_RecordRef
    public $shipComplete;
    public $taxable;
    public $taxItem; //NetSuite_RecordRef
    public $resaleNumber;
    public $startDate;
    public $endDate;
    public $reminderDays;
    public $shippingItem; //NetSuite_RecordRef
    public $thirdPartyAcct;
    public $thirdPartyZipcode;
    public $thirdPartyCountry;
    public $giveAccess;
    public $accessRole; //NetSuite_RecordRef
    public $sendEmail;
    public $password;
    public $password2;
    public $requirePwdChange;
    public $accessHelp;
    public $campaignCategory; //NetSuite_RecordRef
    public $leadSource; //NetSuite_RecordRef
    public $webLead;
    public $unsubscribe;
    public $referrer;
    public $keywords;
    public $clickStream;
    public $lastPageVisited;
    public $visits;
    public $firstVisit;
    public $lastVisit;
    public $billPay;
    public $openingBalance;
    public $lastModifiedDate;
    public $openingBalanceDate;
    public $openingBalanceAccount; //NetSuite_RecordRef
    public $stage;
    public $salesTeamList; //NetSuite_CustomerSalesTeamList
    public $downloadList; //NetSuite_CustomerDownloadList
    public $addressbookList; //NetSuite_CustomerAddressbookList
    public $contactList; //NetSuite_CustomerContactList
    public $creditCardsList; //NetSuite_CustomerCreditCardsList
    public $customFieldList; //NetSuite_CustomFieldList
    public $internalId;
    public $externalId;

    public function __construct(  NetSuite_RecordRef $customForm, $entityId, $isPerson, $salutation, $firstName, $middleName, $lastName, $companyName, NetSuite_RecordRef $entityStatus, NetSuite_RecordRef $parent, $phone, $fax, $email, $url, $defaultAddress, $isInactive, NetSuite_RecordRef $category, $title, $printOnCheckAs, $altPhone, $homePhone, $mobilePhone, $altEmail, $language, $comments, $dateCreated, NetSuite_RecordRef $image, $emailPreference, NetSuite_RecordRef $salesRep, NetSuite_RecordRef $territory, NetSuite_RecordRef $partner, NetSuite_RecordRef $salesGroup, $vatRegNumber, $accountNumber, NetSuite_RecordRef $terms, $creditLimit, $creditHoldOverride, $balance, $overdueBalance, $daysOverdue, NetSuite_RecordRef $priceLevel, NetSuite_RecordRef $currency, NetSuite_RecordRef $prefCCProcessor, $shipComplete, $taxable, NetSuite_RecordRef $taxItem, $resaleNumber, $startDate, $endDate, $reminderDays, NetSuite_RecordRef $shippingItem, $thirdPartyAcct, $thirdPartyZipcode, $thirdPartyCountry, $giveAccess, NetSuite_RecordRef $accessRole, $sendEmail, $password, $password2, $requirePwdChange, $accessHelp, NetSuite_RecordRef $campaignCategory, NetSuite_RecordRef $leadSource, $webLead, $unsubscribe, $referrer, $keywords, $clickStream, $lastPageVisited, $visits, $firstVisit, $lastVisit, $billPay, $openingBalance, $lastModifiedDate, $openingBalanceDate, NetSuite_RecordRef $openingBalanceAccount, $stage, NetSuite_CustomerSalesTeamList $salesTeamList, NetSuite_CustomerDownloadList $downloadList, NetSuite_CustomerAddressbookList $addressbookList, NetSuite_CustomerContactList $contactList, NetSuite_CustomerCreditCardsList $creditCardsList, NetSuite_CustomFieldList $customFieldList, $internalId, $externalId) {
        $this->customForm = $customForm;
        $this->entityId = $entityId;
        $this->isPerson = $isPerson;
        $this->salutation = $salutation;
        $this->firstName = $firstName;
        $this->middleName = $middleName;
        $this->lastName = $lastName;
        $this->companyName = $companyName;
        $this->entityStatus = $entityStatus;
        $this->parent = $parent;
        $this->phone = $phone;
        $this->fax = $fax;
        $this->email = $email;
        $this->url = $url;
        $this->defaultAddress = $defaultAddress;
        $this->isInactive = $isInactive;
        $this->category = $category;
        $this->title = $title;
        $this->printOnCheckAs = $printOnCheckAs;
        $this->altPhone = $altPhone;
        $this->homePhone = $homePhone;
        $this->mobilePhone = $mobilePhone;
        $this->altEmail = $altEmail;
        $this->language = $language;
        $this->comments = $comments;
        $this->dateCreated = $dateCreated;
        $this->image = $image;
        $this->emailPreference = $emailPreference;
        $this->salesRep = $salesRep;
        $this->territory = $territory;
        $this->partner = $partner;
        $this->salesGroup = $salesGroup;
        $this->vatRegNumber = $vatRegNumber;
        $this->accountNumber = $accountNumber;
        $this->terms = $terms;
        $this->creditLimit = $creditLimit;
        $this->creditHoldOverride = $creditHoldOverride;
        $this->balance = $balance;
        $this->overdueBalance = $overdueBalance;
        $this->daysOverdue = $daysOverdue;
        $this->priceLevel = $priceLevel;
        $this->currency = $currency;
        $this->prefCCProcessor = $prefCCProcessor;
        $this->shipComplete = $shipComplete;
        $this->taxable = $taxable;
        $this->taxItem = $taxItem;
        $this->resaleNumber = $resaleNumber;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->reminderDays = $reminderDays;
        $this->shippingItem = $shippingItem;
        $this->thirdPartyAcct = $thirdPartyAcct;
        $this->thirdPartyZipcode = $thirdPartyZipcode;
        $this->thirdPartyCountry = $thirdPartyCountry;
        $this->giveAccess = $giveAccess;
        $this->accessRole = $accessRole;
        $this->sendEmail = $sendEmail;
        $this->password = $password;
        $this->password2 = $password2;
        $this->requirePwdChange = $requirePwdChange;
        $this->accessHelp = $accessHelp;
        $this->campaignCategory = $campaignCategory;
        $this->leadSource = $leadSource;
        $this->webLead = $webLead;
        $this->unsubscribe = $unsubscribe;
        $this->referrer = $referrer;
        $this->keywords = $keywords;
        $this->clickStream = $clickStream;
        $this->lastPageVisited = $lastPageVisited;
        $this->visits = $visits;
        $this->firstVisit = $firstVisit;
        $this->lastVisit = $lastVisit;
        $this->billPay = $billPay;
        $this->openingBalance = $openingBalance;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->openingBalanceDate = $openingBalanceDate;
        $this->openingBalanceAccount = $openingBalanceAccount;
        $this->stage = $stage;
        $this->salesTeamList = $salesTeamList;
        $this->downloadList = $downloadList;
        $this->addressbookList = $addressbookList;
        $this->contactList = $contactList;
        $this->creditCardsList = $creditCardsList;
        $this->customFieldList = $customFieldList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>