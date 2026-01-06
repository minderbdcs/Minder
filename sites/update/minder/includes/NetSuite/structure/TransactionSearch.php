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
 * TransactionSearch map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_TransactionSearch {
    public $basic; //NetSuite_TransactionSearchBasic
    public $classJoin; //NetSuite_ClassificationSearchBasic
    public $contactPrimaryJoin; //NetSuite_ContactSearchBasic
    public $createdFromJoin; //NetSuite_TransactionSearchBasic
    public $customerJoin; //NetSuite_CustomerSearchBasic
    public $departmentJoin; //NetSuite_DepartmentSearchBasic
    public $employeeJoin; //NetSuite_EmployeeSearchBasic
    public $itemJoin; //NetSuite_ItemSearchBasic
    public $locationJoin; //NetSuite_LocationSearchBasic
    public $messagesJoin; //NetSuite_MessageSearchBasic
    public $salesRepJoin; //NetSuite_EmployeeSearchBasic
    public $userNotesJoin; //NetSuite_NoteSearchBasic
    public $vendorJoin; //NetSuite_VendorSearchBasic

    public function __construct(  NetSuite_TransactionSearchBasic $basic, NetSuite_ClassificationSearchBasic $classJoin, NetSuite_ContactSearchBasic $contactPrimaryJoin, NetSuite_TransactionSearchBasic $createdFromJoin, NetSuite_CustomerSearchBasic $customerJoin, NetSuite_DepartmentSearchBasic $departmentJoin, NetSuite_EmployeeSearchBasic $employeeJoin, NetSuite_ItemSearchBasic $itemJoin, NetSuite_LocationSearchBasic $locationJoin, NetSuite_MessageSearchBasic $messagesJoin, NetSuite_EmployeeSearchBasic $salesRepJoin, NetSuite_NoteSearchBasic $userNotesJoin, NetSuite_VendorSearchBasic $vendorJoin) {
        $this->basic = $basic;
        $this->classJoin = $classJoin;
        $this->contactPrimaryJoin = $contactPrimaryJoin;
        $this->createdFromJoin = $createdFromJoin;
        $this->customerJoin = $customerJoin;
        $this->departmentJoin = $departmentJoin;
        $this->employeeJoin = $employeeJoin;
        $this->itemJoin = $itemJoin;
        $this->locationJoin = $locationJoin;
        $this->messagesJoin = $messagesJoin;
        $this->salesRepJoin = $salesRepJoin;
        $this->userNotesJoin = $userNotesJoin;
        $this->vendorJoin = $vendorJoin;
    }
}?>