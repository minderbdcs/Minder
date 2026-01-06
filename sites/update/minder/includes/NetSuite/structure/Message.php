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
 * Message map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_Message {
    public $author; //NetSuite_RecordRef
    public $recipient; //NetSuite_RecordRef
    public $cc;
    public $bcc;
    public $messageDate;
    public $subject;
    public $message;
    public $emailed;
    public $activity; //NetSuite_RecordRef
    public $incoming;
    public $lastModifiedDate;
    public $transaction; //NetSuite_RecordRef
    public $mediaItemList; //NetSuite_MessageMediaItemList
    public $internalId;
    public $externalId;

    public function __construct(  NetSuite_RecordRef $author, NetSuite_RecordRef $recipient, $cc, $bcc, $messageDate, $subject, $message, $emailed, NetSuite_RecordRef $activity, $incoming, $lastModifiedDate, NetSuite_RecordRef $transaction, NetSuite_MessageMediaItemList $mediaItemList, $internalId, $externalId) {
        $this->author = $author;
        $this->recipient = $recipient;
        $this->cc = $cc;
        $this->bcc = $bcc;
        $this->messageDate = $messageDate;
        $this->subject = $subject;
        $this->message = $message;
        $this->emailed = $emailed;
        $this->activity = $activity;
        $this->incoming = $incoming;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->transaction = $transaction;
        $this->mediaItemList = $mediaItemList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>