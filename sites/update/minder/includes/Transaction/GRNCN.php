<?php
/**
 * Transaction_GRNCN
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
 * Transaction_GRNCN
 *
 * This class implements the GRNCN transaction that is used to record a
 * Shipping Container Number about goods delivered to the warehouse.
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_GRNCN Extends Transaction
{
    /**
     * The Goods Receipt No. previously created for this delivery
     *
     * @var string
     */
    public $grnNo;

    /**
     * The Shipping Container No. used to deliver the inventory
     *
     * @var string
     */
    public $containerNo;

    /**
     * Delivery Container Type e.g. 2G = 20 Foot General Shipping Container
     *
     * @var string
     */
    public $containerTypeId;

    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode       = 'GRNC';
        $this->transClass      = 'N';
        $this->grn             = '';
        $this->containerNo     = '';
        $this->containerTypeId = '';
    }

    /**
     * Returns the location for inserting into the database
     *
     * The location for the GRNCN always empty string
     *
     * @return string
     */
    public function getLocation()
    {
        return '';
    }

    /**
     * Returns the location for inserting into the database
     *
     * The sublocation for the GRNCN always empty string
     *
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }

    /**
     * Returns the object id for inserting into the database
     *
     * The object id for the GRNCN transaction is grnNo + '|'
     *
     * @return string
     */
    public function getObjectId()
    {
        return $this->grnNo .  '|';
    }

    /**
     * Returns the reference for inserting into the database
     *
     * The reference is Shipping Container No. + '|' + Container Type ID + '|'
     *
     * @return string
     */
    public function getReference()
    {
        return $this->containerNo .  '|' .  $this->containerTypeId .  '|';
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
