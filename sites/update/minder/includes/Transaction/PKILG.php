<?php
/**
 * Transaction_PKILG
 *
 * PHP version 5.2.4
 *
 * @category  Minder
 * @package   Minder
 * @author    Suhinin Dmitriy <suhinin.dmitriy@binary-studio.com>
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
 *
 * @category  Minder
 * @package   Minder
 * @author    Suhinin Dmitiry <suhinin.dmitriy@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_PKILG Extends Transaction
{
    /**
     * @var string
     */
    public $objectId;

    public $locnId;

    public $whId;
    
    public $reference;
    
    public $subLocnId;

    public $pickedQty;

    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode  = 'PKIL';
        $this->transClass = 'G';
        $this->objectId   = '';
        $this->locnId     = '';
        $this->whId       = '';
        $this->reference  = '';

        $this->pickedQty  = 1;
    }

    /**
     * Returns the ISSN which should be updated
     * Max 10 characters
     *
     * @return string
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Returns the quantity for inserting into the database
     *
     * @return string
     */
    public function getQuantity()
    {
        return $this->pickedQty;
    }
    /**
     * Returns the location for inserting into the database
     *
     * The location for the AUOB transaction is empty string
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
     * The sublocation for the AUOB transaction is empty string
     *
     * @return string
     */
    public function getSubLocation()
    {
        return $this->subLocnId;
    }
}
