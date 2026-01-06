<?php
/**
 * Transaction_STLXA
 *
 * PHP version 5.2.4
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
 * Transaction_STLXA
 * Exit Stocktake of a Location
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_STLXA Extends Transaction
{
    /**
     *  The Location ID of the ISSN
     *
     * @var string
     */
    public $locationId;

    /**
     *  The WH ID of the ISSN
     *
     * @var string
     */
    public $whId;

    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode      = 'STLX';
        $this->transClass     = 'A';
        $this->locationId     = '';
        $this->whId           = '';
    }

    /**
     * Returns empty string
     *
     * @return string
     */
    public function getObjectId()
    {
        return '';
    }

    /**
     * Returns the reference value for update
     *
     * @return string
     */
    public function getReference()
    {
        return '';
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
     * @return string
     */
    public function getLocation()
    {
        return $this->whId . $this->locationId;
    }

    /**
     * Returns the sublocation for inserting into the database
     *
     * The sublocation for the STLX transaction is empty string
     *
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }
}
