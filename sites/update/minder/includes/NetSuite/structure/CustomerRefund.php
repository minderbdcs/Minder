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
 * CustomerRefund map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_CustomerRefund {
    public $createdDate;
    public $lastModifiedDate;
    public $status;
    public $customer; //NetSuite_RecordRef
    public $balance;
    public $arAcct; //NetSuite_RecordRef
    public $currencyName;
    public $exchangeRate;
    public $address;
    public $total;
    public $tranDate;
    public $postingPeriod; //NetSuite_RecordRef
    public $memo;
    public $paymentMethod; //NetSuite_RecordRef
    public $account; //NetSuite_RecordRef
    public $toBePrinted;
    public $tranId;
    public $debitCardIssueNo;
    public $creditCardProcessor; //NetSuite_RecordRef
    public $chargeIt;
    public $pnRefNum;
    public $validFrom;
    public $creditCard; //NetSuite_RecordRef
    public $ccNumber;
    public $ccExpireDate;
    public $ccName;
    public $ccStreet;
    public $ccZipCode;
    public $ccApproved;
    public $applyList; //NetSuite_CustomerRefundApplyList
    public $internalId;
    public $externalId;

    public function __construct(  $createdDate, $lastModifiedDate, $status, NetSuite_RecordRef $customer, $balance, NetSuite_RecordRef $arAcct, $currencyName, $exchangeRate, $address, $total, $tranDate, NetSuite_RecordRef $postingPeriod, $memo, NetSuite_RecordRef $paymentMethod, NetSuite_RecordRef $account, $toBePrinted, $tranId, $debitCardIssueNo, NetSuite_RecordRef $creditCardProcessor, $chargeIt, $pnRefNum, $validFrom, NetSuite_RecordRef $creditCard, $ccNumber, $ccExpireDate, $ccName, $ccStreet, $ccZipCode, $ccApproved, NetSuite_CustomerRefundApplyList $applyList, $internalId, $externalId) {
        $this->createdDate = $createdDate;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->status = $status;
        $this->customer = $customer;
        $this->balance = $balance;
        $this->arAcct = $arAcct;
        $this->currencyName = $currencyName;
        $this->exchangeRate = $exchangeRate;
        $this->address = $address;
        $this->total = $total;
        $this->tranDate = $tranDate;
        $this->postingPeriod = $postingPeriod;
        $this->memo = $memo;
        $this->paymentMethod = $paymentMethod;
        $this->account = $account;
        $this->toBePrinted = $toBePrinted;
        $this->tranId = $tranId;
        $this->debitCardIssueNo = $debitCardIssueNo;
        $this->creditCardProcessor = $creditCardProcessor;
        $this->chargeIt = $chargeIt;
        $this->pnRefNum = $pnRefNum;
        $this->validFrom = $validFrom;
        $this->creditCard = $creditCard;
        $this->ccNumber = $ccNumber;
        $this->ccExpireDate = $ccExpireDate;
        $this->ccName = $ccName;
        $this->ccStreet = $ccStreet;
        $this->ccZipCode = $ccZipCode;
        $this->ccApproved = $ccApproved;
        $this->applyList = $applyList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>