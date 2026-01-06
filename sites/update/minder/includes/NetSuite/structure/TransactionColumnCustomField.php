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
 * TransactionColumnCustomField map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_TransactionColumnCustomField {
    public $label;
    public $fieldType;
    public $selectRecordType; //NetSuite_RecordRef
    public $storeValue;
    public $insertBefore; //NetSuite_RecordRef
    public $displayType;
    public $displayWidth;
    public $displayHeight;
    public $help;
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
    public $colExpense;
    public $colPurchase;
    public $colSale;
    public $colOpportunity;
    public $colStore;
    public $colStoreHidden;
    public $colJournal;
    public $colExpenseReport;
    public $colTime;
    public $colTimeGroup;
    public $colItemReceipt;
    public $colItemReceiptOrder;
    public $colItemFulfillment;
    public $colItemFulfillmentOrder;
    public $colPrintFlag;
    public $colPickingTicket;
    public $colPackingSlip;
    public $colReturnForm;
    public $colStoreWithGroups;
    public $colKitItem;
    public $filterList; //NetSuite_TransactionColumnCustomFieldFilterList
    public $internalId;

    public function __construct(  $label, $fieldType, NetSuite_RecordRef $selectRecordType, $storeValue, NetSuite_RecordRef $insertBefore, $displayType, $displayWidth, $displayHeight, $help, $isMandatory, $maxLength, $minValue, $maxValue, $defaultChecked, $defaultValue, $isFormula, NetSuite_RecordRef $defaultSelection, $dynamicDefault, NetSuite_RecordRef $sourceList, NetSuite_RecordRef $sourceFrom, NetSuite_RecordRef $sourceFilterBy, $colExpense, $colPurchase, $colSale, $colOpportunity, $colStore, $colStoreHidden, $colJournal, $colExpenseReport, $colTime, $colTimeGroup, $colItemReceipt, $colItemReceiptOrder, $colItemFulfillment, $colItemFulfillmentOrder, $colPrintFlag, $colPickingTicket, $colPackingSlip, $colReturnForm, $colStoreWithGroups, $colKitItem, NetSuite_TransactionColumnCustomFieldFilterList $filterList, $internalId) {
        $this->label = $label;
        $this->fieldType = $fieldType;
        $this->selectRecordType = $selectRecordType;
        $this->storeValue = $storeValue;
        $this->insertBefore = $insertBefore;
        $this->displayType = $displayType;
        $this->displayWidth = $displayWidth;
        $this->displayHeight = $displayHeight;
        $this->help = $help;
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
        $this->colExpense = $colExpense;
        $this->colPurchase = $colPurchase;
        $this->colSale = $colSale;
        $this->colOpportunity = $colOpportunity;
        $this->colStore = $colStore;
        $this->colStoreHidden = $colStoreHidden;
        $this->colJournal = $colJournal;
        $this->colExpenseReport = $colExpenseReport;
        $this->colTime = $colTime;
        $this->colTimeGroup = $colTimeGroup;
        $this->colItemReceipt = $colItemReceipt;
        $this->colItemReceiptOrder = $colItemReceiptOrder;
        $this->colItemFulfillment = $colItemFulfillment;
        $this->colItemFulfillmentOrder = $colItemFulfillmentOrder;
        $this->colPrintFlag = $colPrintFlag;
        $this->colPickingTicket = $colPickingTicket;
        $this->colPackingSlip = $colPackingSlip;
        $this->colReturnForm = $colReturnForm;
        $this->colStoreWithGroups = $colStoreWithGroups;
        $this->colKitItem = $colKitItem;
        $this->filterList = $filterList;
        $this->internalId = $internalId;
    }
}?>