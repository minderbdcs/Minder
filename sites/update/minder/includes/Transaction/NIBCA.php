<?php
/**
 * Transaction_NIBCA
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
 * Transaction_NIBCA
 *
 * This class implements the NIBCA transaction that is used to:
 * Updates an SSNs BRAND Field with a value
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_NIBCA Extends Transaction
{
    /**
     * The SSN to be updated
     *
     * @var string
     */

    public $ssnId;
    public $whId;
    public $locnId;
    /**
     * The SSN.BRAND Field to be updated with this value.
     *
     * @var string
     */
    public $brandCodeValue;

    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode      = 'NIBC';
        $this->transClass     = 'A';
        $this->ssnId       = '';
        $this->brandCodeValue = '';
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
     * Returns the BRAND value for update
     *
     * @return string
     */
    public function getReference()
    {
        return $this->brandCodeValue;
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
     * The location for the NIBC transaction is empty string
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
     * The sublocation for the NIBC transaction is empty string
     *
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }
}
