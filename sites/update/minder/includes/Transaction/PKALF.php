<?php
/**
 * Transaction_PKALF
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
 * Transaction_PKALF
 *
 * This class implements the PKALF transaction that is used to record a comment
 * about goods delivered to the warehouse.
 *
 * @category  Minder
 * @package   Minder
 * @author    Richard Buggy <rich@zoombug.com.au>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_PKALF extends Transaction
{
    /**
     * The ID of the device that is being allocated the order
     *
     * @var string
     */
    public $deviceId;

    /**
     * The pick label number of the line to allocate
     *
     * @var string
     */
    public $pickLabelNo;

    /**
     * The due date. String in format YYDDD
     *
     * @var string
     */
//    public $dueDate; //not needed as specified in http://dev.barcoding.com.au/project/minder/wiki/Transaction_PKAL

    /**
     * The PROD_ID
     *
     * @var string
     */
    public $prodId;
    
    public $pickerId;

    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode   = 'PKAL';
        $this->transClass  = 'F';
        $this->deviceId    = '';
        $this->pickLabelNo = '';
//        $this->dueDate     = '';
        $this->prodId      = '';
        $this->pickerId    = '';
    }

    /**
     * Returns the object id for inserting into the database
     *
     * @return string
     */
    public function getObjectId()
    {
        return $this->pickLabelNo;
    }

    /**
     * Returns the location for inserting into the database
     *
     * @return string
     */
    public function getLocation()
    {
//        return substr($this->deviceId, 0, 2) . 'T|';
        return substr($this->deviceId, 0, 2);
	// not used
    }

    /**
     * Returns the sublocation for inserting into the database
     *
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }

    /**
     * Returns the reference for inserting into the database
     *
     * @return string
     */
    public function getReference()
    {
	// no it is whom to allocate for and the product
        return $this->pickerId . '|' . $this->prodId;
    }

    /**
     * Returns the quantity for inserting into the database
     *
     * @return string
     */
    public function getQuantity()
    {
        return 1; //should return orderQty, but orderQty field not desctibed for PKAL J transaction
    }
}
