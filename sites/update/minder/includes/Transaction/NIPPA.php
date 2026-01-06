<?php
/**
 * Transaction_NIPPA
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
 * Transaction_NIPPA
 *
 * This class implements the NIPPA transaction that is used to:
 * Updates an SSNs GENERIC Field with a value
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_NIPPA Extends Transaction
{
    /**
     * The SSN to be updated
     *
     * @var string
     */
    public $ssnId;
    public $whId;
    public $locnId;
    public $purchasePrice;
    /**
     * The SSN.GENERIC Field to be updated with this value. We now
     * prefer to refer to this field as Type II instead of Generic
     *
     * @var string
     */
    public $genericValue;

    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode        = 'NIPP';
        $this->transClass       = 'A';
        $this->ssnId            = '';
        $this->genericValue     = '';
        $this->purchasePrice    = 0;
    }

    /**
     * Returns the SSN to be updated
     *
     * @return string
     */
    public function getObjectId()
    {
        return $this->ssnId;
    }

    /**
     * Returns the GENERIC value for update
     *
     * @return string
     */
    public function getReference()
    {
        return $this->genericValue;
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
        return $this->purchasePrice * 100;
    }

    /**
     * Returns the location for inserting into the database
     *
     * The location for the NIOB transaction is empty string
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
     * The sublocation for the NIOB transaction is empty string
     *
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }
}
