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
 * EmployeeSearchBasic map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_EmployeeSearchBasic {
    public $address; //NetSuite_SearchStringField
    public $alienNumber; //NetSuite_SearchStringField
    public $anniversary; //NetSuite_SearchDateField
    public $approvalLimit; //NetSuite_SearchDoubleField
    public $authworkDate; //NetSuite_SearchDateField
    public $billingClass; //NetSuite_SearchMultiSelectField
    public $birthDate; //NetSuite_SearchDateField
    public $birthDay; //NetSuite_SearchDateField
    public $cContribution; //NetSuite_SearchMultiSelectField
    public $city; //NetSuite_SearchStringField
    public $class; //NetSuite_SearchMultiSelectField
    public $country; //NetSuite_SearchEnumMultiSelectField
    public $county; //NetSuite_SearchStringField
    public $dateCreated; //NetSuite_SearchDateField
    public $deduction; //NetSuite_SearchMultiSelectField
    public $department; //NetSuite_SearchMultiSelectField
    public $earning; //NetSuite_SearchMultiSelectField
    public $education; //NetSuite_SearchMultiSelectField
    public $eligibleForCommission; //NetSuite_SearchBooleanField
    public $email; //NetSuite_SearchStringField
    public $employeeStatus; //NetSuite_SearchMultiSelectField
    public $employeeType; //NetSuite_SearchMultiSelectField
    public $employeeTypeKpi; //NetSuite_SearchBooleanField
    public $entityId; //NetSuite_SearchStringField
    public $ethnicity; //NetSuite_SearchMultiSelectField
    public $externalId; //NetSuite_SearchMultiSelectField
    public $fax; //NetSuite_SearchStringField
    public $firstName; //NetSuite_SearchStringField
    public $gender; //NetSuite_SearchBooleanField
    public $giveAccess; //NetSuite_SearchBooleanField
    public $group; //NetSuite_SearchMultiSelectField
    public $hireDate; //NetSuite_SearchDateField
    public $I9Verified; //NetSuite_SearchBooleanField
    public $image; //NetSuite_SearchStringField
    public $internalId; //NetSuite_SearchMultiSelectField
    public $isInactive; //NetSuite_SearchBooleanField
    public $isJobResource; //NetSuite_SearchBooleanField
    public $isTemplate; //NetSuite_SearchBooleanField
    public $jobDescription; //NetSuite_SearchStringField
    public $laborCost; //NetSuite_SearchDoubleField
    public $language; //NetSuite_SearchStringField
    public $lastModifiedDate; //NetSuite_SearchDateField
    public $lastName; //NetSuite_SearchStringField
    public $lastReviewDate; //NetSuite_SearchDateField
    public $location; //NetSuite_SearchMultiSelectField
    public $maritalStatus; //NetSuite_SearchMultiSelectField
    public $middleName; //NetSuite_SearchStringField
    public $nextReviewDate; //NetSuite_SearchDateField
    public $offlineAccess; //NetSuite_SearchBooleanField
    public $phone; //NetSuite_SearchStringField
    public $phoneticName; //NetSuite_SearchStringField
    public $releaseDate; //NetSuite_SearchDateField
    public $residentStatus; //NetSuite_SearchMultiSelectField
    public $salesRep; //NetSuite_SearchBooleanField
    public $salesRole; //NetSuite_SearchMultiSelectField
    public $salutation; //NetSuite_SearchStringField
    public $socialSecurityNumber; //NetSuite_SearchStringField
    public $state; //NetSuite_SearchStringField
    public $supervisor; //NetSuite_SearchMultiSelectField
    public $supportRep; //NetSuite_SearchBooleanField
    public $title; //NetSuite_SearchStringField
    public $type; //NetSuite_SearchEnumMultiSelectField
    public $unsubscribe; //NetSuite_SearchBooleanField
    public $visaExpDate; //NetSuite_SearchDateField
    public $visaType; //NetSuite_SearchMultiSelectField
    public $withholding; //NetSuite_SearchMultiSelectField
    public $zipCode; //NetSuite_SearchStringField
    public $customFieldList; //NetSuite_SearchCustomFieldList

    public function __construct(  NetSuite_SearchStringField $address, NetSuite_SearchStringField $alienNumber, NetSuite_SearchDateField $anniversary, NetSuite_SearchDoubleField $approvalLimit, NetSuite_SearchDateField $authworkDate, NetSuite_SearchMultiSelectField $billingClass, NetSuite_SearchDateField $birthDate, NetSuite_SearchDateField $birthDay, NetSuite_SearchMultiSelectField $cContribution, NetSuite_SearchStringField $city, NetSuite_SearchMultiSelectField $class, NetSuite_SearchEnumMultiSelectField $country, NetSuite_SearchStringField $county, NetSuite_SearchDateField $dateCreated, NetSuite_SearchMultiSelectField $deduction, NetSuite_SearchMultiSelectField $department, NetSuite_SearchMultiSelectField $earning, NetSuite_SearchMultiSelectField $education, NetSuite_SearchBooleanField $eligibleForCommission, NetSuite_SearchStringField $email, NetSuite_SearchMultiSelectField $employeeStatus, NetSuite_SearchMultiSelectField $employeeType, NetSuite_SearchBooleanField $employeeTypeKpi, NetSuite_SearchStringField $entityId, NetSuite_SearchMultiSelectField $ethnicity, NetSuite_SearchMultiSelectField $externalId, NetSuite_SearchStringField $fax, NetSuite_SearchStringField $firstName, NetSuite_SearchBooleanField $gender, NetSuite_SearchBooleanField $giveAccess, NetSuite_SearchMultiSelectField $group, NetSuite_SearchDateField $hireDate, NetSuite_SearchBooleanField $I9Verified, NetSuite_SearchStringField $image, NetSuite_SearchMultiSelectField $internalId, NetSuite_SearchBooleanField $isInactive, NetSuite_SearchBooleanField $isJobResource, NetSuite_SearchBooleanField $isTemplate, NetSuite_SearchStringField $jobDescription, NetSuite_SearchDoubleField $laborCost, NetSuite_SearchStringField $language, NetSuite_SearchDateField $lastModifiedDate, NetSuite_SearchStringField $lastName, NetSuite_SearchDateField $lastReviewDate, NetSuite_SearchMultiSelectField $location, NetSuite_SearchMultiSelectField $maritalStatus, NetSuite_SearchStringField $middleName, NetSuite_SearchDateField $nextReviewDate, NetSuite_SearchBooleanField $offlineAccess, NetSuite_SearchStringField $phone, NetSuite_SearchStringField $phoneticName, NetSuite_SearchDateField $releaseDate, NetSuite_SearchMultiSelectField $residentStatus, NetSuite_SearchBooleanField $salesRep, NetSuite_SearchMultiSelectField $salesRole, NetSuite_SearchStringField $salutation, NetSuite_SearchStringField $socialSecurityNumber, NetSuite_SearchStringField $state, NetSuite_SearchMultiSelectField $supervisor, NetSuite_SearchBooleanField $supportRep, NetSuite_SearchStringField $title, NetSuite_SearchEnumMultiSelectField $type, NetSuite_SearchBooleanField $unsubscribe, NetSuite_SearchDateField $visaExpDate, NetSuite_SearchMultiSelectField $visaType, NetSuite_SearchMultiSelectField $withholding, NetSuite_SearchStringField $zipCode, NetSuite_SearchCustomFieldList $customFieldList) {
        $this->address = $address;
        $this->alienNumber = $alienNumber;
        $this->anniversary = $anniversary;
        $this->approvalLimit = $approvalLimit;
        $this->authworkDate = $authworkDate;
        $this->billingClass = $billingClass;
        $this->birthDate = $birthDate;
        $this->birthDay = $birthDay;
        $this->cContribution = $cContribution;
        $this->city = $city;
        $this->class = $class;
        $this->country = $country;
        $this->county = $county;
        $this->dateCreated = $dateCreated;
        $this->deduction = $deduction;
        $this->department = $department;
        $this->earning = $earning;
        $this->education = $education;
        $this->eligibleForCommission = $eligibleForCommission;
        $this->email = $email;
        $this->employeeStatus = $employeeStatus;
        $this->employeeType = $employeeType;
        $this->employeeTypeKpi = $employeeTypeKpi;
        $this->entityId = $entityId;
        $this->ethnicity = $ethnicity;
        $this->externalId = $externalId;
        $this->fax = $fax;
        $this->firstName = $firstName;
        $this->gender = $gender;
        $this->giveAccess = $giveAccess;
        $this->group = $group;
        $this->hireDate = $hireDate;
        $this->I9Verified = $I9Verified;
        $this->image = $image;
        $this->internalId = $internalId;
        $this->isInactive = $isInactive;
        $this->isJobResource = $isJobResource;
        $this->isTemplate = $isTemplate;
        $this->jobDescription = $jobDescription;
        $this->laborCost = $laborCost;
        $this->language = $language;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->lastName = $lastName;
        $this->lastReviewDate = $lastReviewDate;
        $this->location = $location;
        $this->maritalStatus = $maritalStatus;
        $this->middleName = $middleName;
        $this->nextReviewDate = $nextReviewDate;
        $this->offlineAccess = $offlineAccess;
        $this->phone = $phone;
        $this->phoneticName = $phoneticName;
        $this->releaseDate = $releaseDate;
        $this->residentStatus = $residentStatus;
        $this->salesRep = $salesRep;
        $this->salesRole = $salesRole;
        $this->salutation = $salutation;
        $this->socialSecurityNumber = $socialSecurityNumber;
        $this->state = $state;
        $this->supervisor = $supervisor;
        $this->supportRep = $supportRep;
        $this->title = $title;
        $this->type = $type;
        $this->unsubscribe = $unsubscribe;
        $this->visaExpDate = $visaExpDate;
        $this->visaType = $visaType;
        $this->withholding = $withholding;
        $this->zipCode = $zipCode;
        $this->customFieldList = $customFieldList;
    }
}?>