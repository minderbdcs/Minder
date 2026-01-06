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
 * TransactionBodyCustomField map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_TransactionBodyCustomField {
    public $label;
    public $fieldType;
    public $selectRecordType; //NetSuite_RecordRef
    public $storeValue;
    public $showInList;
    public $isParent;
    public $insertBefore; //NetSuite_RecordRef
    public $subtab; //NetSuite_RecordRef
    public $displayType;
    public $displayWidth;
    public $displayHeight;
    public $help;
    public $parentSubtab; //NetSuite_RecordRef
    public $linkText;
    public $isMandatory;
    public $maxLength;
    public $minValue;
    public $maxValue;
    public $defaultChecked;
    public $defaultValue;
    public $isFormula;
    public $defaultSelection; //NetSuite_RecordRef
    public $dynamicDefault;
    public $sourceList; //NetSuite_RecordRef
    public $sourceFrom; //NetSuite_RecordRef
    public $sourceFilterBy; //NetSuite_RecordRef
    public $bodyPurchase;
    public $bodySale;
    public $bodyOpportunity;
    public $bodyJournal;
    public $bodyExpenseReport;
    public $bodyStore;
    public $bodyItemReceipt;
    public $bodyItemReceiptOrder;
    public $bodyItemFulfillment;
    public $bodyItemFulfillmentOrder;
    public $bodyInventoryAdjustment;
    public $bodyAssemblyBuild;
    public $bodyPrintFlag;
    public $bodyPickingTicket;
    public $bodyPrintPackingSlip;
    public $bodyPrintStatement;
    public $filterList; //NetSuite_TransactionBodyCustomFieldFilterList
    public $internalId;

    public function __construct(  $label, $fieldType, NetSuite_RecordRef $selectRecordType, $storeValue, $showInList, $isParent, NetSuite_RecordRef $insertBefore, NetSuite_RecordRef $subtab, $displayType, $displayWidth, $displayHeight, $help, NetSuite_RecordRef $parentSubtab, $linkText, $isMandatory, $maxLength, $minValue, $maxValue, $defaultChecked, $defaultValue, $isFormula, NetSuite_RecordRef $defaultSelection, $dynamicDefault, NetSuite_RecordRef $sourceList, NetSuite_RecordRef $sourceFrom, NetSuite_RecordRef $sourceFilterBy, $bodyPurchase, $bodySale, $bodyOpportunity, $bodyJournal, $bodyExpenseReport, $bodyStore, $bodyItemReceipt, $bodyItemReceiptOrder, $bodyItemFulfillment, $bodyItemFulfillmentOrder, $bodyInventoryAdjustment, $bodyAssemblyBuild, $bodyPrintFlag, $bodyPickingTicket, $bodyPrintPackingSlip, $bodyPrintStatement, NetSuite_TransactionBodyCustomFieldFilterList $filterList, $internalId) {
        $this->label = $label;
        $this->fieldType = $fieldType;
        $this->selectRecordType = $selectRecordType;
        $this->storeValue = $storeValue;
        $this->showInList = $showInList;
        $this->isParent = $isParent;
        $this->insertBefore = $insertBefore;
        $this->subtab = $subtab;
        $this->displayType = $displayType;
        $this->displayWidth = $displayWidth;
        $this->displayHeight = $displayHeight;
        $this->help = $help;
        $this->parentSubtab = $parentSubtab;
        $this->linkText = $linkText;
        $this->isMandatory = $isMandatory;
        $this->maxLength = $maxLength;
        $this->minValue = $minValue;
        $this->maxValue = $maxValue;
        $this->defaultChecked = $defaultChecked;
        $this->defaultValue = $defaultValue;
        $this->isFormula = $isFormula;
        $this->defaultSelection = $defaultSelection;
        $this->dynamicDefault = $dynamicDefault;
        $this->sourceList = $sourceList;
        $this->sourceFrom = $sourceFrom;
        $this->sourceFilterBy = $sourceFilterBy;
        $this->bodyPurchase = $bodyPurchase;
        $this->bodySale = $bodySale;
        $this->bodyOpportunity = $bodyOpportunity;
        $this->bodyJournal = $bodyJournal;
        $this->bodyExpenseReport = $bodyExpenseReport;
        $this->bodyStore = $bodyStore;
        $this->bodyItemReceipt = $bodyItemReceipt;
        $this->bodyItemReceiptOrder = $bodyItemReceiptOrder;
        $this->bodyItemFulfillment = $bodyItemFulfillment;
        $this->bodyItemFulfillmentOrder = $bodyItemFulfillmentOrder;
        $this->bodyInventoryAdjustment = $bodyInventoryAdjustment;
        $this->bodyAssemblyBuild = $bodyAssemblyBuild;
        $this->bodyPrintFlag = $bodyPrintFlag;
        $this->bodyPickingTicket = $bodyPickingTicket;
        $this->bodyPrintPackingSlip = $bodyPrintPackingSlip;
        $this->bodyPrintStatement = $bodyPrintStatement;
        $this->filterList = $filterList;
        $this->internalId = $internalId;
    }
}?>