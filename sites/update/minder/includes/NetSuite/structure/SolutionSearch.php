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
 * SolutionSearch map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_SolutionSearch {
    public $basic; //NetSuite_SolutionSearchBasic
    public $messagesJoin; //NetSuite_MessageSearchBasic
    public $userNotesJoin; //NetSuite_NoteSearchBasic
    public $caseJoin; //NetSuite_SupportCaseSearchBasic

    public function __construct(  NetSuite_SolutionSearchBasic $basic, NetSuite_MessageSearchBasic $messagesJoin, NetSuite_NoteSearchBasic $userNotesJoin, NetSuite_SupportCaseSearchBasic $caseJoin) {
        $this->basic = $basic;
        $this->messagesJoin = $messagesJoin;
        $this->userNotesJoin = $userNotesJoin;
        $this->caseJoin = $caseJoin;
    }
}?>