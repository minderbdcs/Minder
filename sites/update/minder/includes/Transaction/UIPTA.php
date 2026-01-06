<?php
/**
 * Transaction_UIPTA
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
 * Transaction_UIPTA
 * ‘UIPT’ will be used to Update the ISSN’s ISSN_PACKAGE_TYPE and ISSN_PREV_PACKAGE_TYPE.
 * This Transaction should be called at the end of RePacking a selection of ISSN’s.
 * Or the Labels are printed after RePrint of an ISSN or ReStacking, RePacking, ReSorting
 * or PrePacking of a group of ISSN’s and therefore need to be re-labelling
 * This Transaction may be called at any time and not just as part of Picking
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_UIPTA Extends Transaction
{
    /**
     * The ISSN to be updated
     *
     * @var string
     */
    public $objectId;

    /**
     * The ISSN.ISSN_PACKAGE_TYPE Field to be updated with this value
     *
     * @var string
     */
    public $issnPackageType;

    /**
     *  The Location ID of the ISSN
     *
     * @var string
     */
    public $locationId;

    /**
     *  The WH ID of the ISSN
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
        $this->transCode       = 'UIPT';
        $this->transClass      = 'A';
        $this->objectId        = '';
        $this->issnPackageType = '';
        $this->qty             = 1;
        $this->whId            = ''; 
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
     * Returns the ISSN_PACKAGE_TYPE value for update
     * Max 2 characters
     *
     * @return string
     */
    public function getReference()
    {
        if (strlen($this->issnPackageType) > 2) {
            return substr($this->issnPackageType, 0, 2);
        } else {
            return $this->issnPackageType;
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
     * @return string
     */
    public function getLocation()
    {
        return $this->whId . $this->locationId;
    }

    /**
     * Returns the sublocation for inserting into the database
     *
     * The sublocation for the UIPT transaction is empty string
     *
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }
}
