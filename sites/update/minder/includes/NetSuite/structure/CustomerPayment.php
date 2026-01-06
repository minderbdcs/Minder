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
 * CustomerPayment map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_CustomerPayment {
    public $createdDate;
    public $lastModifiedDate;
    public $arAcct; //NetSuite_RecordRef
    public $customer; //NetSuite_RecordRef
    public $balance;
    public $pending;
    public $payment;
    public $autoApply;
    public $tranDate;
    public $postingPeriod; //NetSuite_RecordRef
    public $paymentMethod; //NetSuite_RecordRef
    public $memo;
    public $checkNum;
    public $currencyName;
    public $exchangeRate;
    public $creditCard; //NetSuite_RecordRef
    public $chargeIt;
    public $ccNumber;
    public $ccExpireDate;
    public $ccName;
    public $ccStreet;
    public $ccZipCode;
    public $ccApproved;
    public $authCode;
    public $ccAvsStreetMatch;
    public $ccAvsZipMatch;
    public $ccSecurityCode;
    public $ccSecurityCodeMatch;
    public $pnRefNum;
    public $creditCardProcessor; //NetSuite_RecordRef
    public $debitCardIssueNo;
    public $validFrom;
    public $undepFunds;
    public $account; //NetSuite_RecordRef
    public $total;
    public $applied;
    public $unApplied;
    public $class; //NetSuite_RecordRef
    public $department; //NetSuite_RecordRef
    public $location; //NetSuite_RecordRef
    public $status;
    public $applyList; //NetSuite_CustomerPaymentApplyList
    public $creditList; //NetSuite_CustomerPaymentCreditList
    public $depositList; //NetSuite_CustomerPaymentDepositList
    public $internalId;
    public $externalId;

    public function __construct(  $createdDate, $lastModifiedDate, NetSuite_RecordRef $arAcct, NetSuite_RecordRef $customer, $balance, $pending, $payment, $autoApply, $tranDate, NetSuite_RecordRef $postingPeriod, NetSuite_RecordRef $paymentMethod, $memo, $checkNum, $currencyName, $exchangeRate, NetSuite_RecordRef $creditCard, $chargeIt, $ccNumber, $ccExpireDate, $ccName, $ccStreet, $ccZipCode, $ccApproved, $authCode, $ccAvsStreetMatch, $ccAvsZipMatch, $ccSecurityCode, $ccSecurityCodeMatch, $pnRefNum, NetSuite_RecordRef $creditCardProcessor, $debitCardIssueNo, $validFrom, $undepFunds, NetSuite_RecordRef $account, $total, $applied, $unApplied, NetSuite_RecordRef $class, NetSuite_RecordRef $department, NetSuite_RecordRef $location, $status, NetSuite_CustomerPaymentApplyList $applyList, NetSuite_CustomerPaymentCreditList $creditList, NetSuite_CustomerPaymentDepositList $depositList, $internalId, $externalId) {
        $this->createdDate = $createdDate;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->arAcct = $arAcct;
        $this->customer = $customer;
        $this->balance = $balance;
        $this->pending = $pending;
        $this->payment = $payment;
        $this->autoApply = $autoApply;
        $this->tranDate = $tranDate;
        $this->postingPeriod = $postingPeriod;
        $this->paymentMethod = $paymentMethod;
        $this->memo = $memo;
        $this->checkNum = $checkNum;
        $this->currencyName = $currencyName;
        $this->exchangeRate = $exchangeRate;
        $this->creditCard = $creditCard;
        $this->chargeIt = $chargeIt;
        $this->ccNumber = $ccNumber;
        $this->ccExpireDate = $ccExpireDate;
        $this->ccName = $ccName;
        $this->ccStreet = $ccStreet;
        $this->ccZipCode = $ccZipCode;
        $this->ccApproved = $ccApproved;
        $this->authCode = $authCode;
        $this->ccAvsStreetMatch = $ccAvsStreetMatch;
        $this->ccAvsZipMatch = $ccAvsZipMatch;
        $this->ccSecurityCode = $ccSecurityCode;
        $this->ccSecurityCodeMatch = $ccSecurityCodeMatch;
        $this->pnRefNum = $pnRefNum;
        $this->creditCardProcessor = $creditCardProcessor;
        $this->debitCardIssueNo = $debitCardIssueNo;
        $this->validFrom = $validFrom;
        $this->undepFunds = $undepFunds;
        $this->account = $account;
        $this->total = $total;
        $this->applied = $applied;
        $this->unApplied = $unApplied;
        $this->class = $class;
        $this->department = $department;
        $this->location = $location;
        $this->status = $status;
        $this->applyList = $applyList;
        $this->creditList = $creditList;
        $this->depositList = $depositList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>