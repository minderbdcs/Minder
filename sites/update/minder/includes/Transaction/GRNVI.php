<?php
/**
 * Transaction_GRNVI
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
 * Transaction_GRNVI
 *
 * This class implements the GRNVI transaction that is used to record additional
 * Product Inventory details which is In-transit from Supplier.
 * Uses Supplier Printed ISSN Identification Barcodes.
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_GRNVI Extends Transaction
{
    /**
     * The Product ID of the In-transit Inventory
     *
     * @var string
     */
    public $productId;

    /**
     * In-Transit Location ID of the Inventory. i.e. not yet
     * delivered. (Includes Repository ID)
     *
     * @var string
     */
    public $locationId;

    /**
     * The GRN under which the Inventory is being delivered to the
     * Warehouse.
     *
     * @var string
     */
    public $grnNo;

    /**
     * The SSN ID of the In-transit Inventory which was created by
     * the Supplier and previously inserted in SSN Table by GRNVS
     * Transaction. If SSN ID does not exist in SSN table then
     * Transaction Response will be unsuccessful.
     *
     * @var string
     */
    public $ssnId;

    /**
     * The SSN ID Barcode of the In-transit Inventory which was
     * printed and attached by the Supplier. If does not exist then
     * insert record into ISSN Table with ORIGINAL_SSN = SSN
     *
     * @var string
     */
    public $issnId;

    /**
     * Used to flag if Product a Kit
     *
     * @var string
     */
    public $kitFlag;

    /**
     * The quantity of this individual ISSN being shipped to the
     * Warehouse
     *
     * @var string
     */
    public $totalVerified;

    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode     = 'GRNV';
        $this->transClass    = 'I';
        $this->productId     = '';
        $this->locationId    = '';
        $this->grnNo         = '';
        $this->ssnId         = '';
        $this->issnId        = '';
        $this->kitFlag       = '';
        $this->totalVerified = '';
    }

    /**
     * Returns the object id for inserting into the database
     *
     * The object id for the GRNVI transaction is the product ID of the
     * In-transit Inventory
     *
     * @return string
     */
    public function getObjectId()
    {
        return $this->productId;
    }

    /**
     * Returns the location for inserting into the database
     *
     * The location for the GRNVI transaction is In-Transit Location
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->locationId;
    }

    /**
     * Returns the sublocation for inserting into the database
     *
     * The sublocation is the GRN for the transaction
     * Requires previous GRNDB Transaction to create GRN + GRNCN Transaction
     * with Shipping Container Number
     *
     * @return string
     */
    public function getSubLocation()
    {
        return $this->grnNo;
    }

    /**
     * Returns the reference for inserting into the database
     *
     * The reference is SSN ID + ISSN ID + quantity of this individual ISSN
     * being shipped to the Warehouse
     * SSN must exist after previous GRNVS Transaction
     *
     * @return string
     */
    public function getReference()
    {
        return $this->ssnId . '|' .  $this->issnId . '|' .  $this->kitFlag;
    }

    /**
     * Returns the quantity for inserting into the database
     *
     * The quantity is the current quantity printed on the ISSN label
     *
     * @return string
     */
    public function getQuantity()
    {
        return $this->totalVerified;
    }
}
