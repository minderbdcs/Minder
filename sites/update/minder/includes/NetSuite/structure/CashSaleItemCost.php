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
 * CashSaleItemCost map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_CashSaleItemCost {
    public $apply;
    public $doc;
    public $line;
    public $billedDate;
    public $itemDisp;
    public $desc;
    public $departmentDisp;
    public $classDisp;
    public $locationDisp;
    public $unitDisp;
    public $options; //NetSuite_CustomFieldList
    public $count;
    public $serialNumbers;
    public $cost;
    public $amount;
    public $revRecSchedule; //NetSuite_RecordRef
    public $revRecStartDate;
    public $revRecEndDate;
    public $grossAmt;
    public $tax1Amt;
    public $taxCode; //NetSuite_RecordRef
    public $taxRate1;
    public $taxRate2;

    public function __construct(  $apply, $doc, $line, $billedDate, $itemDisp, $desc, $departmentDisp, $classDisp, $locationDisp, $unitDisp, NetSuite_CustomFieldList $options, $count, $serialNumbers, $cost, $amount, NetSuite_RecordRef $revRecSchedule, $revRecStartDate, $revRecEndDate, $grossAmt, $tax1Amt, NetSuite_RecordRef $taxCode, $taxRate1, $taxRate2) {
        $this->apply = $apply;
        $this->doc = $doc;
        $this->line = $line;
        $this->billedDate = $billedDate;
        $this->itemDisp = $itemDisp;
        $this->desc = $desc;
        $this->departmentDisp = $departmentDisp;
        $this->classDisp = $classDisp;
        $this->locationDisp = $locationDisp;
        $this->unitDisp = $unitDisp;
        $this->options = $options;
        $this->count = $count;
        $this->serialNumbers = $serialNumbers;
        $this->cost = $cost;
        $this->amount = $amount;
        $this->revRecSchedule = $revRecSchedule;
        $this->revRecStartDate = $revRecStartDate;
        $this->revRecEndDate = $revRecEndDate;
        $this->grossAmt = $grossAmt;
        $this->tax1Amt = $tax1Amt;
        $this->taxCode = $taxCode;
        $this->taxRate1 = $taxRate1;
        $this->taxRate2 = $taxRate2;
    }
}?>