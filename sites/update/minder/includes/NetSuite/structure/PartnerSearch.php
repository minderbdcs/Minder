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
 * PartnerSearch map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_PartnerSearch {
    public $basic; //NetSuite_PartnerSearchBasic
    public $contactJoin; //NetSuite_ContactSearchBasic
    public $contactPrimaryJoin; //NetSuite_ContactSearchBasic
    public $messagesJoin; //NetSuite_MessageSearchBasic
    public $messagesFromJoin; //NetSuite_MessageSearchBasic
    public $messagesToJoin; //NetSuite_MessageSearchBasic
    public $transactionJoin; //NetSuite_TransactionSearchBasic
    public $userNotesJoin; //NetSuite_NoteSearchBasic

    public function __construct(  NetSuite_PartnerSearchBasic $basic, NetSuite_ContactSearchBasic $contactJoin, NetSuite_ContactSearchBasic $contactPrimaryJoin, NetSuite_MessageSearchBasic $messagesJoin, NetSuite_MessageSearchBasic $messagesFromJoin, NetSuite_MessageSearchBasic $messagesToJoin, NetSuite_TransactionSearchBasic $transactionJoin, NetSuite_NoteSearchBasic $userNotesJoin) {
        $this->basic = $basic;
        $this->contactJoin = $contactJoin;
        $this->contactPrimaryJoin = $contactPrimaryJoin;
        $this->messagesJoin = $messagesJoin;
        $this->messagesFromJoin = $messagesFromJoin;
        $this->messagesToJoin = $messagesToJoin;
        $this->transactionJoin = $transactionJoin;
        $this->userNotesJoin = $userNotesJoin;
    }
}?>