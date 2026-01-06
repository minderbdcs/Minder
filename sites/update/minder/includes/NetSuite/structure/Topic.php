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
 * Topic map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_Topic {
    public $title;
    public $parentTopic; //NetSuite_RecordRef
    public $description;
    public $isInactive;
    public $longDescription;
    public $solutionList; //NetSuite_TopicSolutionList
    public $internalId;
    public $externalId;

    public function __construct(  $title, NetSuite_RecordRef $parentTopic, $description, $isInactive, $longDescription, NetSuite_TopicSolutionList $solutionList, $internalId, $externalId) {
        $this->title = $title;
        $this->parentTopic = $parentTopic;
        $this->description = $description;
        $this->isInactive = $isInactive;
        $this->longDescription = $longDescription;
        $this->solutionList = $solutionList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>