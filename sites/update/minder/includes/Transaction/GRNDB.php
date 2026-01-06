<?php
/**
 * Transaction_GRNDB
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
 * Transaction_GRNDB
 *
 * This class implements the GRNDB transaction that is used to
 * record how receipt delivered to warehouse
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_GRNDB Extends Transaction
{
    /**
     * The ID of the carrier who delivered the goods
     *
     * @var string
     */
    public $carrierId;

    /**
     * The ID of the hire crate owner
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
     * The code for the crate type of hire crate
     *
     * @var string
     */
    public $crateTypeId;

    /**
     * The carrier's consignment note number
     *
     * @var string
     */
    public $conNoteNo;

    /**
     * The signed for quantity of packages delivered by carrier
     *
     * @var string
     */
    public $conNoteQty;

    /**
     * Y if there was a shipping container, otherwise N
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
     * If empty system generates the order number
     *
     * @var string
     */
    public $orderNo;

    /**
     * Must be empty if empty Order No
     *
     * @var string
     */
    public $orderLineNo;

    /**
     * The ID for the hire pallet owner. N = None
     *
     * @var string
     */
    public $palletOwnerId;

    /**
     * The number of hire pallets delivered
     *
     * @var string
     */
    public $palletQty;

    /**
     * The date the items were shipped as shown on shipping documentation
     *
     * @var string
     */
    public $shipDate;

    /**
     * The registration number of the vehicle that delivered the goods
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
        $this->transClass          = 'B';
        $this->carrierId           = '';
        $this->crateOwnerId        = '';
        $this->crateQty            = '';
        $this->crateTypeId         = '';
        $this->conNoteNo           = '';
        $this->conNoteQty          = '1';
        $this->hasContainer        = 'N';
        $this->deliveryTypeId      = '';
        $this->orderNo             = '';
        $this->orderLineNo         = '';
        $this->palletOwnerId       = 'N';
        $this->palletQty           = '';
        $this->shipDate            = '';
        $this->vehicleRegistration = '';
    }

    /**
     * Returns the object id for inserting into the database
     *
     * The object id for the GRNDB transaction is the carriers consignment note
     * number + '|' +  date the inventory shipped
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
     * The location for the GRNDB transaction is name of the Carrier who
     * delivered the goods
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->carrierId;
    }

    /**
    * method for retrieve data from property
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
     * The reference is combination of deliveryType + '|' + orderNo + '|' +
     * orderLineNo + '|' + hasContainer + '|' + palletOwner + '|' + palletQty +
     * '|' + crateOwnerId + '|' + crateType + '|' + crateQty
     *
     * @return string
     */
    public function getReference()
    {
        return $this->deliveryTypeId .  '|' .
               $this->orderNo .  '|' .
               $this->orderLineNo .  '|' .
               $this->hasContainer .  '|'.
               $this->palletOwnerId .  '|' .
               $this->palletQty .  '|' .
               $this->crateOwnerId .  '|' .
               $this->crateTypeId .  '|' .
               $this->crateQty;
         /* needs to add the other1 and wh_id here */
    }

    /**
     * Returns the quantity for inserting into the database
     *
     * The quantity of packages delivered by Carrier
     *
     * @return string
     */
    public function getQuantity()
    {
        return $this->conNoteQty;
    }

    /**
     * @param string $transactionResponse
     * @return Transaction_Response_GRNDB
     */
    public function parseResponse($transactionResponse)
    {
        return new Transaction_Response_GRNDB($transactionResponse);
    }


}
