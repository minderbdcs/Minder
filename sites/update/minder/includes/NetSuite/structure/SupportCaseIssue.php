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
 * SupportCaseIssue map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_SupportCaseIssue {
    public $name;
    public $insertBefore; //NetSuite_RecordRef
    public $description;
    public $isInactive;
    public $internalId;
    public $externalId;

    public function __construct(  $name, NetSuite_RecordRef $insertBefore, $description, $isInactive, $internalId, $externalId) {
        $this->name = $name;
        $this->insertBefore = $insertBefore;
        $this->description = $description;
        $this->isInactive = $isInactive;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>