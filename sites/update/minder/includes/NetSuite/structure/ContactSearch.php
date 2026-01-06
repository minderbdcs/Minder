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
 * ContactSearch map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_ContactSearch {
    public $basic; //NetSuite_ContactSearchBasic
    public $callJoin; //NetSuite_PhoneCallSearchBasic
    public $caseJoin; //NetSuite_SupportCaseSearchBasic
    public $customerJoin; //NetSuite_CustomerSearchBasic
    public $customerPrimaryJoin; //NetSuite_CustomerSearchBasic
    public $eventJoin; //NetSuite_CalendarEventSearchBasic
    public $messagesJoin; //NetSuite_MessageSearchBasic
    public $messagesFromJoin; //NetSuite_MessageSearchBasic
    public $messagesToJoin; //NetSuite_MessageSearchBasic
    public $opportunityJoin; //NetSuite_OpportunitySearchBasic
    public $partnerJoin; //NetSuite_PartnerSearchBasic
    public $partnerPrimaryJoin; //NetSuite_PartnerSearchBasic
    public $taskJoin; //NetSuite_TaskSearchBasic
    public $transactionJoin; //NetSuite_TransactionSearchBasic
    public $userNotesJoin; //NetSuite_NoteSearchBasic
    public $vendorJoin; //NetSuite_VendorSearchBasic
    public $vendorPrimaryJoin; //NetSuite_VendorSearchBasic

    public function __construct(  NetSuite_ContactSearchBasic $basic, NetSuite_PhoneCallSearchBasic $callJoin, NetSuite_SupportCaseSearchBasic $caseJoin, NetSuite_CustomerSearchBasic $customerJoin, NetSuite_CustomerSearchBasic $customerPrimaryJoin, NetSuite_CalendarEventSearchBasic $eventJoin, NetSuite_MessageSearchBasic $messagesJoin, NetSuite_MessageSearchBasic $messagesFromJoin, NetSuite_MessageSearchBasic $messagesToJoin, NetSuite_OpportunitySearchBasic $opportunityJoin, NetSuite_PartnerSearchBasic $partnerJoin, NetSuite_PartnerSearchBasic $partnerPrimaryJoin, NetSuite_TaskSearchBasic $taskJoin, NetSuite_TransactionSearchBasic $transactionJoin, NetSuite_NoteSearchBasic $userNotesJoin, NetSuite_VendorSearchBasic $vendorJoin, NetSuite_VendorSearchBasic $vendorPrimaryJoin) {
        $this->basic = $basic;
        $this->callJoin = $callJoin;
        $this->caseJoin = $caseJoin;
        $this->customerJoin = $customerJoin;
        $this->customerPrimaryJoin = $customerPrimaryJoin;
        $this->eventJoin = $eventJoin;
        $this->messagesJoin = $messagesJoin;
        $this->messagesFromJoin = $messagesFromJoin;
        $this->messagesToJoin = $messagesToJoin;
        $this->opportunityJoin = $opportunityJoin;
        $this->partnerJoin = $partnerJoin;
        $this->partnerPrimaryJoin = $partnerPrimaryJoin;
        $this->taskJoin = $taskJoin;
        $this->transactionJoin = $transactionJoin;
        $this->userNotesJoin = $userNotesJoin;
        $this->vendorJoin = $vendorJoin;
        $this->vendorPrimaryJoin = $vendorPrimaryJoin;
    }
}?>