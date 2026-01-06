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
 * ItemAvailability map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_ItemAvailability {
    public $item; //NetSuite_RecordRef
    public $lastQtyAvailableChange;
    public $locationId; //NetSuite_RecordRef
    public $quantityOnHand;
    public $onHandValueMli;
    public $reorderPoint;
    public $preferredStockLevel;
    public $quantityOnOrder;
    public $quantityCommitted;
    public $quantityBackOrdered;
    public $quantityAvailable;

    public function __construct(  NetSuite_RecordRef $item, $lastQtyAvailableChange, NetSuite_RecordRef $locationId, $quantityOnHand, $onHandValueMli, $reorderPoint, $preferredStockLevel, $quantityOnOrder, $quantityCommitted, $quantityBackOrdered, $quantityAvailable) {
        $this->item = $item;
        $this->lastQtyAvailableChange = $lastQtyAvailableChange;
        $this->locationId = $locationId;
        $this->quantityOnHand = $quantityOnHand;
        $this->onHandValueMli = $onHandValueMli;
        $this->reorderPoint = $reorderPoint;
        $this->preferredStockLevel = $preferredStockLevel;
        $this->quantityOnOrder = $quantityOnOrder;
        $this->quantityCommitted = $quantityCommitted;
        $this->quantityBackOrdered = $quantityBackOrdered;
        $this->quantityAvailable = $quantityAvailable;
    }
}?>