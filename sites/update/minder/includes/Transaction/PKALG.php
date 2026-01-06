<?php
/**
 * Transaction_PKALG
 *
 * PHP version 5.2.3
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
 * Transaction_PKALI
 *
 * This class implements the PKALI transaction that is used to record a comment
 * about goods delivered to the warehouse.
 *
 * @category  Minder
 * @package   Minder
 * @author    Richard Buggy <rich@zoombug.com.au>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_PKALG extends Transaction
{
    /**
     * The pick order to allocate
     */
    public $orderNo;

    /**
     * The ID of the device that is being allocated the order
     *
     * @var string
     */
    public $deviceId;
    
    public $trolleyId;
    
    public $orderQty;
    
    public $pickerId;

    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode   = 'PKAL';
        $this->transClass  = 'G';
        $this->orderNo     = '';
        $this->deviceId    = '';
        $this->trolleyId   = '';
        $this->orderQty    = 0;
        $this->pickerId    = '';
    }

    /**
     * Returns the object id for inserting into the database
     *
     * @return string
     */
    public function getObjectId()
    {
        return $this->orderNo;
    }

    /**
     * Returns the location for inserting into the database
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->deviceId;
    }

    /**
     * Returns the sublocation for inserting into the database
     *
     * @return string
     */
    public function getSubLocation()
    {
        return $this->trolleyId;
    }

    /**
     * Returns the reference for inserting into the database
     *
     * @return string
     */
    public function getReference()
    {
        return $this->pickerId;
    }

    /**
     * Returns the quantity for inserting into the database
     *
     * @return string
     */
    public function getQuantity()
    {
        return $this->orderQty;
    }
}
