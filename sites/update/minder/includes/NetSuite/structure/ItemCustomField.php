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
 * ItemCustomField map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_ItemCustomField {
    public $label;
    public $itemMatrix;
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
    public $appliesToInventory;
    public $appliesToNonInventory;
    public $appliesToService;
    public $appliesToOtherCharge;
    public $appliesToGroup;
    public $appliesToKit;
    public $appliesToItemAssembly;
    public $itemSubType;
    public $filterList; //NetSuite_ItemCustomFieldFilterList
    public $internalId;

    public function __construct(  $label, $itemMatrix, $fieldType, NetSuite_RecordRef $selectRecordType, $storeValue, $showInList, $isParent, NetSuite_RecordRef $insertBefore, NetSuite_RecordRef $subtab, $displayType, $displayWidth, $displayHeight, $help, NetSuite_RecordRef $parentSubtab, $linkText, $isMandatory, $maxLength, $minValue, $maxValue, $defaultChecked, $defaultValue, $isFormula, NetSuite_RecordRef $defaultSelection, $dynamicDefault, NetSuite_RecordRef $sourceList, NetSuite_RecordRef $sourceFrom, NetSuite_RecordRef $sourceFilterBy, $appliesToInventory, $appliesToNonInventory, $appliesToService, $appliesToOtherCharge, $appliesToGroup, $appliesToKit, $appliesToItemAssembly, $itemSubType, NetSuite_ItemCustomFieldFilterList $filterList, $internalId) {
        $this->label = $label;
        $this->itemMatrix = $itemMatrix;
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
        $this->appliesToInventory = $appliesToInventory;
        $this->appliesToNonInventory = $appliesToNonInventory;
        $this->appliesToService = $appliesToService;
        $this->appliesToOtherCharge = $appliesToOtherCharge;
        $this->appliesToGroup = $appliesToGroup;
        $this->appliesToKit = $appliesToKit;
        $this->appliesToItemAssembly = $appliesToItemAssembly;
        $this->itemSubType = $itemSubType;
        $this->filterList = $filterList;
        $this->internalId = $internalId;
    }
}?>