<?php
/**
 * Transaction_GRNCL
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
 * Transaction_GRNCL
 *
 * This class implements the GRNCL transaction that is used to assign a lot no.
 * to a delivery GRN, creates a lot line no., updates company & supplier ID's
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_GRNCL Extends Transaction
{
    /**
     * The Goods Receipt No previously created for this Delivery
     *
     * @var string
     */
    public $grnNo;

    /**
     * The ID of the inventory owner
     *
     * @var string
     */
    public $ownerId;

    /**
     * The ID of the inventory supplier
     *
     * @var string
     */
    public $supplierId;

    /**
     * The lot number for the received inventory. If empty the system generates
     * this number.
     *
     * @var string
     */
    public $lotNo;

    /**
     * The line no for this lot
     *
     * @var string
     */
    public $lotLineNo;


    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode  = 'GRNC';
        $this->transClass = 'L';
        $this->grnNo      = '';
        $this->ownerId    = '';
        $this->supplierId = '';
        $this->lotNo      = '';
        $this->lotLineNo  = '';
    }

    /**
     * Returns the object id for inserting into the database
     *
     * The object id for the GRNCL transaction is the grnNo
     *
     * @return string
     */
    public function getObjectId()
    {
        return $this->grnNo;
    }

    /**
     * Returns the location for inserting into the database
     *
     * The location for the GRNCL transaction is the ownerId
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->ownerId;
    }

    /**
     * Returns the sublocation for inserting into the database
     *
     * The sublocation is the supplierId
     *
     * @return string
     */
    public function getSubLocation()
    {
        return $this->supplierId;
    }

    /**
     * Returns the reference for inserting into the database
     *
     * The reference is lotNo + '|' + lotLineNo + '|'
     *
     * @return string
     */
    public function getReference()
    {
        return $this->lotNo .  '|' .  $this->lotLineNo .  '|';
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
        return 1;
    }
}
