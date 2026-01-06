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
 * SalesTaxItem map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_SalesTaxItem {
    public $itemId;
    public $displayName;
    public $description;
    public $rate;
    public $taxType; //NetSuite_RecordRef
    public $taxAgency; //NetSuite_RecordRef
    public $purchaseAccount; //NetSuite_RecordRef
    public $saleAccount; //NetSuite_RecordRef
    public $isInactive;
    public $effectiveFrom;
    public $validUntil;
    public $eccode;
    public $parent; //NetSuite_RecordRef
    public $isDefault;
    public $excludeFromTaxReports;
    public $available;
    public $export;
    public $taxAccount; //NetSuite_RecordRef
    public $county;
    public $city;
    public $state;
    public $zip;
    public $internalId;
    public $externalId;

    public function __construct(  $itemId, $displayName, $description, $rate, NetSuite_RecordRef $taxType, NetSuite_RecordRef $taxAgency, NetSuite_RecordRef $purchaseAccount, NetSuite_RecordRef $saleAccount, $isInactive, $effectiveFrom, $validUntil, $eccode, NetSuite_RecordRef $parent, $isDefault, $excludeFromTaxReports, $available, $export, NetSuite_RecordRef $taxAccount, $county, $city, $state, $zip, $internalId, $externalId) {
        $this->itemId = $itemId;
        $this->displayName = $displayName;
        $this->description = $description;
        $this->rate = $rate;
        $this->taxType = $taxType;
        $this->taxAgency = $taxAgency;
        $this->purchaseAccount = $purchaseAccount;
        $this->saleAccount = $saleAccount;
        $this->isInactive = $isInactive;
        $this->effectiveFrom = $effectiveFrom;
        $this->validUntil = $validUntil;
        $this->eccode = $eccode;
        $this->parent = $parent;
        $this->isDefault = $isDefault;
        $this->excludeFromTaxReports = $excludeFromTaxReports;
        $this->available = $available;
        $this->export = $export;
        $this->taxAccount = $taxAccount;
        $this->county = $county;
        $this->city = $city;
        $this->state = $state;
        $this->zip = $zip;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>