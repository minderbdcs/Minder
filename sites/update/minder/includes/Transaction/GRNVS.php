<?php
/**
 * Transaction_GRNVS
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
 * Transaction_GRNVS
 *
 * This class implements the GRNVS transaction that is used to record Inventory
 * which is In-transit from Supplier. Uses Supplier Printed ISSN Identification
 * Barcodes
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_GRNVS Extends Transaction
{
    /**
     * The Supplier ID of the In-transit Inventory
     *
     * @var string
     */
    public $supplierId;

    /**
     * The Owner ID of the In-transit Inventory
     *
     * @var string
     */
    public $ownerId;

    /**
     * The Division ID of the In-transit Inventory (Future, not yet
     * being used)
     *
     * @var string
     */
    public $divisionId;

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
     * the Supplier. If it does not exist then Insert into SSN
     * table plus GRN and Order No and Type details
     *
     * @var string
     */
    public $ssnId;

    /**
     * The SSN ID of the In-transit Inventory which was created by
     * the Supplier. If it does not exist then insert into ISSN
     * Table with ORIGINAL_SSN = SSN
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
     * The overall Total ISSNs to be Verified. See GRNVI
     * Transaction
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
        $this->transClass    = 'S';
        $this->supplierId    = '';
        $this->ownerId       = '';
        $this->divisionId    = '';
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
     * The object id for the GRNVS transaction is the Supplier ID of
     * the In-transit Inventory
     *
     * @return string
     */
    public function getObjectId()
    {
        return str_pad($this->supplierId, 10, ' ', STR_PAD_RIGHT)
               . str_pad($this->ownerId, 10, ' ', STR_PAD_RIGHT)
               . str_pad($this->divisionId, 10, ' ', STR_PAD_RIGHT);
    }

    /**
     * Returns the location for inserting into the database
     *
     * The location for the GRNVS transaction is In-Transit Location
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
     * The reference is ssnId + | + issnId + | + kitFlag
     *
     * @return string
     */
    public function getReference()
    {
        return substr($this->ssnId, 0, 18) . '|' .
               substr($this->issnId, 0, 18) . '|' .
               $this->kitFlag;
    }

    /**
     * Returns the quantity for inserting into the database
     *
     * The quantity of ISSN
     * User must scan this quantity of ISSN's, create GRNVI
     * transaction for each ISSN
     *
     * @return string
     */
    public function getQuantity()
    {
        return $this->totalVerified;
    }
}
