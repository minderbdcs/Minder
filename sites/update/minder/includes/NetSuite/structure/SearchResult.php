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
 * SearchResult map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_SearchResult {
    public $status; //NetSuite_Status
    public $totalRecords;
    public $pageSize;
    public $totalPages;
    public $pageIndex;
    public $recordList; //NetSuite_RecordList

    public function __construct(  NetSuite_Status $status, $totalRecords, $pageSize, $totalPages, $pageIndex, NetSuite_RecordList $recordList) {
        $this->status = $status;
        $this->totalRecords = $totalRecords;
        $this->pageSize = $pageSize;
        $this->totalPages = $totalPages;
        $this->pageIndex = $pageIndex;
        $this->recordList = $recordList;
    }
}?>