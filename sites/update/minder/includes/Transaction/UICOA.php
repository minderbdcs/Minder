<?php
/**
 * Transaction_UICOA
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
 * Transaction_UICOA
 *
 * This class implements the UICOA transaction that is used to:
 * Updates an ISSNs COMPANY_ID Field with a Company ID which
 * may occur after it was originally Received at the Warehouse
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_UICOA Extends Transaction
{
    /**
     * The ISSN to be updated
     *
     * @var string
     */
    public $objectId;

    /**
     * The ISSN.COMPANY_ID Field to be updated with this value
     *
     * @var string
     */
    public $companyId;

    /**
     * The LOCN_ID
     *
     * @var string
     */
    public $locnId;

    /**
     * The WH_ID
     *
     * @var string
     */
    public $whId;
    
    public $qty;

    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode  = 'UICO';
        $this->transClass = 'A';
        $this->objectId   = '';
        $this->companyId  = '';
        $this->whId       = '';
        $this->locnId     = '';
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
     * Returns the COMPANY_ID value for update
     *
     * return max 40 characters
     *
     * @return string
     */
    public function getReference()
    {
        if (strlen($this->companyId) > 40) {
            return substr($this->companyId, 0, 40);
        } else {
            return $this->companyId;
        }
    }

    /**
     * Returns the Quantity for inserting into the database
     *
     * The quantity is always the value 1
     *
     * @return string
     */
    public function getQuantity()
    {
        return $this->qty;;
    }

    /**
     * Returns the location for inserting into the database
     *
     * The location for the UICO transaction is empty string
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
     * The sublocation for the UICO transaction is empty string
     *
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }
}
