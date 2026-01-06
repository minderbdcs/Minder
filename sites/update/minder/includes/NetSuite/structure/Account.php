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
 * Account map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_Account {
    public $acctType;
    public $acctNumber;
    public $acctName;
    public $currency; //NetSuite_RecordRef
    public $exchangeRate;
    public $parent; //NetSuite_RecordRef
    public $billableExpensesAcct; //NetSuite_RecordRef
    public $deferralAcct; //NetSuite_RecordRef
    public $description;
    public $curDocNum;
    public $isInactive;
    public $department; //NetSuite_RecordRef
    public $class; //NetSuite_RecordRef
    public $location; //NetSuite_RecordRef
    public $category1099misc; //NetSuite_RecordRef
    public $incomeStatement; //NetSuite_RecordRef
    public $balanceSheet; //NetSuite_RecordRef
    public $cashFlow; //NetSuite_RecordRef
    public $openingBalance;
    public $tranDate;
    public $customFieldList; //NetSuite_CustomFieldList
    public $internalId;
    public $externalId;

    public function __construct(  $acctType, $acctNumber, $acctName, NetSuite_RecordRef $currency, $exchangeRate, NetSuite_RecordRef $parent, NetSuite_RecordRef $billableExpensesAcct, NetSuite_RecordRef $deferralAcct, $description, $curDocNum, $isInactive, NetSuite_RecordRef $department, NetSuite_RecordRef $class, NetSuite_RecordRef $location, NetSuite_RecordRef $category1099misc, NetSuite_RecordRef $incomeStatement, NetSuite_RecordRef $balanceSheet, NetSuite_RecordRef $cashFlow, $openingBalance, $tranDate, NetSuite_CustomFieldList $customFieldList, $internalId, $externalId) {
        $this->acctType = $acctType;
        $this->acctNumber = $acctNumber;
        $this->acctName = $acctName;
        $this->currency = $currency;
        $this->exchangeRate = $exchangeRate;
        $this->parent = $parent;
        $this->billableExpensesAcct = $billableExpensesAcct;
        $this->deferralAcct = $deferralAcct;
        $this->description = $description;
        $this->curDocNum = $curDocNum;
        $this->isInactive = $isInactive;
        $this->department = $department;
        $this->class = $class;
        $this->location = $location;
        $this->category1099misc = $category1099misc;
        $this->incomeStatement = $incomeStatement;
        $this->balanceSheet = $balanceSheet;
        $this->cashFlow = $cashFlow;
        $this->openingBalance = $openingBalance;
        $this->tranDate = $tranDate;
        $this->customFieldList = $customFieldList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>