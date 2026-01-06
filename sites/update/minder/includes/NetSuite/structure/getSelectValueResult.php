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
 * getSelectValueResult map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_getSelectValueResult {
    public $status; //NetSuite_Status
    public $totalRecords;
    public $recordRefList; //NetSuite_RecordRefList

    public function __construct(  NetSuite_Status $status, $totalRecords, NetSuite_RecordRefList $recordRefList) {
        $this->status = $status;
        $this->totalRecords = $totalRecords;
        $this->recordRefList = $recordRefList;
    }
}?>