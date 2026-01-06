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
 * Solution map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_Solution {
    public $customForm; //NetSuite_RecordRef
    public $solutionCode;
    public $title;
    public $message;
    public $status;
    public $displayOnline;
    public $isInactive;
    public $longDescription;
    public $topicsList; //NetSuite_SolutionTopicsList
    public $solutionsList; //NetSuite_SolutionsList
    public $customFieldList; //NetSuite_CustomFieldList
    public $internalId;
    public $externalId;

    public function __construct(  NetSuite_RecordRef $customForm, $solutionCode, $title, $message, $status, $displayOnline, $isInactive, $longDescription, NetSuite_SolutionTopicsList $topicsList, NetSuite_SolutionsList $solutionsList, NetSuite_CustomFieldList $customFieldList, $internalId, $externalId) {
        $this->customForm = $customForm;
        $this->solutionCode = $solutionCode;
        $this->title = $title;
        $this->message = $message;
        $this->status = $status;
        $this->displayOnline = $displayOnline;
        $this->isInactive = $isInactive;
        $this->longDescription = $longDescription;
        $this->topicsList = $topicsList;
        $this->solutionsList = $solutionsList;
        $this->customFieldList = $customFieldList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>