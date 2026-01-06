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
 * LocationSearchBasic map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_LocationSearchBasic {
    public $address; //NetSuite_SearchStringField
    public $city; //NetSuite_SearchStringField
    public $country; //NetSuite_SearchEnumMultiSelectField
    public $county; //NetSuite_SearchStringField
    public $externalId; //NetSuite_SearchMultiSelectField
    public $internalId; //NetSuite_SearchMultiSelectField
    public $isInactive; //NetSuite_SearchBooleanField
    public $isOffice; //NetSuite_SearchBooleanField
    public $name; //NetSuite_SearchStringField
    public $phone; //NetSuite_SearchStringField
    public $state; //NetSuite_SearchStringField
    public $zip; //NetSuite_SearchStringField
    public $customFieldList; //NetSuite_SearchCustomFieldList

    public function __construct(  NetSuite_SearchStringField $address, NetSuite_SearchStringField $city, NetSuite_SearchEnumMultiSelectField $country, NetSuite_SearchStringField $county, NetSuite_SearchMultiSelectField $externalId, NetSuite_SearchMultiSelectField $internalId, NetSuite_SearchBooleanField $isInactive, NetSuite_SearchBooleanField $isOffice, NetSuite_SearchStringField $name, NetSuite_SearchStringField $phone, NetSuite_SearchStringField $state, NetSuite_SearchStringField $zip, NetSuite_SearchCustomFieldList $customFieldList) {
        $this->address = $address;
        $this->city = $city;
        $this->country = $country;
        $this->county = $county;
        $this->externalId = $externalId;
        $this->internalId = $internalId;
        $this->isInactive = $isInactive;
        $this->isOffice = $isOffice;
        $this->name = $name;
        $this->phone = $phone;
        $this->state = $state;
        $this->zip = $zip;
        $this->customFieldList = $customFieldList;
    }
}?>