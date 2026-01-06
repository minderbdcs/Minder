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
 * CustomRecordSearchBasic map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_CustomRecordSearchBasic {
    public $recType; //NetSuite_RecordRef
    public $availableOffline; //NetSuite_SearchBooleanField
    public $created; //NetSuite_SearchDateField
    public $externalId; //NetSuite_SearchMultiSelectField
    public $internalId; //NetSuite_SearchMultiSelectField
    public $isInactive; //NetSuite_SearchBooleanField
    public $lastModified; //NetSuite_SearchDateField
    public $lastModifiedBy; //NetSuite_SearchMultiSelectField
    public $name; //NetSuite_SearchStringField
    public $owner; //NetSuite_SearchMultiSelectField
    public $customFieldList; //NetSuite_SearchCustomFieldList

    public function __construct(  NetSuite_RecordRef $recType, NetSuite_SearchBooleanField $availableOffline, NetSuite_SearchDateField $created, NetSuite_SearchMultiSelectField $externalId, NetSuite_SearchMultiSelectField $internalId, NetSuite_SearchBooleanField $isInactive, NetSuite_SearchDateField $lastModified, NetSuite_SearchMultiSelectField $lastModifiedBy, NetSuite_SearchStringField $name, NetSuite_SearchMultiSelectField $owner, NetSuite_SearchCustomFieldList $customFieldList) {
        $this->recType = $recType;
        $this->availableOffline = $availableOffline;
        $this->created = $created;
        $this->externalId = $externalId;
        $this->internalId = $internalId;
        $this->isInactive = $isInactive;
        $this->lastModified = $lastModified;
        $this->lastModifiedBy = $lastModifiedBy;
        $this->name = $name;
        $this->owner = $owner;
        $this->customFieldList = $customFieldList;
    }
}?>