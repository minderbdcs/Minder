<?php
/**
 * Transaction_UISCA
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
 * Transaction_UISCA
 *
 * This class implements the UISCA transaction that is used to:
 * Updates an ISSNs STATUS_CODE Field with a value
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_UISCA Extends Transaction
{
    /**
     * The ISSN to be updated
     *
     * @var string
     */

    public $objectId;
    /**
     * The ISSN.STATUS_CODE Field to be updated with this value
     *
     * @var string
     */
    public $statusCode;

    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode  = 'UISC';
        $this->transClass = 'A';
        $this->objectId   = '';
        $this->statusCode = '';
    }

    /**
     * Returns the ISSN to be updated
     *
     * @return string
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * Returns the STATUS_CODE value for update
     * Max 10 characters
     *
     * @return string
     */
    public function getReference()
    {
        if (strlen($this->statusCode) > 10) {
            return substr($this->statusCode, 0, 10);
        } else {
            return $this->statusCode;
        }
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
     * The location for the UISC transaction is empty string
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
     * The sublocation for the UISC transaction is empty string
     *
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }
}
