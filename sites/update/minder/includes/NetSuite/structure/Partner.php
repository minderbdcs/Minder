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
 * Partner map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_Partner {
    public $customForm; //NetSuite_RecordRef
    public $entityId;
    public $partnerCode;
    public $isPerson;
    public $salutation;
    public $firstName;
    public $middleName;
    public $lastName;
    public $companyName;
    public $parent; //NetSuite_RecordRef
    public $phone;
    public $fax;
    public $email;
    public $url;
    public $defaultAddress;
    public $isInactive;
    public $lastModifiedDate;
    public $dateCreated;
    public $referringUrl;
    public $unsubscribe;
    public $categoryList; //NetSuite_CategoryList
    public $title;
    public $printOnCheckAs;
    public $taxIdNum;
    public $comments;
    public $image; //NetSuite_RecordRef
    public $emailPreference;
    public $department; //NetSuite_RecordRef
    public $location; //NetSuite_RecordRef
    public $class; //NetSuite_RecordRef
    public $homePhone;
    public $mobilePhone;
    public $altEmail;
    public $giveAccess;
    public $accessRole; //NetSuite_RecordRef
    public $sendEmail;
    public $password;
    public $password2;
    public $requirePwdChange;
    public $subPartnerLogin;
    public $promoCodeList; //NetSuite_PartnerPromoCodeList
    public $addressbookList; //NetSuite_PartnerAddressbookList
    public $customFieldList; //NetSuite_CustomFieldList
    public $internalId;
    public $externalId;

    public function __construct(  NetSuite_RecordRef $customForm, $entityId, $partnerCode, $isPerson, $salutation, $firstName, $middleName, $lastName, $companyName, NetSuite_RecordRef $parent, $phone, $fax, $email, $url, $defaultAddress, $isInactive, $lastModifiedDate, $dateCreated, $referringUrl, $unsubscribe, NetSuite_CategoryList $categoryList, $title, $printOnCheckAs, $taxIdNum, $comments, NetSuite_RecordRef $image, $emailPreference, NetSuite_RecordRef $department, NetSuite_RecordRef $location, NetSuite_RecordRef $class, $homePhone, $mobilePhone, $altEmail, $giveAccess, NetSuite_RecordRef $accessRole, $sendEmail, $password, $password2, $requirePwdChange, $subPartnerLogin, NetSuite_PartnerPromoCodeList $promoCodeList, NetSuite_PartnerAddressbookList $addressbookList, NetSuite_CustomFieldList $customFieldList, $internalId, $externalId) {
        $this->customForm = $customForm;
        $this->entityId = $entityId;
        $this->partnerCode = $partnerCode;
        $this->isPerson = $isPerson;
        $this->salutation = $salutation;
        $this->firstName = $firstName;
        $this->middleName = $middleName;
        $this->lastName = $lastName;
        $this->companyName = $companyName;
        $this->parent = $parent;
        $this->phone = $phone;
        $this->fax = $fax;
        $this->email = $email;
        $this->url = $url;
        $this->defaultAddress = $defaultAddress;
        $this->isInactive = $isInactive;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->dateCreated = $dateCreated;
        $this->referringUrl = $referringUrl;
        $this->unsubscribe = $unsubscribe;
        $this->categoryList = $categoryList;
        $this->title = $title;
        $this->printOnCheckAs = $printOnCheckAs;
        $this->taxIdNum = $taxIdNum;
        $this->comments = $comments;
        $this->image = $image;
        $this->emailPreference = $emailPreference;
        $this->department = $department;
        $this->location = $location;
        $this->class = $class;
        $this->homePhone = $homePhone;
        $this->mobilePhone = $mobilePhone;
        $this->altEmail = $altEmail;
        $this->giveAccess = $giveAccess;
        $this->accessRole = $accessRole;
        $this->sendEmail = $sendEmail;
        $this->password = $password;
        $this->password2 = $password2;
        $this->requirePwdChange = $requirePwdChange;
        $this->subPartnerLogin = $subPartnerLogin;
        $this->promoCodeList = $promoCodeList;
        $this->addressbookList = $addressbookList;
        $this->customFieldList = $customFieldList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>