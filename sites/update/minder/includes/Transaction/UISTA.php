<?php
/**
 * Transaction_UISTA
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
 * Transaction_UISTA
 * Updates an ISSN's Inventory Status (ISSN_STATUS) Field (which is not
 * to be confused with STATUS_CODE. Inventory maybe temporarily given
 * Quarantine Status (ISSN_STATUS='QR') and once inspected and passes
 * it must be returned to either Putaway (ISSN_STATUS='PA') or Saleable
 * Stock (ISSN_STATUS='ST') before it can be Transferred out of a
 * Quarantine location. This Transaction is designed to update the
 * ISSN_STATUS and keep an Audit Trail.
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_UISTA Extends Transaction
{
    /**
     * The ISSN to be updated
     *
     * @var string
     */
    public $objectId;

    /**
     * The ISSN.ISSN_STATUS Field to be updated with this value
     *
     * @var string
     */
    public $issnStatus;
    
    public $qty;
    
    public $whId;
    
    public $locnId;

    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode  = 'UIST';
        $this->transClass = 'A';
        $this->objectId   = '';
        $this->issnStatus = '';
        $this->whId       = '';
        $this->locnId     = '';
        $this->qty        = 1;
    }

    /**
     * Returns the ISSN which should be updated
     * Max 12 characters
     *
     * @return string
     */
    public function getObjectId()
    {
        if (strlen($this->objectId) > 12) {
            return substr($this->objectId, 0, 12);
        } else {
            return $this->objectId;
        }
    }

    /**
     * Returns the ISSN_STATUS value for update
     * Max 2 characters
     *
     * @return string
     */
    public function getReference()
    {
        if (strlen($this->issnStatus) > 2) {
            return substr($this->issnStatus, 0, 2);
        } else {
            return $this->issnStatus;
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
     * The location for the UIST transaction is empty string
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
     * The sublocation for the UIST transaction is empty string
     *
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }
}
