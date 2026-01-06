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
 * Employee map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_Employee {
    public $customForm; //NetSuite_RecordRef
    public $entityId;
    public $salutation;
    public $firstName;
    public $middleName;
    public $lastName;
    public $phone;
    public $fax;
    public $email;
    public $defaultAddress;
    public $isInactive;
    public $initials;
    public $officePhone;
    public $homePhone;
    public $mobilePhone;
    public $department; //NetSuite_RecordRef
    public $class; //NetSuite_RecordRef
    public $location; //NetSuite_RecordRef
    public $billingClass; //NetSuite_RecordRef
    public $accountNumber;
    public $comments;
    public $unsubscribe;
    public $image; //NetSuite_RecordRef
    public $payFrequency;
    public $lastPaidDate;
    public $useTimeData;
    public $directDeposit;
    public $socialSecurityNumber;
    public $supervisor; //NetSuite_RecordRef
    public $approver; //NetSuite_RecordRef
    public $approvalLimit;
    public $employeeType; //NetSuite_RecordRef
    public $isSalesRep;
    public $isSupportRep;
    public $birthDate;
    public $hireDate;
    public $releaseDate;
    public $lastReviewDate;
    public $nextReviewDate;
    public $title;
    public $employeeStatus; //NetSuite_RecordRef
    public $jobDescription;
    public $giveAccess;
    public $sendEmail;
    public $hasOfflineAccess;
    public $password;
    public $password2;
    public $requirePwdChange;
    public $inheritIPRules;
    public $IPAddressRule;
    public $billPay;
    public $dateCreated;
    public $lastModifiedDate;
    public $addressbookList; //NetSuite_EmployeeAddressbookList
    public $rolesList; //NetSuite_EmployeeRolesList
    public $customFieldList; //NetSuite_CustomFieldList
    public $internalId;
    public $externalId;

    public function __construct(  NetSuite_RecordRef $customForm, $entityId, $salutation, $firstName, $middleName, $lastName, $phone, $fax, $email, $defaultAddress, $isInactive, $initials, $officePhone, $homePhone, $mobilePhone, NetSuite_RecordRef $department, NetSuite_RecordRef $class, NetSuite_RecordRef $location, NetSuite_RecordRef $billingClass, $accountNumber, $comments, $unsubscribe, NetSuite_RecordRef $image, $payFrequency, $lastPaidDate, $useTimeData, $directDeposit, $socialSecurityNumber, NetSuite_RecordRef $supervisor, NetSuite_RecordRef $approver, $approvalLimit, NetSuite_RecordRef $employeeType, $isSalesRep, $isSupportRep, $birthDate, $hireDate, $releaseDate, $lastReviewDate, $nextReviewDate, $title, NetSuite_RecordRef $employeeStatus, $jobDescription, $giveAccess, $sendEmail, $hasOfflineAccess, $password, $password2, $requirePwdChange, $inheritIPRules, $IPAddressRule, $billPay, $dateCreated, $lastModifiedDate, NetSuite_EmployeeAddressbookList $addressbookList, NetSuite_EmployeeRolesList $rolesList, NetSuite_CustomFieldList $customFieldList, $internalId, $externalId) {
        $this->customForm = $customForm;
        $this->entityId = $entityId;
        $this->salutation = $salutation;
        $this->firstName = $firstName;
        $this->middleName = $middleName;
        $this->lastName = $lastName;
        $this->phone = $phone;
        $this->fax = $fax;
        $this->email = $email;
        $this->defaultAddress = $defaultAddress;
        $this->isInactive = $isInactive;
        $this->initials = $initials;
        $this->officePhone = $officePhone;
        $this->homePhone = $homePhone;
        $this->mobilePhone = $mobilePhone;
        $this->department = $department;
        $this->class = $class;
        $this->location = $location;
        $this->billingClass = $billingClass;
        $this->accountNumber = $accountNumber;
        $this->comments = $comments;
        $this->unsubscribe = $unsubscribe;
        $this->image = $image;
        $this->payFrequency = $payFrequency;
        $this->lastPaidDate = $lastPaidDate;
        $this->useTimeData = $useTimeData;
        $this->directDeposit = $directDeposit;
        $this->socialSecurityNumber = $socialSecurityNumber;
        $this->supervisor = $supervisor;
        $this->approver = $approver;
        $this->approvalLimit = $approvalLimit;
        $this->employeeType = $employeeType;
        $this->isSalesRep = $isSalesRep;
        $this->isSupportRep = $isSupportRep;
        $this->birthDate = $birthDate;
        $this->hireDate = $hireDate;
        $this->releaseDate = $releaseDate;
        $this->lastReviewDate = $lastReviewDate;
        $this->nextReviewDate = $nextReviewDate;
        $this->title = $title;
        $this->employeeStatus = $employeeStatus;
        $this->jobDescription = $jobDescription;
        $this->giveAccess = $giveAccess;
        $this->sendEmail = $sendEmail;
        $this->hasOfflineAccess = $hasOfflineAccess;
        $this->password = $password;
        $this->password2 = $password2;
        $this->requirePwdChange = $requirePwdChange;
        $this->inheritIPRules = $inheritIPRules;
        $this->IPAddressRule = $IPAddressRule;
        $this->billPay = $billPay;
        $this->dateCreated = $dateCreated;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->addressbookList = $addressbookList;
        $this->rolesList = $rolesList;
        $this->customFieldList = $customFieldList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>