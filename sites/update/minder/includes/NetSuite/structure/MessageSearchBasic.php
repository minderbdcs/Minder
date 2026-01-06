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
 * MessageSearchBasic map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_MessageSearchBasic {
    public $author; //NetSuite_SearchMultiSelectField
    public $authorEmail; //NetSuite_SearchStringField
    public $bcc; //NetSuite_SearchStringField
    public $cc; //NetSuite_SearchStringField
    public $externalId; //NetSuite_SearchMultiSelectField
    public $hasAttachment; //NetSuite_SearchBooleanField
    public $internalId; //NetSuite_SearchMultiSelectField
    public $internalOnly; //NetSuite_SearchBooleanField
    public $message; //NetSuite_SearchStringField
    public $messageDate; //NetSuite_SearchDateField
    public $messageType; //NetSuite_SearchEnumMultiSelectField
    public $recipient; //NetSuite_SearchMultiSelectField
    public $recipientEmail; //NetSuite_SearchStringField
    public $subject; //NetSuite_SearchStringField

    public function __construct(  NetSuite_SearchMultiSelectField $author, NetSuite_SearchStringField $authorEmail, NetSuite_SearchStringField $bcc, NetSuite_SearchStringField $cc, NetSuite_SearchMultiSelectField $externalId, NetSuite_SearchBooleanField $hasAttachment, NetSuite_SearchMultiSelectField $internalId, NetSuite_SearchBooleanField $internalOnly, NetSuite_SearchStringField $message, NetSuite_SearchDateField $messageDate, NetSuite_SearchEnumMultiSelectField $messageType, NetSuite_SearchMultiSelectField $recipient, NetSuite_SearchStringField $recipientEmail, NetSuite_SearchStringField $subject) {
        $this->author = $author;
        $this->authorEmail = $authorEmail;
        $this->bcc = $bcc;
        $this->cc = $cc;
        $this->externalId = $externalId;
        $this->hasAttachment = $hasAttachment;
        $this->internalId = $internalId;
        $this->internalOnly = $internalOnly;
        $this->message = $message;
        $this->messageDate = $messageDate;
        $this->messageType = $messageType;
        $this->recipient = $recipient;
        $this->recipientEmail = $recipientEmail;
        $this->subject = $subject;
    }
}?>