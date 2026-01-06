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
 * DepartmentSearchBasic map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_DepartmentSearchBasic {
    public $externalId; //NetSuite_SearchMultiSelectField
    public $internalId; //NetSuite_SearchMultiSelectField
    public $isInactive; //NetSuite_SearchBooleanField
    public $name; //NetSuite_SearchStringField
    public $customFieldList; //NetSuite_SearchCustomFieldList

    public function __construct(  NetSuite_SearchMultiSelectField $externalId, NetSuite_SearchMultiSelectField $internalId, NetSuite_SearchBooleanField $isInactive, NetSuite_SearchStringField $name, NetSuite_SearchCustomFieldList $customFieldList) {
        $this->externalId = $externalId;
        $this->internalId = $internalId;
        $this->isInactive = $isInactive;
        $this->name = $name;
        $this->customFieldList = $customFieldList;
    }
}?>