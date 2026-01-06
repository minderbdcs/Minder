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
 * TimeBillSearch map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_TimeBillSearch {
    public $basic; //NetSuite_TimeBillSearchBasic
    public $callJoin; //NetSuite_PhoneCallSearchBasic
    public $caseJoin; //NetSuite_SupportCaseSearchBasic
    public $customerJoin; //NetSuite_CustomerSearchBasic
    public $employeeJoin; //NetSuite_EmployeeSearchBasic
    public $eventJoin; //NetSuite_CalendarEventSearchBasic
    public $itemJoin; //NetSuite_ItemSearchBasic
    public $taskJoin; //NetSuite_TaskSearchBasic

    public function __construct(  NetSuite_TimeBillSearchBasic $basic, NetSuite_PhoneCallSearchBasic $callJoin, NetSuite_SupportCaseSearchBasic $caseJoin, NetSuite_CustomerSearchBasic $customerJoin, NetSuite_EmployeeSearchBasic $employeeJoin, NetSuite_CalendarEventSearchBasic $eventJoin, NetSuite_ItemSearchBasic $itemJoin, NetSuite_TaskSearchBasic $taskJoin) {
        $this->basic = $basic;
        $this->callJoin = $callJoin;
        $this->caseJoin = $caseJoin;
        $this->customerJoin = $customerJoin;
        $this->employeeJoin = $employeeJoin;
        $this->eventJoin = $eventJoin;
        $this->itemJoin = $itemJoin;
        $this->taskJoin = $taskJoin;
    }
}?>