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
 * BinSearchBasic map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_BinSearchBasic {
    public $internalId; //NetSuite_SearchMultiSelectField
    public $binNumber; //NetSuite_SearchStringField
    public $location; //NetSuite_SearchMultiSelectField
    public $memo; //NetSuite_SearchStringField
    public $isInactive; //NetSuite_SearchBooleanField

    public function __construct(  NetSuite_SearchMultiSelectField $internalId, NetSuite_SearchStringField $binNumber, NetSuite_SearchMultiSelectField $location, NetSuite_SearchStringField $memo, NetSuite_SearchBooleanField $isInactive) {
        $this->internalId = $internalId;
        $this->binNumber = $binNumber;
        $this->location = $location;
        $this->memo = $memo;
        $this->isInactive = $isInactive;
    }
}?>