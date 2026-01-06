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
 * CustomRecordSearch map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_CustomRecordSearch {
    public $basic; //NetSuite_CustomRecordSearchBasic
    public $messagesJoin; //NetSuite_MessageSearchBasic
    public $userNotesJoin; //NetSuite_NoteSearchBasic

    public function __construct(  NetSuite_CustomRecordSearchBasic $basic, NetSuite_MessageSearchBasic $messagesJoin, NetSuite_NoteSearchBasic $userNotesJoin) {
        $this->basic = $basic;
        $this->messagesJoin = $messagesJoin;
        $this->userNotesJoin = $userNotesJoin;
    }
}?>