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
 * MessageSearch map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_MessageSearch {
    public $basic; //NetSuite_MessageSearchBasic
    public $authorJoin; //NetSuite_EntitySearchBasic
    public $caseJoin; //NetSuite_SupportCaseSearchBasic
    public $contactJoin; //NetSuite_ContactSearchBasic
    public $customerJoin; //NetSuite_CustomerSearchBasic
    public $employeeJoin; //NetSuite_EmployeeSearchBasic
    public $entityJoin; //NetSuite_EntitySearchBasic
    public $opportunityJoin; //NetSuite_OpportunitySearchBasic
    public $partnerJoin; //NetSuite_PartnerSearchBasic
    public $recipientJoin; //NetSuite_EntitySearchBasic
    public $solutionJoin; //NetSuite_SolutionSearchBasic
    public $transactionJoin; //NetSuite_TransactionSearchBasic
    public $vendorJoin; //NetSuite_VendorSearchBasic

    public function __construct(  NetSuite_MessageSearchBasic $basic, NetSuite_EntitySearchBasic $authorJoin, NetSuite_SupportCaseSearchBasic $caseJoin, NetSuite_ContactSearchBasic $contactJoin, NetSuite_CustomerSearchBasic $customerJoin, NetSuite_EmployeeSearchBasic $employeeJoin, NetSuite_EntitySearchBasic $entityJoin, NetSuite_OpportunitySearchBasic $opportunityJoin, NetSuite_PartnerSearchBasic $partnerJoin, NetSuite_EntitySearchBasic $recipientJoin, NetSuite_SolutionSearchBasic $solutionJoin, NetSuite_TransactionSearchBasic $transactionJoin, NetSuite_VendorSearchBasic $vendorJoin) {
        $this->basic = $basic;
        $this->authorJoin = $authorJoin;
        $this->caseJoin = $caseJoin;
        $this->contactJoin = $contactJoin;
        $this->customerJoin = $customerJoin;
        $this->employeeJoin = $employeeJoin;
        $this->entityJoin = $entityJoin;
        $this->opportunityJoin = $opportunityJoin;
        $this->partnerJoin = $partnerJoin;
        $this->recipientJoin = $recipientJoin;
        $this->solutionJoin = $solutionJoin;
        $this->transactionJoin = $transactionJoin;
        $this->vendorJoin = $vendorJoin;
    }
}?>