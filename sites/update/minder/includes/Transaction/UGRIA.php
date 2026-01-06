<?php
/**
 * Transaction_UGRIA
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
 * Transaction_UGRIA
 *
 * This class implements the UGRIA transaction that is used to:
 * Updates a GRNs RETURN_ID Field with the Supplier ID which
 * may occur after it was originally Received at the Warehouse
 * usually because of incorrect Receipt information.
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_UGRIA Extends Transaction
{
    /**
     * The GRN to be updated
     *
     * @var string
     */
    public $grnId;
    /**
     * The GRN.RETURN_ID Field to be updated with this value
     *
     * @var string
     */
    public $returnId;

    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode  = 'UGRI';
        $this->transClass = 'A';
        $this->grnId      = '';
        $this->returnId   = '';
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
     * Returns the RETURN_ID value for update
     *
     * @return string
     */
    public function getReference()
    {
        return $this->returnId;
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
        return '1';
    }

    /**
     * Returns the location for inserting into the database
     *
     * The location for the UGRI transaction is empty string
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
     * The sublocation for the UGRI transaction is empty string
     *
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }
}
