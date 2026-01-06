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
 * Note map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_Note {
    public $title;
    public $noteType; //NetSuite_RecordRef
    public $direction;
    public $noteDate;
    public $note;
    public $activity; //NetSuite_RecordRef
    public $author; //NetSuite_RecordRef
    public $entity; //NetSuite_RecordRef
    public $item; //NetSuite_RecordRef
    public $lastModifiedDate;
    public $record; //NetSuite_RecordRef
    public $recordType; //NetSuite_RecordRef
    public $topic; //NetSuite_RecordRef
    public $transaction; //NetSuite_RecordRef
    public $internalId;
    public $externalId;

    public function __construct(  $title, NetSuite_RecordRef $noteType, $direction, $noteDate, $note, NetSuite_RecordRef $activity, NetSuite_RecordRef $author, NetSuite_RecordRef $entity, NetSuite_RecordRef $item, $lastModifiedDate, NetSuite_RecordRef $record, NetSuite_RecordRef $recordType, NetSuite_RecordRef $topic, NetSuite_RecordRef $transaction, $internalId, $externalId) {
        $this->title = $title;
        $this->noteType = $noteType;
        $this->direction = $direction;
        $this->noteDate = $noteDate;
        $this->note = $note;
        $this->activity = $activity;
        $this->author = $author;
        $this->entity = $entity;
        $this->item = $item;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->record = $record;
        $this->recordType = $recordType;
        $this->topic = $topic;
        $this->transaction = $transaction;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>