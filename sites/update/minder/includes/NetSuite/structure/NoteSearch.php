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
 * NoteSearch map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_NoteSearch {
    public $basic; //NetSuite_NoteSearchBasic
    public $authorJoin; //NetSuite_EmployeeSearchBasic
    public $callJoin; //NetSuite_PhoneCallSearchBasic
    public $caseJoin; //NetSuite_SupportCaseSearchBasic
    public $contactJoin; //NetSuite_ContactSearchBasic
    public $customerJoin; //NetSuite_CustomerSearchBasic
    public $employeeJoin; //NetSuite_EmployeeSearchBasic
    public $entityJoin; //NetSuite_EntitySearchBasic
    public $eventJoin; //NetSuite_CalendarEventSearchBasic
    public $opportunityJoin; //NetSuite_OpportunitySearchBasic
    public $partnerJoin; //NetSuite_PartnerSearchBasic
    public $solutionJoin; //NetSuite_SolutionSearchBasic
    public $taskJoin; //NetSuite_TaskSearchBasic
    public $transactionJoin; //NetSuite_TransactionSearchBasic
    public $vendorJoin; //NetSuite_VendorSearchBasic

    public function __construct(  NetSuite_NoteSearchBasic $basic, NetSuite_EmployeeSearchBasic $authorJoin, NetSuite_PhoneCallSearchBasic $callJoin, NetSuite_SupportCaseSearchBasic $caseJoin, NetSuite_ContactSearchBasic $contactJoin, NetSuite_CustomerSearchBasic $customerJoin, NetSuite_EmployeeSearchBasic $employeeJoin, NetSuite_EntitySearchBasic $entityJoin, NetSuite_CalendarEventSearchBasic $eventJoin, NetSuite_OpportunitySearchBasic $opportunityJoin, NetSuite_PartnerSearchBasic $partnerJoin, NetSuite_SolutionSearchBasic $solutionJoin, NetSuite_TaskSearchBasic $taskJoin, NetSuite_TransactionSearchBasic $transactionJoin, NetSuite_VendorSearchBasic $vendorJoin) {
        $this->basic = $basic;
        $this->authorJoin = $authorJoin;
        $this->callJoin = $callJoin;
        $this->caseJoin = $caseJoin;
        $this->contactJoin = $contactJoin;
        $this->customerJoin = $customerJoin;
        $this->employeeJoin = $employeeJoin;
        $this->entityJoin = $entityJoin;
        $this->eventJoin = $eventJoin;
        $this->opportunityJoin = $opportunityJoin;
        $this->partnerJoin = $partnerJoin;
        $this->solutionJoin = $solutionJoin;
        $this->taskJoin = $taskJoin;
        $this->transactionJoin = $transactionJoin;
        $this->vendorJoin = $vendorJoin;
    }
}?>