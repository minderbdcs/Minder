<?php
/**
 * Transaction_UGCNA
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
 * Transaction_UGCNA
 *
 * This class implements the UGCNA transaction that is used to:
 * Updates a GRNs AWB_CONSIGNMENT_NO Field  which may occur
 * after it was originally Received at the Warehouse usually
 * because of incorrect Receipt information.
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_UGCNA Extends Transaction
{
    /**
     * The GRN to be updated
     *
     * @var string
     */
    public $grnId;
    /**
     * The GRN.AWB_CONSIGNMENT_NO Field to be updated with this
     * value
     *
     * @var string
     */
    public $awbconnoteNo;

    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode    = 'UGCN';
        $this->transClass   = 'A';
        $this->grnId        = '';
        $this->awbconnoteNo = '';
    }

    /**
     * Returns the GRN to be update
     *
     * @return string
     */
    public function getObjectId()
    {
        return $this->grnId;
    }

    /**
     * Returns the AWB_CONSIGNMENT_NO value to update
     *
     * @return string
     */
    public function getReference()
    {
        return $this->awbconnoteNo;
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
        return '1';
    }

    /**
     * Returns the location for inserting into the database
     *
     * The location for the UGCN transaction is empty string
     *
     * @return string
     */
    public function getLocation()
    {
        return '';
    }

    /**
     * Returns the sublocation for inserting into the database
     *
     * The sublocation for the UGCN transaction is empty string
     *
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }
}
