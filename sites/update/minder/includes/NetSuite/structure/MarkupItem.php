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
 * MarkupItem map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_MarkupItem {
    public $createdDate;
    public $lastModifiedDate;
    public $description;
    public $nonPosting;
    public $account; //NetSuite_RecordRef
    public $rate;
    public $isPreTax;
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
    public $deferredRevenueAccount; //NetSuite_RecordRef
    public $expenseAccount; //NetSuite_RecordRef
    public $incomeAccount; //NetSuite_RecordRef
    public $revRecSchedule; //NetSuite_RecordRef
    public $salesTaxCode; //NetSuite_RecordRef
    public $translation; //NetSuite_TranslationList
    public $customFieldList; //NetSuite_CustomFieldList
    public $internalId;
    public $externalId;

    public function __construct(  $createdDate, $lastModifiedDate, $description, $nonPosting, NetSuite_RecordRef $account, $rate, $isPreTax, NetSuite_RecordRef $customForm, $itemId, $nameIsUPC, $displayName, $vendorName, NetSuite_RecordRef $parent, $isInactive, $availableToPartners, NetSuite_RecordRef $department, NetSuite_RecordRef $class, NetSuite_RecordRef $location, NetSuite_RecordRef $deferredRevenueAccount, NetSuite_RecordRef $expenseAccount, NetSuite_RecordRef $incomeAccount, NetSuite_RecordRef $revRecSchedule, NetSuite_RecordRef $salesTaxCode, NetSuite_TranslationList $translation, NetSuite_CustomFieldList $customFieldList, $internalId, $externalId) {
        $this->createdDate = $createdDate;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->description = $description;
        $this->nonPosting = $nonPosting;
        $this->account = $account;
        $this->rate = $rate;
        $this->isPreTax = $isPreTax;
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
        $this->deferredRevenueAccount = $deferredRevenueAccount;
        $this->expenseAccount = $expenseAccount;
        $this->incomeAccount = $incomeAccount;
        $this->revRecSchedule = $revRecSchedule;
        $this->salesTaxCode = $salesTaxCode;
        $this->translation = $translation;
        $this->customFieldList = $customFieldList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>