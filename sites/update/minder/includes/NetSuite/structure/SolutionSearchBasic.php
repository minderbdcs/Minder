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
 * SolutionSearchBasic map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_SolutionSearchBasic {
    public $abstract; //NetSuite_SearchStringField
    public $code; //NetSuite_SearchStringField
    public $description; //NetSuite_SearchStringField
    public $externalId; //NetSuite_SearchMultiSelectField
    public $find; //NetSuite_SearchStringField
    public $internalId; //NetSuite_SearchMultiSelectField
    public $isInactive; //NetSuite_SearchBooleanField
    public $number; //NetSuite_SearchLongField
    public $status; //NetSuite_SearchEnumMultiSelectField
    public $title; //NetSuite_SearchStringField
    public $topic; //NetSuite_SearchMultiSelectField
    public $customFieldList; //NetSuite_SearchCustomFieldList

    public function __construct(  NetSuite_SearchStringField $abstract, NetSuite_SearchStringField $code, NetSuite_SearchStringField $description, NetSuite_SearchMultiSelectField $externalId, NetSuite_SearchStringField $find, NetSuite_SearchMultiSelectField $internalId, NetSuite_SearchBooleanField $isInactive, NetSuite_SearchLongField $number, NetSuite_SearchEnumMultiSelectField $status, NetSuite_SearchStringField $title, NetSuite_SearchMultiSelectField $topic, NetSuite_SearchCustomFieldList $customFieldList) {
        $this->abstract = $abstract;
        $this->code = $code;
        $this->description = $description;
        $this->externalId = $externalId;
        $this->find = $find;
        $this->internalId = $internalId;
        $this->isInactive = $isInactive;
        $this->number = $number;
        $this->status = $status;
        $this->title = $title;
        $this->topic = $topic;
        $this->customFieldList = $customFieldList;
    }
}?>