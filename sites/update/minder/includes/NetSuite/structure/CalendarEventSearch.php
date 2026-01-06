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
 * CalendarEventSearch map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_CalendarEventSearch {
    public $basic; //NetSuite_CalendarEventSearchBasic
    public $attendeeJoin; //NetSuite_EntitySearchBasic
    public $attendeeContactJoin; //NetSuite_ContactSearchBasic
    public $attendeeCustomerJoin; //NetSuite_CustomerSearchBasic
    public $caseJoin; //NetSuite_SupportCaseSearchBasic
    public $opportunityJoin; //NetSuite_OpportunitySearchBasic
    public $transactionJoin; //NetSuite_TransactionSearchBasic
    public $userNotesJoin; //NetSuite_NoteSearchBasic

    public function __construct(  NetSuite_CalendarEventSearchBasic $basic, NetSuite_EntitySearchBasic $attendeeJoin, NetSuite_ContactSearchBasic $attendeeContactJoin, NetSuite_CustomerSearchBasic $attendeeCustomerJoin, NetSuite_SupportCaseSearchBasic $caseJoin, NetSuite_OpportunitySearchBasic $opportunityJoin, NetSuite_TransactionSearchBasic $transactionJoin, NetSuite_NoteSearchBasic $userNotesJoin) {
        $this->basic = $basic;
        $this->attendeeJoin = $attendeeJoin;
        $this->attendeeContactJoin = $attendeeContactJoin;
        $this->attendeeCustomerJoin = $attendeeCustomerJoin;
        $this->caseJoin = $caseJoin;
        $this->opportunityJoin = $opportunityJoin;
        $this->transactionJoin = $transactionJoin;
        $this->userNotesJoin = $userNotesJoin;
    }
}?>