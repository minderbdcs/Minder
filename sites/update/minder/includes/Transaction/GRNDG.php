<?php
/**
 * Transaction_GRNDG
 *
 * PHP version 5.2.3
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 *
 */

/**
 * Include the definition of the Transaction base class
 */
require_once 'Transaction.php';

/**
 * Transaction_GRNDG
 *
 * This class implements the GRNDG transaction that is used to Record how
 * receipt delivered to warehouse
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_GRNDG Extends Transaction
{
    /**
     * The ID of the carrier who delivered the goods
     *
     * @var string
     */
    public $carrierId;

    /**
     * The ID of the crate owner for transaction
     *
     * @var string
     */
    public $crateOwnerId;

    /**
     * The number of hire crates
     *
     * @var string
     */
    public $crateQty;

    /**
     * The ID for the crate type
     *
     * @var string
     */
    public $crateTypeId;

    /**
     * Note for consignment
     *
     * @var string
     */
    public $conNoteNo;

    /**
     * Y if there was a container, otherwise N
     *
     * @var string
     */
    public $hasContainer;

    /**
     * The type of delivery. Currently this should be one of:
     *   LD = Load
     *   LP = Load Product
     *   PO = Purchase Order
     *   RA = Return Authorised
     *   TR = Transfer
     *   WO = Work Order
     *
     * @var string
     */
    public $deliveryTypeId;

    /**
     *
     * @var string
     */
    public $orderNo;

    /**
     * The code for the pallet owner
     *
     * @var string
     */
    public $palletOwnerId;

    /**
     * The number of pallets delivered
     *
     * @var string
     */
    public $palletQty;

    /**
     * The date the items were shipped
     *
     * @var string
     */
    public $shipDate;

    /**
     * The registration of the vehicle that delivered the goods
     *
     * @var string
     */
    public $vehicleRegistration;


    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode           = 'GRND';
        $this->transClass          = 'G';
        $this->carrierId           = '';
        $this->crateOwnerId        = '';
        $this->crateQty            = '';
        $this->crateTypeId         = '';
        $this->conNoteNo           = '';
        $this->hasContainer        = 'N';
        $this->deliveryTypeId      = '';
        $this->orderNo             = '';
        $this->palletOwnerId       = '';
        $this->palletQty           = '';
        $this->shipDate            = '';
        $this->vehicleRegistration = '';
    }

    /**
     * Returns the object id for inserting into the database
     *
     * The object id for the GRNDG transaction is the consignmentNoteNo + | +
     * shipDate
     *
     * @return string
     */
    public function getObjectId()
    {
        return $this->conNoteNo .  '|' .  $this->shipDate;
    }

    /**
     * Returns the location for inserting into the database
     *
     * The location for the GRNDG transaction name of the carrier who
     * delivered the goods
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->carrierId;
    }

    /**
     * Returns the sublocation for inserting into the database
     *
     * The sublocation is the registration of the vehicle that delivered the
     * goods
     *
     * @return string
     */
    public function getSubLocation()
    {
        return $this->vehicleRegistration;
    }

    /**
     * Returns the reference for inserting into the database
     *
     * The reference is deliveryTypeId + | + orderNo + | + | + hasContainer + |
     * + palletOwnerId + | + palletQty + | + crateOwner + | + crateTypeId + | +
     * crateQty
     *
     * @return string
     */
    public function getReference()
    {
        return $this->deliveryTypeId .  '|' .
               $this->orderNo .  '||' .
               $this->hasContainer .  '|' .
               $this->palletOwnerId .  '|' .
               $this->palletQty .  '|' .
               $this->crateOwnerId .  '|' .
               $this->crateTypeId .  '|' .
               $this->crateQty;
    }

    /**
     * Returns the quantity for inserting into the database
     *
     * The quantity is always the value 1
     *
     * @return string
     */
    public function getQuantity()
    {
        return 1;
    }
}
