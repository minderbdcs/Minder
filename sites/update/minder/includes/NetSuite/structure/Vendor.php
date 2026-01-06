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
 * Vendor map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_Vendor {
    public $customForm; //NetSuite_RecordRef
    public $entityId;
    public $isPerson;
    public $salutation;
    public $firstName;
    public $middleName;
    public $lastName;
    public $companyName;
    public $phone;
    public $fax;
    public $email;
    public $url;
    public $defaultAddress;
    public $isInactive;
    public $lastModifiedDate;
    public $dateCreated;
    public $category; //NetSuite_RecordRef
    public $title;
    public $printOnCheckAs;
    public $altPhone;
    public $homePhone;
    public $mobilePhone;
    public $altEmail;
    public $comments;
    public $unsubscribe;
    public $image; //NetSuite_RecordRef
    public $emailPreference;
    public $accountNumber;
    public $legalName;
    public $expenseAccount; //NetSuite_RecordRef
    public $terms; //NetSuite_RecordRef
    public $creditLimit;
    public $openingBalance;
    public $openingBalanceDate;
    public $openingBalanceAccount; //NetSuite_RecordRef
    public $balance;
    public $currency; //NetSuite_RecordRef
    public $is1099Eligible;
    public $isJobResourceVend;
    public $taxIdNum;
    public $giveAccess;
    public $sendEmail;
    public $billPay;
    public $isAccountant;
    public $password;
    public $password2;
    public $requirePwdChange;
    public $pricingScheduleList; //NetSuite_VendorPricingScheduleList
    public $addressbookList; //NetSuite_VendorAddressbookList
    public $rolesList; //NetSuite_VendorRolesList
    public $customFieldList; //NetSuite_CustomFieldList
    public $internalId;
    public $externalId;

    public function __construct(  NetSuite_RecordRef $customForm, $entityId, $isPerson, $salutation, $firstName, $middleName, $lastName, $companyName, $phone, $fax, $email, $url, $defaultAddress, $isInactive, $lastModifiedDate, $dateCreated, NetSuite_RecordRef $category, $title, $printOnCheckAs, $altPhone, $homePhone, $mobilePhone, $altEmail, $comments, $unsubscribe, NetSuite_RecordRef $image, $emailPreference, $accountNumber, $legalName, NetSuite_RecordRef $expenseAccount, NetSuite_RecordRef $terms, $creditLimit, $openingBalance, $openingBalanceDate, NetSuite_RecordRef $openingBalanceAccount, $balance, NetSuite_RecordRef $currency, $is1099Eligible, $isJobResourceVend, $taxIdNum, $giveAccess, $sendEmail, $billPay, $isAccountant, $password, $password2, $requirePwdChange, NetSuite_VendorPricingScheduleList $pricingScheduleList, NetSuite_VendorAddressbookList $addressbookList, NetSuite_VendorRolesList $rolesList, NetSuite_CustomFieldList $customFieldList, $internalId, $externalId) {
        $this->customForm = $customForm;
        $this->entityId = $entityId;
        $this->isPerson = $isPerson;
        $this->salutation = $salutation;
        $this->firstName = $firstName;
        $this->middleName = $middleName;
        $this->lastName = $lastName;
        $this->companyName = $companyName;
        $this->phone = $phone;
        $this->fax = $fax;
        $this->email = $email;
        $this->url = $url;
        $this->defaultAddress = $defaultAddress;
        $this->isInactive = $isInactive;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->dateCreated = $dateCreated;
        $this->category = $category;
        $this->title = $title;
        $this->printOnCheckAs = $printOnCheckAs;
        $this->altPhone = $altPhone;
        $this->homePhone = $homePhone;
        $this->mobilePhone = $mobilePhone;
        $this->altEmail = $altEmail;
        $this->comments = $comments;
        $this->unsubscribe = $unsubscribe;
        $this->image = $image;
        $this->emailPreference = $emailPreference;
        $this->accountNumber = $accountNumber;
        $this->legalName = $legalName;
        $this->expenseAccount = $expenseAccount;
        $this->terms = $terms;
        $this->creditLimit = $creditLimit;
        $this->openingBalance = $openingBalance;
        $this->openingBalanceDate = $openingBalanceDate;
        $this->openingBalanceAccount = $openingBalanceAccount;
        $this->balance = $balance;
        $this->currency = $currency;
        $this->is1099Eligible = $is1099Eligible;
        $this->isJobResourceVend = $isJobResourceVend;
        $this->taxIdNum = $taxIdNum;
        $this->giveAccess = $giveAccess;
        $this->sendEmail = $sendEmail;
        $this->billPay = $billPay;
        $this->isAccountant = $isAccountant;
        $this->password = $password;
        $this->password2 = $password2;
        $this->requirePwdChange = $requirePwdChange;
        $this->pricingScheduleList = $pricingScheduleList;
        $this->addressbookList = $addressbookList;
        $this->rolesList = $rolesList;
        $this->customFieldList = $customFieldList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>