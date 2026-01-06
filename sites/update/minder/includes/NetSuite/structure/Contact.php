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
 * Contact map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_Contact {
    public $customForm; //NetSuite_RecordRef
    public $entityId;
    public $company; //NetSuite_RecordRef
    public $salutation;
    public $firstName;
    public $middleName;
    public $lastName;
    public $title;
    public $phone;
    public $fax;
    public $email;
    public $defaultAddress;
    public $isPrivate;
    public $isInactive;
    public $categoryList; //NetSuite_CategoryList
    public $altEmail;
    public $officePhone;
    public $homePhone;
    public $mobilePhone;
    public $supervisor; //NetSuite_RecordRef
    public $supervisorPhone;
    public $assistant; //NetSuite_RecordRef
    public $assistantPhone;
    public $comments;
    public $unsubscribe;
    public $image; //NetSuite_RecordRef
    public $billPay;
    public $dateCreated;
    public $lastModifiedDate;
    public $addressbookList; //NetSuite_ContactAddressbookList
    public $customFieldList; //NetSuite_CustomFieldList
    public $internalId;
    public $externalId;

    public function __construct(  NetSuite_RecordRef $customForm, $entityId, NetSuite_RecordRef $company, $salutation, $firstName, $middleName, $lastName, $title, $phone, $fax, $email, $defaultAddress, $isPrivate, $isInactive, NetSuite_CategoryList $categoryList, $altEmail, $officePhone, $homePhone, $mobilePhone, NetSuite_RecordRef $supervisor, $supervisorPhone, NetSuite_RecordRef $assistant, $assistantPhone, $comments, $unsubscribe, NetSuite_RecordRef $image, $billPay, $dateCreated, $lastModifiedDate, NetSuite_ContactAddressbookList $addressbookList, NetSuite_CustomFieldList $customFieldList, $internalId, $externalId) {
        $this->customForm = $customForm;
        $this->entityId = $entityId;
        $this->company = $company;
        $this->salutation = $salutation;
        $this->firstName = $firstName;
        $this->middleName = $middleName;
        $this->lastName = $lastName;
        $this->title = $title;
        $this->phone = $phone;
        $this->fax = $fax;
        $this->email = $email;
        $this->defaultAddress = $defaultAddress;
        $this->isPrivate = $isPrivate;
        $this->isInactive = $isInactive;
        $this->categoryList = $categoryList;
        $this->altEmail = $altEmail;
        $this->officePhone = $officePhone;
        $this->homePhone = $homePhone;
        $this->mobilePhone = $mobilePhone;
        $this->supervisor = $supervisor;
        $this->supervisorPhone = $supervisorPhone;
        $this->assistant = $assistant;
        $this->assistantPhone = $assistantPhone;
        $this->comments = $comments;
        $this->unsubscribe = $unsubscribe;
        $this->image = $image;
        $this->billPay = $billPay;
        $this->dateCreated = $dateCreated;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->addressbookList = $addressbookList;
        $this->customFieldList = $customFieldList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>