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
 * SupportCaseSearch map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_SupportCaseSearch {
    public $basic; //NetSuite_SupportCaseSearchBasic
    public $contactJoin; //NetSuite_ContactSearchBasic
    public $customerJoin; //NetSuite_CustomerSearchBasic
    public $employeeJoin; //NetSuite_EmployeeSearchBasic
    public $itemJoin; //NetSuite_ItemSearchBasic
    public $messagesJoin; //NetSuite_MessageSearchBasic
    public $userNotesJoin; //NetSuite_NoteSearchBasic

    public function __construct(  NetSuite_SupportCaseSearchBasic $basic, NetSuite_ContactSearchBasic $contactJoin, NetSuite_CustomerSearchBasic $customerJoin, NetSuite_EmployeeSearchBasic $employeeJoin, NetSuite_ItemSearchBasic $itemJoin, NetSuite_MessageSearchBasic $messagesJoin, NetSuite_NoteSearchBasic $userNotesJoin) {
        $this->basic = $basic;
        $this->contactJoin = $contactJoin;
        $this->customerJoin = $customerJoin;
        $this->employeeJoin = $employeeJoin;
        $this->itemJoin = $itemJoin;
        $this->messagesJoin = $messagesJoin;
        $this->userNotesJoin = $userNotesJoin;
    }
}?>