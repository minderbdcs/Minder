<?php
/**
 * Transaction_UIO1A
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
 * Transaction_UIO1A
 *
 * This class implements the UIO1A transaction that is used to:
 * Updates an ISSNs OTHER1 Field with a value
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_UIO1A Extends Transaction
{
    /**
     * The ISSN to be updated
     *
     * @var string
     */

    public $objectId;
    /**
     * The ISSN.OTHER1 Field to be updated with this value
     *
     * @var string
     */
    public $other1Value;
    
    public $qty;
    
    public $locnId;
    
    public $whId; 

    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode   = 'UIO1';
        $this->transClass  = 'A';
        $this->objectId    = '';
        $this->other1Value = '';
        $this->qty         = 1;
        $this->locnId      = '';
        $this->whId        = ''; 
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
     * Returns the OTHER1 value for update
     *
     * return max 40 characters
     *
     * @return string
     */
    public function getReference()
    {
        if (strlen($this->other1Value) > 40) {
            return substr($this->other1Value, 0, 40);
        } else {
            return $this->other1Value;
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
        return $this->qty;
    }

    /**
     * Returns the location for inserting into the database
     *
     * The location for the UIO1 transaction is empty string
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
     * The sublocation for the UIO1 transaction is empty string
     *
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }
}
