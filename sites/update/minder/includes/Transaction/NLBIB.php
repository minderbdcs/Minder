<?php
/**
 * Transaction_NLBIB
 *
 * PHP version 5.2.3
 *
 * @category  Minder
 * @package   Minder
 * @author    Denis Obuhov
 * @copyright 2009 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 *
 */

require_once 'Transaction.php';

class Transaction_NLBIB Extends Transaction
{
    public $borrowerId;
    public $borrowerName;
    public $companyId;
    public $date;

    /**
     * Initialise the transaction
     *
     * @return Transaction_NLBIB
     */
    public function __construct()
    {
        $this->transCode    = 'NLBI';
        $this->transClass   = 'B';
    }

    /**
     * Returns the Object updated
     *
     * @return string
     */
    public function getObjectId()
    {
        return $this->borrowerId . '|' . $this->date;
    }

    /**
     * Returns the Reference value for update
     *
     * @return string
     */
    public function getReference()
    {
        return $this->borrowerName;
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
     * The location for the NITP transaction is empty string
     *
     * @return string
     */
    public function getLocation()
    {
        return 'XB' . $this->borrowerId;
    }

    /**
     * Returns the sublocation for inserting into the database
     *
     * The sublocation for the NITP transaction is empty string
     *
     * @return string
     */
    public function getSubLocation()
    {
        return $this->companyId;
    }
}

?>