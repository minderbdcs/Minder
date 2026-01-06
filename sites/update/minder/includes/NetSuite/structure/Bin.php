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
 * Bin map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_Bin {
    public $binNumber;
    public $location; //NetSuite_RecordRef
    public $memo;
    public $isInactive;
    public $internalId;
    public $externalId;

    public function __construct(  $binNumber, NetSuite_RecordRef $location, $memo, $isInactive, $internalId, $externalId) {
        $this->binNumber = $binNumber;
        $this->location = $location;
        $this->memo = $memo;
        $this->isInactive = $isInactive;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>