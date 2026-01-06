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
 * CustomRecord map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_CustomRecord {
    public $customForm; //NetSuite_RecordRef
    public $isInactive;
    public $created;
    public $lastModified;
    public $name;
    public $owner; //NetSuite_RecordRef
    public $recType; //NetSuite_RecordRef
    public $customFieldList; //NetSuite_CustomFieldList
    public $internalId;
    public $externalId;

    public function __construct(  NetSuite_RecordRef $customForm, $isInactive, $created, $lastModified, $name, NetSuite_RecordRef $owner, NetSuite_RecordRef $recType, NetSuite_CustomFieldList $customFieldList, $internalId, $externalId) {
        $this->customForm = $customForm;
        $this->isInactive = $isInactive;
        $this->created = $created;
        $this->lastModified = $lastModified;
        $this->name = $name;
        $this->owner = $owner;
        $this->recType = $recType;
        $this->customFieldList = $customFieldList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>