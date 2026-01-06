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
 * VendorSearchBasic map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_VendorSearchBasic {
    public $accountNumber; //NetSuite_SearchStringField
    public $address; //NetSuite_SearchStringField
    public $balance; //NetSuite_SearchDoubleField
    public $category; //NetSuite_SearchMultiSelectField
    public $city; //NetSuite_SearchStringField
    public $contact; //NetSuite_SearchStringField
    public $country; //NetSuite_SearchMultiSelectField
    public $county; //NetSuite_SearchStringField
    public $creditLimit; //NetSuite_SearchDoubleField
    public $currency; //NetSuite_SearchMultiSelectField
    public $dateCreated; //NetSuite_SearchDateField
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
    public $isJobResourceVend; //NetSuite_SearchBooleanField
    public $isPerson; //NetSuite_SearchBooleanField
    public $laborCost; //NetSuite_SearchDoubleField
    public $language; //NetSuite_SearchStringField
    public $lastModifiedDate; //NetSuite_SearchDateField
    public $lastName; //NetSuite_SearchStringField
    public $middleName; //NetSuite_SearchStringField
    public $phone; //NetSuite_SearchStringField
    public $phoneticName; //NetSuite_SearchStringField
    public $salutation; //NetSuite_SearchStringField
    public $state; //NetSuite_SearchMultiSelectField
    public $taxIdNum; //NetSuite_SearchStringField
    public $unsubscribe; //NetSuite_SearchBooleanField
    public $url; //NetSuite_SearchStringField
    public $zipCode; //NetSuite_SearchStringField
    public $customFieldList; //NetSuite_SearchCustomFieldList

    public function __construct(  NetSuite_SearchStringField $accountNumber, NetSuite_SearchStringField $address, NetSuite_SearchDoubleField $balance, NetSuite_SearchMultiSelectField $category, NetSuite_SearchStringField $city, NetSuite_SearchStringField $contact, NetSuite_SearchMultiSelectField $country, NetSuite_SearchStringField $county, NetSuite_SearchDoubleField $creditLimit, NetSuite_SearchMultiSelectField $currency, NetSuite_SearchDateField $dateCreated, NetSuite_SearchBooleanField $eligibleForCommission, NetSuite_SearchStringField $email, NetSuite_SearchStringField $entityId, NetSuite_SearchMultiSelectField $externalId, NetSuite_SearchStringField $fax, NetSuite_SearchStringField $firstName, NetSuite_SearchBooleanField $giveAccess, NetSuite_SearchMultiSelectField $group, NetSuite_SearchMultiSelectField $image, NetSuite_SearchMultiSelectField $internalId, NetSuite_SearchBooleanField $isInactive, NetSuite_SearchBooleanField $isJobResourceVend, NetSuite_SearchBooleanField $isPerson, NetSuite_SearchDoubleField $laborCost, NetSuite_SearchStringField $language, NetSuite_SearchDateField $lastModifiedDate, NetSuite_SearchStringField $lastName, NetSuite_SearchStringField $middleName, NetSuite_SearchStringField $phone, NetSuite_SearchStringField $phoneticName, NetSuite_SearchStringField $salutation, NetSuite_SearchMultiSelectField $state, NetSuite_SearchStringField $taxIdNum, NetSuite_SearchBooleanField $unsubscribe, NetSuite_SearchStringField $url, NetSuite_SearchStringField $zipCode, NetSuite_SearchCustomFieldList $customFieldList) {
        $this->accountNumber = $accountNumber;
        $this->address = $address;
        $this->balance = $balance;
        $this->category = $category;
        $this->city = $city;
        $this->contact = $contact;
        $this->country = $country;
        $this->county = $county;
        $this->creditLimit = $creditLimit;
        $this->currency = $currency;
        $this->dateCreated = $dateCreated;
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
        $this->isJobResourceVend = $isJobResourceVend;
        $this->isPerson = $isPerson;
        $this->laborCost = $laborCost;
        $this->language = $language;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->lastName = $lastName;
        $this->middleName = $middleName;
        $this->phone = $phone;
        $this->phoneticName = $phoneticName;
        $this->salutation = $salutation;
        $this->state = $state;
        $this->taxIdNum = $taxIdNum;
        $this->unsubscribe = $unsubscribe;
        $this->url = $url;
        $this->zipCode = $zipCode;
        $this->customFieldList = $customFieldList;
    }
}?>