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
 * NonInventoryPurchaseItem map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_NonInventoryPurchaseItem {
    public $createdDate;
    public $lastModifiedDate;
    public $purchaseDescription;
    public $cost;
    public $costUnits;
    public $expenseAccount; //NetSuite_RecordRef
    public $isTaxable;
    public $unitsType; //NetSuite_RecordRef
    public $purchaseUnit; //NetSuite_RecordRef
    public $customForm; //NetSuite_RecordRef
    public $itemId;
    public $nameIsUPC;
    public $displayName;
    public $vendorName;
    public $parent; //NetSuite_RecordRef
    public $isInactive;
    public $availableToPartners;
    public $department; //NetSuite_RecordRef
    public $class; //NetSuite_RecordRef
    public $location; //NetSuite_RecordRef
    public $currency;
    public $itemOptionsList; //NetSuite_ItemOptionsList
    public $itemVendorList; //NetSuite_ItemVendorList
    public $purchaseTaxCode; //NetSuite_RecordRef
    public $salesTaxCode; //NetSuite_RecordRef
    public $translation; //NetSuite_TranslationList
    public $vendor; //NetSuite_RecordRef
    public $customFieldList; //NetSuite_CustomFieldList
    public $internalId;
    public $externalId;

    public function __construct(  $createdDate, $lastModifiedDate, $purchaseDescription, $cost, $costUnits, NetSuite_RecordRef $expenseAccount, $isTaxable, NetSuite_RecordRef $unitsType, NetSuite_RecordRef $purchaseUnit, NetSuite_RecordRef $customForm, $itemId, $nameIsUPC, $displayName, $vendorName, NetSuite_RecordRef $parent, $isInactive, $availableToPartners, NetSuite_RecordRef $department, NetSuite_RecordRef $class, NetSuite_RecordRef $location, $currency, NetSuite_ItemOptionsList $itemOptionsList, NetSuite_ItemVendorList $itemVendorList, NetSuite_RecordRef $purchaseTaxCode, NetSuite_RecordRef $salesTaxCode, NetSuite_TranslationList $translation, NetSuite_RecordRef $vendor, NetSuite_CustomFieldList $customFieldList, $internalId, $externalId) {
        $this->createdDate = $createdDate;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->purchaseDescription = $purchaseDescription;
        $this->cost = $cost;
        $this->costUnits = $costUnits;
        $this->expenseAccount = $expenseAccount;
        $this->isTaxable = $isTaxable;
        $this->unitsType = $unitsType;
        $this->purchaseUnit = $purchaseUnit;
        $this->customForm = $customForm;
        $this->itemId = $itemId;
        $this->nameIsUPC = $nameIsUPC;
        $this->displayName = $displayName;
        $this->vendorName = $vendorName;
        $this->parent = $parent;
        $this->isInactive = $isInactive;
        $this->availableToPartners = $availableToPartners;
        $this->department = $department;
        $this->class = $class;
        $this->location = $location;
        $this->currency = $currency;
        $this->itemOptionsList = $itemOptionsList;
        $this->itemVendorList = $itemVendorList;
        $this->purchaseTaxCode = $purchaseTaxCode;
        $this->salesTaxCode = $salesTaxCode;
        $this->translation = $translation;
        $this->vendor = $vendor;
        $this->customFieldList = $customFieldList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>