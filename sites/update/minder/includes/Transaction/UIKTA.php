<?php
/**
 * Transaction_UIKTA
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
 * Transaction_UIKTA
 *
 * This class implements the UIKTA transaction that is used to:
 * Updates an ISSNs KITTED Field with a value
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_UIKTA Extends Transaction
{
    /**
     * The ISSN to be updated
     *
     * @var string
     */

    public $objectId;
    /**
     * The ISSN.KITTED Field to be updated with this value
     *
     * @var string
     */
    public $kittedValue;

    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode   = 'UIKT';
        $this->transClass  = 'A';
        $this->objectId    = '';
        $this->kittedValue = '';
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
     * Returns the KITTED value for update
     * Max 1 characters
     *
     * @return string
     */
    public function getReference()
    {
        if (strlen($this->kittedValue) > 1) {
            return substr($this->kittedValue, 0, 1);
        } else {
            return $this->kittedValue;
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
     * The location for the UIKT transaction is empty string
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
     * The sublocation for the UIKT transaction is empty string
     *
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }
}
