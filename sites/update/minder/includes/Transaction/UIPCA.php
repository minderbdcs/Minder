<?php
/**
 * Transaction_UIPCA
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
 * Transaction_UIPCA
 *
 * This class implements the UIPCA transaction that is used to:
 * Updates an ISSNs Product Code (PROD_ID) Field with a value.
 * Occasionally Users make errors with Product Code during
 * Receipting, this Transaction is designed to correct these
 * errors and keep an Audit Trail.
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_UIPCA Extends Transaction
{
    /**
     * The ISSN to be updated
     *
     * @var string
     */
    public $objectId;
    /**
     * The ISSN.PROD_ID Field to be updated with this value
     *
     * @var string
     */
    public $prodIdValue;

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
        $this->transCode   = 'UIPC';
        $this->transClass  = 'A';
        $this->objectId    = '';
        $this->prodIdValue = '';
        $this->whId        = '';
        $this->locnId      = '';
        $this->qty         = 1;
    }

    /**
     * Returns the ISSN which should be updated
     *
     * @return string
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * Returns the PROD_ID value for update
     * Max 30 characters
     *
     * @return string
     */
    public function getReference()
    {
        if (strlen($this->prodIdValue) > 30) {
            return strpos($this->prodIdValue, 0, 30);
        } else {
            return $this->prodIdValue;
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
     * The location for the UIPC transaction is empty string
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
     * The sublocation for the UIPC transaction is empty string
     *
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }
}
