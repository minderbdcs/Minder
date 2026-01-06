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
 * CustomRecordType map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_CustomRecordType {
    public $recordName;
    public $includeName;
    public $showId;
    public $showCreationDate;
    public $showCreationDateOnList;
    public $showLastModified;
    public $showLastModifiedOnList;
    public $showOwner;
    public $showOwnerOnList;
    public $showOwnerAllowChange;
    public $usePermissions;
    public $allowAttachments;
    public $showNotes;
    public $enableMailMerge;
    public $isOrdered;
    public $allowInlineEditing;
    public $isAvailableOffline;
    public $allowQuickSearch;
    public $isInactive;
    public $disclaimer;
    public $enableNumbering;
    public $numberingPrefix;
    public $numberingSuffix;
    public $numberingMinDigits;
    public $numberingInit;
    public $numberingCurrentNumber;
    public $allowNumberingOverride;
    public $isNumberingUpdateable;
    public $fieldList; //NetSuite_CustomRecordTypeFieldList
    public $tabsList; //NetSuite_CustomRecordTypeTabsList
    public $formsList; //NetSuite_CustomRecordTypeFormsList
    public $onlineFormsList; //NetSuite_CustomRecordTypeOnlineFormsList
    public $permissionsList; //NetSuite_CustomRecordTypePermissionsList
    public $linksList; //NetSuite_CustomRecordTypeLinksList
    public $managersList; //NetSuite_CustomRecordTypeManagersList
    public $childrenList; //NetSuite_CustomRecordTypeChildrenList
    public $parentsList; //NetSuite_CustomRecordTypeParentsList
    public $internalId;

    public function __construct(  $recordName, $includeName, $showId, $showCreationDate, $showCreationDateOnList, $showLastModified, $showLastModifiedOnList, $showOwner, $showOwnerOnList, $showOwnerAllowChange, $usePermissions, $allowAttachments, $showNotes, $enableMailMerge, $isOrdered, $allowInlineEditing, $isAvailableOffline, $allowQuickSearch, $isInactive, $disclaimer, $enableNumbering, $numberingPrefix, $numberingSuffix, $numberingMinDigits, $numberingInit, $numberingCurrentNumber, $allowNumberingOverride, $isNumberingUpdateable, NetSuite_CustomRecordTypeFieldList $fieldList, NetSuite_CustomRecordTypeTabsList $tabsList, NetSuite_CustomRecordTypeFormsList $formsList, NetSuite_CustomRecordTypeOnlineFormsList $onlineFormsList, NetSuite_CustomRecordTypePermissionsList $permissionsList, NetSuite_CustomRecordTypeLinksList $linksList, NetSuite_CustomRecordTypeManagersList $managersList, NetSuite_CustomRecordTypeChildrenList $childrenList, NetSuite_CustomRecordTypeParentsList $parentsList, $internalId) {
        $this->recordName = $recordName;
        $this->includeName = $includeName;
        $this->showId = $showId;
        $this->showCreationDate = $showCreationDate;
        $this->showCreationDateOnList = $showCreationDateOnList;
        $this->showLastModified = $showLastModified;
        $this->showLastModifiedOnList = $showLastModifiedOnList;
        $this->showOwner = $showOwner;
        $this->showOwnerOnList = $showOwnerOnList;
        $this->showOwnerAllowChange = $showOwnerAllowChange;
        $this->usePermissions = $usePermissions;
        $this->allowAttachments = $allowAttachments;
        $this->showNotes = $showNotes;
        $this->enableMailMerge = $enableMailMerge;
        $this->isOrdered = $isOrdered;
        $this->allowInlineEditing = $allowInlineEditing;
        $this->isAvailableOffline = $isAvailableOffline;
        $this->allowQuickSearch = $allowQuickSearch;
        $this->isInactive = $isInactive;
        $this->disclaimer = $disclaimer;
        $this->enableNumbering = $enableNumbering;
        $this->numberingPrefix = $numberingPrefix;
        $this->numberingSuffix = $numberingSuffix;
        $this->numberingMinDigits = $numberingMinDigits;
        $this->numberingInit = $numberingInit;
        $this->numberingCurrentNumber = $numberingCurrentNumber;
        $this->allowNumberingOverride = $allowNumberingOverride;
        $this->isNumberingUpdateable = $isNumberingUpdateable;
        $this->fieldList = $fieldList;
        $this->tabsList = $tabsList;
        $this->formsList = $formsList;
        $this->onlineFormsList = $onlineFormsList;
        $this->permissionsList = $permissionsList;
        $this->linksList = $linksList;
        $this->managersList = $managersList;
        $this->childrenList = $childrenList;
        $this->parentsList = $parentsList;
        $this->internalId = $internalId;
    }
}?>