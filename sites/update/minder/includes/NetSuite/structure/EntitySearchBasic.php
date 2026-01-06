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
 * EntitySearchBasic map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_EntitySearchBasic {
    public $address; //NetSuite_SearchStringField
    public $city; //NetSuite_SearchStringField
    public $country; //NetSuite_SearchEnumMultiSelectField
    public $county; //NetSuite_SearchStringField
    public $dateCreated; //NetSuite_SearchDateField
    public $email; //NetSuite_SearchStringField
    public $entityId; //NetSuite_SearchStringField
    public $externalId; //NetSuite_SearchMultiSelectField
    public $fax; //NetSuite_SearchStringField
    public $giveAccess; //NetSuite_SearchBooleanField
    public $image; //NetSuite_SearchStringField
    public $internalId; //NetSuite_SearchMultiSelectField
    public $isInactive; //NetSuite_SearchBooleanField
    public $language; //NetSuite_SearchStringField
    public $lastModifiedDate; //NetSuite_SearchDateField
    public $phone; //NetSuite_SearchStringField
    public $phoneticName; //NetSuite_SearchStringField
    public $state; //NetSuite_SearchStringField
    public $unsubscribe; //NetSuite_SearchBooleanField
    public $zipCode; //NetSuite_SearchStringField

    public function __construct(  NetSuite_SearchStringField $address, NetSuite_SearchStringField $city, NetSuite_SearchEnumMultiSelectField $country, NetSuite_SearchStringField $county, NetSuite_SearchDateField $dateCreated, NetSuite_SearchStringField $email, NetSuite_SearchStringField $entityId, NetSuite_SearchMultiSelectField $externalId, NetSuite_SearchStringField $fax, NetSuite_SearchBooleanField $giveAccess, NetSuite_SearchStringField $image, NetSuite_SearchMultiSelectField $internalId, NetSuite_SearchBooleanField $isInactive, NetSuite_SearchStringField $language, NetSuite_SearchDateField $lastModifiedDate, NetSuite_SearchStringField $phone, NetSuite_SearchStringField $phoneticName, NetSuite_SearchStringField $state, NetSuite_SearchBooleanField $unsubscribe, NetSuite_SearchStringField $zipCode) {
        $this->address = $address;
        $this->city = $city;
        $this->country = $country;
        $this->county = $county;
        $this->dateCreated = $dateCreated;
        $this->email = $email;
        $this->entityId = $entityId;
        $this->externalId = $externalId;
        $this->fax = $fax;
        $this->giveAccess = $giveAccess;
        $this->image = $image;
        $this->internalId = $internalId;
        $this->isInactive = $isInactive;
        $this->language = $language;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->phone = $phone;
        $this->phoneticName = $phoneticName;
        $this->state = $state;
        $this->unsubscribe = $unsubscribe;
        $this->zipCode = $zipCode;
    }
}?>