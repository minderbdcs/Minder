<?php
/**
 * Transaction_UIDIA
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
 * Transaction_UIDIA
 *
 * This class implements the UIDIA transaction that is used to:
 * Updates an ISSNs DIVISION_ID Field with a Division ID which
 * may occur after it was originally Received at the Warehouse
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_UIDIA Extends Transaction
{
    /**
     * The ISSN to be updated
     *
     * @var string
     */

    public $objectId;
    /**
     * The ISSN.DIVISION_ID Field to be updated with this value
     *
     * @var string
     */
    public $divisionId;
    
    public $whId;
    
    public $locnId;
    
    public $qty;

    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode  = 'UIDI';
        $this->transClass = 'A';
        $this->objectId   = '';
        $this->divisionId = '';
        $this->locnId     = '';
        $this->whId       = '';
        $this->qty        = 1;  
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
     * Returns the DIVISION_ID value for update
     *
     * @return string
     */
    public function getReference()
    {
        return $this->divisionId;
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
     * The location for the UIDI transaction is empty string
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
     * The sublocation for the UIDI transaction is empty string
     *
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }
}
