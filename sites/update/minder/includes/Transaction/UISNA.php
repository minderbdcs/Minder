<?php
/**
 * Transaction_UISNA
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
 * Transaction_UISNA
 *
 * This class implements the UISNA transaction that is used to:
 * Updates an ISSNs SERIAL NUMBER Field with a Serial No which
 * may occur during or after it was originally Received at the
 * Warehouse
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_UISNA Extends Transaction
{
    /**
     * The ISSN to be updated
     *
     * @var string
     */

    public $objectId;
    /**
     * The ISSN.SERIAL_NUMBER Field to be updated with this value
     *
     * @var string
     */
    public $serialNo;
    
    public $qty;
    
    public $locationId;
    
    public $whId;

    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode  = 'UISN';
        $this->transClass = 'A';
        $this->objectId   = '';
        $this->serialNo   = '';
        $this->qty        = 1;
        $this->locationId = '';
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
     * Returns the SERIAL_NUMBER value for update
     *
     * @return string
     */
    public function getReference()
    {
        return $this->serialNo;
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
     * The location for the UISN transaction is empty string
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
     * The sublocation for the UISN transaction is empty string
     *
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }
}
