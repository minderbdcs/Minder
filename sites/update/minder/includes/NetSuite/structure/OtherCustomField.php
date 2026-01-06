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
 * OtherCustomField map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_OtherCustomField {
    public $recType; //NetSuite_RecordRef
    public $label;
    public $fieldType;
    public $selectRecordType; //NetSuite_RecordRef
    public $storeValue;
    public $showInList;
    public $insertBefore; //NetSuite_RecordRef
    public $displayType;
    public $displayWidth;
    public $displayHeight;
    public $help;
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
    public $filterList; //NetSuite_OtherCustomFieldFilterList
    public $internalId;

    public function __construct(  NetSuite_RecordRef $recType, $label, $fieldType, NetSuite_RecordRef $selectRecordType, $storeValue, $showInList, NetSuite_RecordRef $insertBefore, $displayType, $displayWidth, $displayHeight, $help, $linkText, $isMandatory, $maxLength, $minValue, $maxValue, $defaultChecked, $defaultValue, $isFormula, NetSuite_RecordRef $defaultSelection, $dynamicDefault, NetSuite_RecordRef $sourceList, NetSuite_RecordRef $sourceFrom, NetSuite_RecordRef $sourceFilterBy, NetSuite_OtherCustomFieldFilterList $filterList, $internalId) {
        $this->recType = $recType;
        $this->label = $label;
        $this->fieldType = $fieldType;
        $this->selectRecordType = $selectRecordType;
        $this->storeValue = $storeValue;
        $this->showInList = $showInList;
        $this->insertBefore = $insertBefore;
        $this->displayType = $displayType;
        $this->displayWidth = $displayWidth;
        $this->displayHeight = $displayHeight;
        $this->help = $help;
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
        $this->filterList = $filterList;
        $this->internalId = $internalId;
    }
}?>