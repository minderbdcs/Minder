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
 * ItemCustomFieldFilter map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_ItemCustomFieldFilter {
    public $fldFilter; //NetSuite_RecordRef
    public $fldFilterChecked;
    public $fldFilterCompareType;
    public $fldFilterVal;
    public $fldFilterSelList; //NetSuite_FldFilterSelList
    public $fldFilterNotNull;

    public function __construct(  NetSuite_RecordRef $fldFilter, $fldFilterChecked, $fldFilterCompareType, $fldFilterVal, NetSuite_FldFilterSelList $fldFilterSelList, $fldFilterNotNull) {
        $this->fldFilter = $fldFilter;
        $this->fldFilterChecked = $fldFilterChecked;
        $this->fldFilterCompareType = $fldFilterCompareType;
        $this->fldFilterVal = $fldFilterVal;
        $this->fldFilterSelList = $fldFilterSelList;
        $this->fldFilterNotNull = $fldFilterNotNull;
    }
}?>