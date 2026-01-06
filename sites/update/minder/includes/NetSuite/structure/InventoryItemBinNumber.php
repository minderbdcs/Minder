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
 * InventoryItemBinNumber map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_InventoryItemBinNumber {
    public $binNumber; //NetSuite_RecordRef
    public $onHand;
    public $location;
    public $preferredBin;

    public function __construct(  NetSuite_RecordRef $binNumber, $onHand, $location, $preferredBin) {
        $this->binNumber = $binNumber;
        $this->onHand = $onHand;
        $this->location = $location;
        $this->preferredBin = $preferredBin;
    }
}?>