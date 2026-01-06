<?php
/**
 * Transaction_GRNDL
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
 * Transaction_GRNDL
 *
 * This class implements the GRNDL transaction that is used to record a comment
 * about goods delivered to the warehouse.
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_GRNDL Extends Transaction
{
    /**
     * The Goods Receipt No. previously created for this Delivery
     *
     * @var string
     */
    public $grnNo;

    /**
     * The ID of the inventory owner
     *
     * @var string
     */
    public $ownerId;

    /**
     * The ID of the inventory supplier
     *
     * @var string
     */
    public $supplierId;

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
     * The Order No. If blank, system generates
     *
     * @var string
     */
    public $orderNo;

    /**
     * Delivery shipping container no
     *
     * @var string
     */
    public $containerNo;

    /**
     * The ID for the type of delivery container
     *
     * @var string
     */
    public $containerTypeId;

    /**
     * GRN Label Printer ID. Look up SYS_EQUIP Table WHERE
     * DEVICE_TYPE = PR
     *
     * @var string
     */
    public $printerId;

    /**
     * The required quantity of GRN labels to be printed
     *
     * @var string
     */
    public $grnLabelQty;

    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode       = 'GRND';
        $this->transClass      = 'L';
        $this->grnNo           = '';
        $this->ownerId         = '';
        $this->supplierId      = '';
        $this->deliveryTypeId  = '';
        $this->orderNo         = '';
        $this->containerNo     = '';
        $this->containerTypeId = '';
        $this->printerId       = '';
        $this->grnLabelQty     = '';
    }

    /**
     * Returns the object id for inserting into the database
     *
     * The object id for the GRNDL transaction is the grnNo + |
     *
     * @return string
     */
    public function getObjectId()
    {
        return $this->grnNo . '|';
    }

    /**
     * Returns the location for inserting into the database
     *
     * The location for the GRNDL transaction is the ownerId.
     * Max lenght = 10 chars
     *
     * @return string
     */
    public function getLocation()
    {
        return substr($this->ownerId, 0, 10);
    }

    /**
     * Returns the sublocation for inserting into the database
     *
     * The sublocation is the supplierId
     *
     * @return string
     */
    public function getSubLocation()
    {
        return $this->supplierId;
    }

    /**
     * Returns the reference for inserting into the database
     *
     * The reference is deliveryType + | + orderNo + | + containerNo+ | +
     * containerType + | + printerId
     *
     * @return string
     */
    public function getReference()
    {
        return $this->deliveryTypeId .  '|' .
               $this->orderNo .  '|' .
               $this->containerNo .  '|' .
               $this->containerTypeId .  '|' .
               $this->printerId;
    }

    /**
     * Returns the quantity for inserting into the database
     *
     * The quantity is required quantity of GRN labels to be printed
     *
     * @return string
     */
    public function getQuantity()
    {
        return $this->grnLabelQty;
    }
}
