<?php
/**
 * Transaction_STISF
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
 * Transaction_STISF
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_STISF Extends Transaction
{
    /**
     * The ISSN to be updated
     *
     * @var string
     */
    public $objectId;

    /**
     * The ISSN.CURRENT_QTY actual Count of ISSN
     *
     * @var string
     */
    public $issnCurrentQty;

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
     * Some comments for transaction
     *
     * @var string
     */
    public $comment;

    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode      = 'STIS';
        $this->transClass     = 'F';
        $this->objectId       = '';
        $this->locationId     = '';
        $this->whId           = '';
        $this->issnCurrentQty = '1';
        $this->comment        = '';
    }

    /**
     * Returns the ISSN which should be updated
     * Max 12 characters
     *
     * @return string
     */
    public function getObjectId()
    {
        if (strlen($this->objectId) > 12) {
            return substr($this->objectId, 0, 12);
        } else {
            return $this->objectId;
        }
    }

    /**
     * Returns the reference value for update
     *
     * @return string
     */
    public function getReference()
    {
        return $this->getQuantity() . '|' . $this->comment . '|';
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
        return $this->issnCurrentQty;
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
     * The sublocation for the STIS transaction is empty string
     *
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }
}