<?php
/**
 * Transaction_AUOBC
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
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_AUOBC Extends Transaction
{
    public $ssnId;

    public $locnId;

    public $whId;

    public $reason = '';

    /**
     * Initialise the transaction
     *
     * @return \Transaction_AUOBC
     */
    public function __construct()
    {
        $this->transCode  = 'AUOB';
        $this->transClass = 'C';
        $this->locnId     = '';
        $this->whId       = '';
    }

    /**
     * Returns the ISSN which should be updated
     * Max 10 characters
     *
     * @return string
     */
    public function getObjectId()
    {
        return $this->ssnId;
    }

    /**
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reason;
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
     * The location for the AUOB transaction is empty string
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->whId . $this->locnId;
    }

    /**
     * Returns the sublocation for inserting into the database
     *
     * The sublocation for the AUOB transaction is empty string
     *
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }
}
