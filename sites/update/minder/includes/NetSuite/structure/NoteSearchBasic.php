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
 * NoteSearchBasic map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_NoteSearchBasic {
    public $author; //NetSuite_SearchMultiSelectField
    public $direction; //NetSuite_SearchBooleanField
    public $externalId; //NetSuite_SearchMultiSelectField
    public $internalId; //NetSuite_SearchMultiSelectField
    public $note; //NetSuite_SearchStringField
    public $noteDate; //NetSuite_SearchDateField
    public $noteType; //NetSuite_SearchMultiSelectField
    public $title; //NetSuite_SearchStringField

    public function __construct(  NetSuite_SearchMultiSelectField $author, NetSuite_SearchBooleanField $direction, NetSuite_SearchMultiSelectField $externalId, NetSuite_SearchMultiSelectField $internalId, NetSuite_SearchStringField $note, NetSuite_SearchDateField $noteDate, NetSuite_SearchMultiSelectField $noteType, NetSuite_SearchStringField $title) {
        $this->author = $author;
        $this->direction = $direction;
        $this->externalId = $externalId;
        $this->internalId = $internalId;
        $this->note = $note;
        $this->noteDate = $noteDate;
        $this->noteType = $noteType;
        $this->title = $title;
    }
}?>