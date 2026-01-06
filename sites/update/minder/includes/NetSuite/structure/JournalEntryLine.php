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
 * JournalEntryLine map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_JournalEntryLine {
    public $account; //NetSuite_RecordRef
    public $debit;
    public $credit;
    public $memo;
    public $entity; //NetSuite_RecordRef
    public $department; //NetSuite_RecordRef
    public $class; //NetSuite_RecordRef
    public $location; //NetSuite_RecordRef
    public $amortizationSched; //NetSuite_RecordRef
    public $amortizStartDate;
    public $amortizationEndDate;
    public $amortizationResidual;
    public $customFieldList; //NetSuite_CustomFieldList

    public function __construct(  NetSuite_RecordRef $account, $debit, $credit, $memo, NetSuite_RecordRef $entity, NetSuite_RecordRef $department, NetSuite_RecordRef $class, NetSuite_RecordRef $location, NetSuite_RecordRef $amortizationSched, $amortizStartDate, $amortizationEndDate, $amortizationResidual, NetSuite_CustomFieldList $customFieldList) {
        $this->account = $account;
        $this->debit = $debit;
        $this->credit = $credit;
        $this->memo = $memo;
        $this->entity = $entity;
        $this->department = $department;
        $this->class = $class;
        $this->location = $location;
        $this->amortizationSched = $amortizationSched;
        $this->amortizStartDate = $amortizStartDate;
        $this->amortizationEndDate = $amortizationEndDate;
        $this->amortizationResidual = $amortizationResidual;
        $this->customFieldList = $customFieldList;
    }
}?>