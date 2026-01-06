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
 * ContactSearchBasic map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_ContactSearchBasic {
    public $address; //NetSuite_SearchStringField
    public $availableOffline; //NetSuite_SearchBooleanField
    public $category; //NetSuite_SearchMultiSelectField
    public $city; //NetSuite_SearchStringField
    public $company; //NetSuite_SearchStringField
    public $contactRole; //NetSuite_SearchMultiSelectField
    public $country; //NetSuite_SearchEnumMultiSelectField
    public $county; //NetSuite_SearchStringField
    public $dateCreated; //NetSuite_SearchDateField
    public $email; //NetSuite_SearchStringField
    public $entityId; //NetSuite_SearchStringField
    public $externalId; //NetSuite_SearchMultiSelectField
    public $fax; //NetSuite_SearchStringField
    public $firstName; //NetSuite_SearchStringField
    public $giveAccess; //NetSuite_SearchBooleanField
    public $group; //NetSuite_SearchMultiSelectField
    public $hasDuplicates; //NetSuite_SearchBooleanField
    public $image; //NetSuite_SearchStringField
    public $internalId; //NetSuite_SearchMultiSelectField
    public $isInactive; //NetSuite_SearchBooleanField
    public $isPrivate; //NetSuite_SearchBooleanField
    public $language; //NetSuite_SearchStringField
    public $lastModifiedDate; //NetSuite_SearchDateField
    public $lastName; //NetSuite_SearchStringField
    public $middleName; //NetSuite_SearchStringField
    public $owner; //NetSuite_SearchMultiSelectField
    public $phone; //NetSuite_SearchStringField
    public $phoneticName; //NetSuite_SearchStringField
    public $salutation; //NetSuite_SearchStringField
    public $state; //NetSuite_SearchStringField
    public $title; //NetSuite_SearchStringField
    public $type; //NetSuite_SearchEnumMultiSelectField
    public $unsubscribe; //NetSuite_SearchBooleanField
    public $zipCode; //NetSuite_SearchStringField
    public $customFieldList; //NetSuite_SearchCustomFieldList

    public function __construct(  NetSuite_SearchStringField $address, NetSuite_SearchBooleanField $availableOffline, NetSuite_SearchMultiSelectField $category, NetSuite_SearchStringField $city, NetSuite_SearchStringField $company, NetSuite_SearchMultiSelectField $contactRole, NetSuite_SearchEnumMultiSelectField $country, NetSuite_SearchStringField $county, NetSuite_SearchDateField $dateCreated, NetSuite_SearchStringField $email, NetSuite_SearchStringField $entityId, NetSuite_SearchMultiSelectField $externalId, NetSuite_SearchStringField $fax, NetSuite_SearchStringField $firstName, NetSuite_SearchBooleanField $giveAccess, NetSuite_SearchMultiSelectField $group, NetSuite_SearchBooleanField $hasDuplicates, NetSuite_SearchStringField $image, NetSuite_SearchMultiSelectField $internalId, NetSuite_SearchBooleanField $isInactive, NetSuite_SearchBooleanField $isPrivate, NetSuite_SearchStringField $language, NetSuite_SearchDateField $lastModifiedDate, NetSuite_SearchStringField $lastName, NetSuite_SearchStringField $middleName, NetSuite_SearchMultiSelectField $owner, NetSuite_SearchStringField $phone, NetSuite_SearchStringField $phoneticName, NetSuite_SearchStringField $salutation, NetSuite_SearchStringField $state, NetSuite_SearchStringField $title, NetSuite_SearchEnumMultiSelectField $type, NetSuite_SearchBooleanField $unsubscribe, NetSuite_SearchStringField $zipCode, NetSuite_SearchCustomFieldList $customFieldList) {
        $this->address = $address;
        $this->availableOffline = $availableOffline;
        $this->category = $category;
        $this->city = $city;
        $this->company = $company;
        $this->contactRole = $contactRole;
        $this->country = $country;
        $this->county = $county;
        $this->dateCreated = $dateCreated;
        $this->email = $email;
        $this->entityId = $entityId;
        $this->externalId = $externalId;
        $this->fax = $fax;
        $this->firstName = $firstName;
        $this->giveAccess = $giveAccess;
        $this->group = $group;
        $this->hasDuplicates = $hasDuplicates;
        $this->image = $image;
        $this->internalId = $internalId;
        $this->isInactive = $isInactive;
        $this->isPrivate = $isPrivate;
        $this->language = $language;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->lastName = $lastName;
        $this->middleName = $middleName;
        $this->owner = $owner;
        $this->phone = $phone;
        $this->phoneticName = $phoneticName;
        $this->salutation = $salutation;
        $this->state = $state;
        $this->title = $title;
        $this->type = $type;
        $this->unsubscribe = $unsubscribe;
        $this->zipCode = $zipCode;
        $this->customFieldList = $customFieldList;
    }
}?>