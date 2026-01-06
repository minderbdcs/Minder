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
 * InvoiceExpCost map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_InvoiceExpCost {
    public $apply;
    public $doc;
    public $line;
    public $billedDate;
    public $employeeDisp;
    public $categoryDisp;
    public $memo;
    public $departmentDisp;
    public $classDisp;
    public $locationDisp;
    public $originalAmount;
    public $amount;
    public $taxableDisp;
    public $revRecSchedule; //NetSuite_RecordRef
    public $revRecStartDate;
    public $revRecEndDate;
    public $grossAmt;
    public $tax1Amt;
    public $taxCode; //NetSuite_RecordRef
    public $taxRate1;
    public $taxRate2;

    public function __construct(  $apply, $doc, $line, $billedDate, $employeeDisp, $categoryDisp, $memo, $departmentDisp, $classDisp, $locationDisp, $originalAmount, $amount, $taxableDisp, NetSuite_RecordRef $revRecSchedule, $revRecStartDate, $revRecEndDate, $grossAmt, $tax1Amt, NetSuite_RecordRef $taxCode, $taxRate1, $taxRate2) {
        $this->apply = $apply;
        $this->doc = $doc;
        $this->line = $line;
        $this->billedDate = $billedDate;
        $this->employeeDisp = $employeeDisp;
        $this->categoryDisp = $categoryDisp;
        $this->memo = $memo;
        $this->departmentDisp = $departmentDisp;
        $this->classDisp = $classDisp;
        $this->locationDisp = $locationDisp;
        $this->originalAmount = $originalAmount;
        $this->amount = $amount;
        $this->taxableDisp = $taxableDisp;
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