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
 * OpportunitySearch map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_OpportunitySearch {
    public $basic; //NetSuite_OpportunitySearchBasic
    public $callJoin; //NetSuite_PhoneCallSearchBasic
    public $customerJoin; //NetSuite_CustomerSearchBasic
    public $decisionMakerJoin; //NetSuite_ContactSearchBasic
    public $eventJoin; //NetSuite_CalendarEventSearchBasic
    public $messagesJoin; //NetSuite_MessageSearchBasic
    public $partnerJoin; //NetSuite_PartnerSearchBasic
    public $salesRepJoin; //NetSuite_EmployeeSearchBasic
    public $taskJoin; //NetSuite_TaskSearchBasic
    public $userNotesJoin; //NetSuite_NoteSearchBasic

    public function __construct(  NetSuite_OpportunitySearchBasic $basic, NetSuite_PhoneCallSearchBasic $callJoin, NetSuite_CustomerSearchBasic $customerJoin, NetSuite_ContactSearchBasic $decisionMakerJoin, NetSuite_CalendarEventSearchBasic $eventJoin, NetSuite_MessageSearchBasic $messagesJoin, NetSuite_PartnerSearchBasic $partnerJoin, NetSuite_EmployeeSearchBasic $salesRepJoin, NetSuite_TaskSearchBasic $taskJoin, NetSuite_NoteSearchBasic $userNotesJoin) {
        $this->basic = $basic;
        $this->callJoin = $callJoin;
        $this->customerJoin = $customerJoin;
        $this->decisionMakerJoin = $decisionMakerJoin;
        $this->eventJoin = $eventJoin;
        $this->messagesJoin = $messagesJoin;
        $this->partnerJoin = $partnerJoin;
        $this->salesRepJoin = $salesRepJoin;
        $this->taskJoin = $taskJoin;
        $this->userNotesJoin = $userNotesJoin;
    }
}?>