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
 * ItemOptionCustomField map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_ItemOptionCustomField {
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
    public $colPurchase;
    public $colSale;
    public $colOpportunity;
    public $colStore;
    public $colStoreHidden;
    public $colAllItems;
    public $itemsList; //NetSuite_ItemsList
    public $filterList; //NetSuite_ItemOptionCustomFieldFilterList
    public $internalId;

    public function __construct(  $label, $fieldType, NetSuite_RecordRef $selectRecordType, $storeValue, NetSuite_RecordRef $insertBefore, $displayType, $displayWidth, $displayHeight, $help, $isMandatory, $maxLength, $minValue, $maxValue, $defaultChecked, $defaultValue, $isFormula, NetSuite_RecordRef $defaultSelection, $dynamicDefault, NetSuite_RecordRef $sourceList, NetSuite_RecordRef $sourceFrom, NetSuite_RecordRef $sourceFilterBy, $colPurchase, $colSale, $colOpportunity, $colStore, $colStoreHidden, $colAllItems, NetSuite_ItemsList $itemsList, NetSuite_ItemOptionCustomFieldFilterList $filterList, $internalId) {
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
        $this->colPurchase = $colPurchase;
        $this->colSale = $colSale;
        $this->colOpportunity = $colOpportunity;
        $this->colStore = $colStore;
        $this->colStoreHidden = $colStoreHidden;
        $this->colAllItems = $colAllItems;
        $this->itemsList = $itemsList;
        $this->filterList = $filterList;
        $this->internalId = $internalId;
    }
}?>