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
 * InventoryItemLocations map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_InventoryItemLocations {
    public $location;
    public $quantityOnHand;
    public $onHandValueMli;
    public $reorderPoint;
    public $preferredStockLevel;
    public $quantityOnOrder;
    public $quantityCommitted;
    public $quantityAvailable;
    public $quantityBackOrdered;
    public $locationId; //NetSuite_RecordRef

    public function __construct(  $location, $quantityOnHand, $onHandValueMli, $reorderPoint, $preferredStockLevel, $quantityOnOrder, $quantityCommitted, $quantityAvailable, $quantityBackOrdered, NetSuite_RecordRef $locationId) {
        $this->location = $location;
        $this->quantityOnHand = $quantityOnHand;
        $this->onHandValueMli = $onHandValueMli;
        $this->reorderPoint = $reorderPoint;
        $this->preferredStockLevel = $preferredStockLevel;
        $this->quantityOnOrder = $quantityOnOrder;
        $this->quantityCommitted = $quantityCommitted;
        $this->quantityAvailable = $quantityAvailable;
        $this->quantityBackOrdered = $quantityBackOrdered;
        $this->locationId = $locationId;
    }
}?>