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
 * TaskSearch map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_TaskSearch {
    public $basic; //NetSuite_TaskSearchBasic
    public $companyCustomerJoin; //NetSuite_CustomerSearchBasic
    public $contactJoin; //NetSuite_ContactSearchBasic
    public $employeeJoin; //NetSuite_EmployeeSearchBasic
    public $opportunityJoin; //NetSuite_OpportunitySearchBasic
    public $transactionJoin; //NetSuite_TransactionSearchBasic
    public $userNotesJoin; //NetSuite_NoteSearchBasic

    public function __construct(  NetSuite_TaskSearchBasic $basic, NetSuite_CustomerSearchBasic $companyCustomerJoin, NetSuite_ContactSearchBasic $contactJoin, NetSuite_EmployeeSearchBasic $employeeJoin, NetSuite_OpportunitySearchBasic $opportunityJoin, NetSuite_TransactionSearchBasic $transactionJoin, NetSuite_NoteSearchBasic $userNotesJoin) {
        $this->basic = $basic;
        $this->companyCustomerJoin = $companyCustomerJoin;
        $this->contactJoin = $contactJoin;
        $this->employeeJoin = $employeeJoin;
        $this->opportunityJoin = $opportunityJoin;
        $this->transactionJoin = $transactionJoin;
        $this->userNotesJoin = $userNotesJoin;
    }
}?>