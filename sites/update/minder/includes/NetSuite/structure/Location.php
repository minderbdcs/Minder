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
 * Location map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_Location {
    public $name;
    public $parent; //NetSuite_RecordRef
    public $isInactive;
    public $tranPrefix;
    public $attention;
    public $addressee;
    public $addrPhone;
    public $addr1;
    public $addr2;
    public $city;
    public $state;
    public $zip;
    public $country;
    public $addrText;
    public $override;
    public $logo; //NetSuite_RecordRef
    public $customFieldList; //NetSuite_CustomFieldList
    public $internalId;
    public $externalId;

    public function __construct(  $name, NetSuite_RecordRef $parent, $isInactive, $tranPrefix, $attention, $addressee, $addrPhone, $addr1, $addr2, $city, $state, $zip, $country, $addrText, $override, NetSuite_RecordRef $logo, NetSuite_CustomFieldList $customFieldList, $internalId, $externalId) {
        $this->name = $name;
        $this->parent = $parent;
        $this->isInactive = $isInactive;
        $this->tranPrefix = $tranPrefix;
        $this->attention = $attention;
        $this->addressee = $addressee;
        $this->addrPhone = $addrPhone;
        $this->addr1 = $addr1;
        $this->addr2 = $addr2;
        $this->city = $city;
        $this->state = $state;
        $this->zip = $zip;
        $this->country = $country;
        $this->addrText = $addrText;
        $this->override = $override;
        $this->logo = $logo;
        $this->customFieldList = $customFieldList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>