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
 * CustomerSearch map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_CustomerSearch {
    public $basic; //NetSuite_CustomerSearchBasic
    public $callJoin; //NetSuite_PhoneCallSearchBasic
    public $caseJoin; //NetSuite_SupportCaseSearchBasic
    public $contactJoin; //NetSuite_ContactSearchBasic
    public $contactPrimaryJoin; //NetSuite_ContactSearchBasic
    public $eventJoin; //NetSuite_CalendarEventSearchBasic
    public $messagesJoin; //NetSuite_MessageSearchBasic
    public $messagesFromJoin; //NetSuite_MessageSearchBasic
    public $messagesToJoin; //NetSuite_MessageSearchBasic
    public $opportunityJoin; //NetSuite_OpportunitySearchBasic
    public $parentCustomerJoin; //NetSuite_CustomerSearchBasic
    public $partnerJoin; //NetSuite_PartnerSearchBasic
    public $salesRepJoin; //NetSuite_EmployeeSearchBasic
    public $subCustomerJoin; //NetSuite_CustomerSearchBasic
    public $taskJoin; //NetSuite_TaskSearchBasic
    public $transactionJoin; //NetSuite_TransactionSearchBasic
    public $userNotesJoin; //NetSuite_NoteSearchBasic
    public $webSiteCategoryJoin; //NetSuite_SiteCategorySearchBasic
    public $webSiteItemJoin; //NetSuite_ItemSearchBasic

    public function __construct(  NetSuite_CustomerSearchBasic $basic, NetSuite_PhoneCallSearchBasic $callJoin, NetSuite_SupportCaseSearchBasic $caseJoin, NetSuite_ContactSearchBasic $contactJoin, NetSuite_ContactSearchBasic $contactPrimaryJoin, NetSuite_CalendarEventSearchBasic $eventJoin, NetSuite_MessageSearchBasic $messagesJoin, NetSuite_MessageSearchBasic $messagesFromJoin, NetSuite_MessageSearchBasic $messagesToJoin, NetSuite_OpportunitySearchBasic $opportunityJoin, NetSuite_CustomerSearchBasic $parentCustomerJoin, NetSuite_PartnerSearchBasic $partnerJoin, NetSuite_EmployeeSearchBasic $salesRepJoin, NetSuite_CustomerSearchBasic $subCustomerJoin, NetSuite_TaskSearchBasic $taskJoin, NetSuite_TransactionSearchBasic $transactionJoin, NetSuite_NoteSearchBasic $userNotesJoin, NetSuite_SiteCategorySearchBasic $webSiteCategoryJoin, NetSuite_ItemSearchBasic $webSiteItemJoin) {
        $this->basic = $basic;
        $this->callJoin = $callJoin;
        $this->caseJoin = $caseJoin;
        $this->contactJoin = $contactJoin;
        $this->contactPrimaryJoin = $contactPrimaryJoin;
        $this->eventJoin = $eventJoin;
        $this->messagesJoin = $messagesJoin;
        $this->messagesFromJoin = $messagesFromJoin;
        $this->messagesToJoin = $messagesToJoin;
        $this->opportunityJoin = $opportunityJoin;
        $this->parentCustomerJoin = $parentCustomerJoin;
        $this->partnerJoin = $partnerJoin;
        $this->salesRepJoin = $salesRepJoin;
        $this->subCustomerJoin = $subCustomerJoin;
        $this->taskJoin = $taskJoin;
        $this->transactionJoin = $transactionJoin;
        $this->userNotesJoin = $userNotesJoin;
        $this->webSiteCategoryJoin = $webSiteCategoryJoin;
        $this->webSiteItemJoin = $webSiteItemJoin;
    }
}?>