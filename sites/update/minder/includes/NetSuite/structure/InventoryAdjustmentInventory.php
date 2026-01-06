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
 * InventoryAdjustmentInventory map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_InventoryAdjustmentInventory {
    public $item; //NetSuite_RecordRef
    public $description;
    public $location; //NetSuite_RecordRef
    public $units; //NetSuite_RecordRef
    public $qtyOnHand;
    public $currentValue;
    public $adjustQtyBy;
    public $binNumbers;
    public $serialNumbers;
    public $newQty;
    public $unitCost;
    public $memo;
    public $expirationDate;

    public function __construct(  NetSuite_RecordRef $item, $description, NetSuite_RecordRef $location, NetSuite_RecordRef $units, $qtyOnHand, $currentValue, $adjustQtyBy, $binNumbers, $serialNumbers, $newQty, $unitCost, $memo, $expirationDate) {
        $this->item = $item;
        $this->description = $description;
        $this->location = $location;
        $this->units = $units;
        $this->qtyOnHand = $qtyOnHand;
        $this->currentValue = $currentValue;
        $this->adjustQtyBy = $adjustQtyBy;
        $this->binNumbers = $binNumbers;
        $this->serialNumbers = $serialNumbers;
        $this->newQty = $newQty;
        $this->unitCost = $unitCost;
        $this->memo = $memo;
        $this->expirationDate = $expirationDate;
    }
}?>