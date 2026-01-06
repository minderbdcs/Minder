<?php
/**
 * Transaction_STLOA
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
 * Transaction_STLOA
 * used to update LOCATION.LAST_AUDITED_DATE� + Reset all ISSN.AUDITED =>�M�
 * Not Counted and ISSN.AUDIT_DATE => NOW()
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_STLOA Extends Transaction
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
        $this->transCode      = 'STLO';
        $this->transClass     = 'A';
        $this->locationId     = '';
        $this->whId           = '';
    }

    /**
     * Returns the empty string
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
        return 'Reset Location Audit and all ISSN inside';
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
     * The sublocation for the STLO transaction is empty string
     *
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }

    /**
     * @param string $transactionResponse
     * @return Transaction_Response_STLOA
     */
    public function parseResponse($transactionResponse)
    {
        return new Transaction_Response_STLOA($transactionResponse);
    }


}
