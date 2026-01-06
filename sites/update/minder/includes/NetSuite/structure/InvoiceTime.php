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
 * InvoiceTime map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_InvoiceTime {
    public $apply;
    public $doc;
    public $billedDate;
    public $employeeDisp;
    public $itemDisp;
    public $departmentDisp;
    public $classDisp;
    public $locationDisp;
    public $quantity;
    public $rate;
    public $amount;
    public $desc;
    public $revRecSchedule; //NetSuite_RecordRef
    public $revRecStartDate;
    public $revRecEndDate;
    public $grossAmt;
    public $tax1Amt;
    public $taxCode; //NetSuite_RecordRef
    public $taxRate1;
    public $taxRate2;

    public function __construct(  $apply, $doc, $billedDate, $employeeDisp, $itemDisp, $departmentDisp, $classDisp, $locationDisp, $quantity, $rate, $amount, $desc, NetSuite_RecordRef $revRecSchedule, $revRecStartDate, $revRecEndDate, $grossAmt, $tax1Amt, NetSuite_RecordRef $taxCode, $taxRate1, $taxRate2) {
        $this->apply = $apply;
        $this->doc = $doc;
        $this->billedDate = $billedDate;
        $this->employeeDisp = $employeeDisp;
        $this->itemDisp = $itemDisp;
        $this->departmentDisp = $departmentDisp;
        $this->classDisp = $classDisp;
        $this->locationDisp = $locationDisp;
        $this->quantity = $quantity;
        $this->rate = $rate;
        $this->amount = $amount;
        $this->desc = $desc;
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