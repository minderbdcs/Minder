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
 * CustomList map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_CustomList {
    public $name;
    public $isOrdered;
    public $isMatrixOption;
    public $convertToCustomRecord;
    public $isInactive;
    public $customValueList; //NetSuite_CustomListCustomValueList
    public $internalId;

    public function __construct(  $name, $isOrdered, $isMatrixOption, $convertToCustomRecord, $isInactive, NetSuite_CustomListCustomValueList $customValueList, $internalId) {
        $this->name = $name;
        $this->isOrdered = $isOrdered;
        $this->isMatrixOption = $isMatrixOption;
        $this->convertToCustomRecord = $convertToCustomRecord;
        $this->isInactive = $isInactive;
        $this->customValueList = $customValueList;
        $this->internalId = $internalId;
    }
}?>