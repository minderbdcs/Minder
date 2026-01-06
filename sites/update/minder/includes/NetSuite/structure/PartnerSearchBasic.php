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
 * PartnerSearchBasic map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_PartnerSearchBasic {
    public $address; //NetSuite_SearchStringField
    public $assignTasks; //NetSuite_SearchBooleanField
    public $category; //NetSuite_SearchMultiSelectField
    public $city; //NetSuite_SearchStringField
    public $class; //NetSuite_SearchMultiSelectField
    public $country; //NetSuite_SearchMultiSelectField
    public $county; //NetSuite_SearchStringField
    public $dateCreated; //NetSuite_SearchDateField
    public $department; //NetSuite_SearchMultiSelectField
    public $eligibleForCommission; //NetSuite_SearchBooleanField
    public $email; //NetSuite_SearchStringField
    public $entityId; //NetSuite_SearchStringField
    public $externalId; //NetSuite_SearchMultiSelectField
    public $fax; //NetSuite_SearchStringField
    public $firstName; //NetSuite_SearchStringField
    public $giveAccess; //NetSuite_SearchBooleanField
    public $group; //NetSuite_SearchMultiSelectField
    public $image; //NetSuite_SearchMultiSelectField
    public $internalId; //NetSuite_SearchMultiSelectField
    public $isInactive; //NetSuite_SearchBooleanField
    public $isPerson; //NetSuite_SearchBooleanField
    public $language; //NetSuite_SearchStringField
    public $lastModifiedDate; //NetSuite_SearchDateField
    public $lastName; //NetSuite_SearchStringField
    public $location; //NetSuite_SearchMultiSelectField
    public $middleName; //NetSuite_SearchStringField
    public $parent; //NetSuite_SearchMultiSelectField
    public $partnerCode; //NetSuite_SearchStringField
    public $phone; //NetSuite_SearchStringField
    public $phoneticName; //NetSuite_SearchStringField
    public $promoCode; //NetSuite_SearchMultiSelectField
    public $salutation; //NetSuite_SearchStringField
    public $state; //NetSuite_SearchMultiSelectField
    public $unsubscribe; //NetSuite_SearchBooleanField
    public $URL; //NetSuite_SearchStringField
    public $zipCode; //NetSuite_SearchStringField
    public $customFieldList; //NetSuite_SearchCustomFieldList

    public function __construct(  NetSuite_SearchStringField $address, NetSuite_SearchBooleanField $assignTasks, NetSuite_SearchMultiSelectField $category, NetSuite_SearchStringField $city, NetSuite_SearchMultiSelectField $class, NetSuite_SearchMultiSelectField $country, NetSuite_SearchStringField $county, NetSuite_SearchDateField $dateCreated, NetSuite_SearchMultiSelectField $department, NetSuite_SearchBooleanField $eligibleForCommission, NetSuite_SearchStringField $email, NetSuite_SearchStringField $entityId, NetSuite_SearchMultiSelectField $externalId, NetSuite_SearchStringField $fax, NetSuite_SearchStringField $firstName, NetSuite_SearchBooleanField $giveAccess, NetSuite_SearchMultiSelectField $group, NetSuite_SearchMultiSelectField $image, NetSuite_SearchMultiSelectField $internalId, NetSuite_SearchBooleanField $isInactive, NetSuite_SearchBooleanField $isPerson, NetSuite_SearchStringField $language, NetSuite_SearchDateField $lastModifiedDate, NetSuite_SearchStringField $lastName, NetSuite_SearchMultiSelectField $location, NetSuite_SearchStringField $middleName, NetSuite_SearchMultiSelectField $parent, NetSuite_SearchStringField $partnerCode, NetSuite_SearchStringField $phone, NetSuite_SearchStringField $phoneticName, NetSuite_SearchMultiSelectField $promoCode, NetSuite_SearchStringField $salutation, NetSuite_SearchMultiSelectField $state, NetSuite_SearchBooleanField $unsubscribe, NetSuite_SearchStringField $URL, NetSuite_SearchStringField $zipCode, NetSuite_SearchCustomFieldList $customFieldList) {
        $this->address = $address;
        $this->assignTasks = $assignTasks;
        $this->category = $category;
        $this->city = $city;
        $this->class = $class;
        $this->country = $country;
        $this->county = $county;
        $this->dateCreated = $dateCreated;
        $this->department = $department;
        $this->eligibleForCommission = $eligibleForCommission;
        $this->email = $email;
        $this->entityId = $entityId;
        $this->externalId = $externalId;
        $this->fax = $fax;
        $this->firstName = $firstName;
        $this->giveAccess = $giveAccess;
        $this->group = $group;
        $this->image = $image;
        $this->internalId = $internalId;
        $this->isInactive = $isInactive;
        $this->isPerson = $isPerson;
        $this->language = $language;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->lastName = $lastName;
        $this->location = $location;
        $this->middleName = $middleName;
        $this->parent = $parent;
        $this->partnerCode = $partnerCode;
        $this->phone = $phone;
        $this->phoneticName = $phoneticName;
        $this->promoCode = $promoCode;
        $this->salutation = $salutation;
        $this->state = $state;
        $this->unsubscribe = $unsubscribe;
        $this->URL = $URL;
        $this->zipCode = $zipCode;
        $this->customFieldList = $customFieldList;
    }
}?>