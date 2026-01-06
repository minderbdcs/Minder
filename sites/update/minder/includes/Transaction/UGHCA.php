<?php
/**
 * Transaction_UGHCA
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
 * Transaction_UGHCA
 *
 * This class implements the UGHCA transaction that is used to:
 * Updates a GRNs PACK_CRATE_OWNER, PACK_CRATE_TYPE and
 * PACK_CRATE_QTY Fields  which may occur after it was
 * originally Received at the Warehouse usually because of
 * incorrect Receipt information.
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_UGHCA Extends Transaction
{
    /**
     * The GRN to be updated
     *
     * @var string
     */
    public $grnId;
    /**
     * The GRN.PACK_CRATE_OWNER Field to be updated with this value
     *
     * @var string
     */
    public $crateOwner;
    /**
     * The GRN.PACK_CRATE_TYPE Field to be updated with this value
     *
     * @var string
     */
    public $crateType;
    /**
     * The GRN.PACK_CRATE_QTY Field to be updated with this value
     *
     * @var string
     */
    public $crateQty;

    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode  = 'UGHC';
        $this->transClass = 'A';
        $this->grnId      = '';
        $this->crateOwner = '';
        $this->crateType  = '';
        $this->crateQty   = '';
    }

    /**
     * Returns the GRN to be updated
     *
     * @return string
     */
    public function getObjectId()
    {
        return $this->grnId;
    }

    /**
     * Returns the REFERENCE value to update
     *
     * @return string
     */
    public function getReference()
    {
        return $this->crateOwner.
               '|'.
               $this->crateType.
               '|';
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
        return $this->crateQty;
    }

    /**
     * Returns the location for inserting into the database
     *
     * The location for the UGHC transaction is empty string
     *
     * @return string
     */
    public function getLocation()
    {
        return '';
    }

    /**
     * Returns the sublocation for inserting into the database
     *
     * The sublocation for the UGHC transaction is empty string
     *
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }
}
