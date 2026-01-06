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
 * JournalEntry map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_JournalEntry {
    public $postingPeriod; //NetSuite_RecordRef
    public $tranDate;
    public $currency; //NetSuite_RecordRef
    public $exchangeRate;
    public $tranId;
    public $reversalDate;
    public $reversalDefer;
    public $reversalEntry;
    public $createdFrom; //NetSuite_RecordRef
    public $department; //NetSuite_RecordRef
    public $class; //NetSuite_RecordRef
    public $location; //NetSuite_RecordRef
    public $approved;
    public $lineList; //NetSuite_JournalEntryLineList
    public $customFieldList; //NetSuite_CustomFieldList
    public $internalId;
    public $externalId;

    public function __construct(  NetSuite_RecordRef $postingPeriod, $tranDate, NetSuite_RecordRef $currency, $exchangeRate, $tranId, $reversalDate, $reversalDefer, $reversalEntry, NetSuite_RecordRef $createdFrom, NetSuite_RecordRef $department, NetSuite_RecordRef $class, NetSuite_RecordRef $location, $approved, NetSuite_JournalEntryLineList $lineList, NetSuite_CustomFieldList $customFieldList, $internalId, $externalId) {
        $this->postingPeriod = $postingPeriod;
        $this->tranDate = $tranDate;
        $this->currency = $currency;
        $this->exchangeRate = $exchangeRate;
        $this->tranId = $tranId;
        $this->reversalDate = $reversalDate;
        $this->reversalDefer = $reversalDefer;
        $this->reversalEntry = $reversalEntry;
        $this->createdFrom = $createdFrom;
        $this->department = $department;
        $this->class = $class;
        $this->location = $location;
        $this->approved = $approved;
        $this->lineList = $lineList;
        $this->customFieldList = $customFieldList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>