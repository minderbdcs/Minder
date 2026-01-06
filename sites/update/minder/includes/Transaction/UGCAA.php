<?php
/**
 * Transaction_UGCAA
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
 * Transaction_UGCAA
 *
 * This class implements the UGCAA transaction that is used to:
 * Updates a GRNs CARRIER Field  which may occur after it was
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
class Transaction_UGCAA Extends Transaction
{
    /**
     * The GRN to be updated
     *
     * @var string
     */
    public $grnId;
    /**
     * The GRN.CARRIER Field to be updated with this value
     *
     * @var string
     */
    public $carrierId;

    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode  = 'UGCA';
        $this->transClass = 'A';
        $this->grnId      = '';
        $this->carrierId  = '';
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
     * Returns the CARRIER value to update
     *
     * @return string
     */
    public function getReference()
    {
        return $this->carrierId;
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
     * The location for the UGCA transaction is empty string
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
     * The sublocation for the UGCA transaction is empty string
     *
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }
}
