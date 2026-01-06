<?php
/**
 * Transaction_GRNCC
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
 * Transaction_GRNCC
 *
 * This class implements the GRNCC transaction that is used to record a comment
 * about goods delivered to the warehouse.
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_GRNCC extends Transaction
{
    /**
     * The GRN No for the transaction
     *
     * @var string
     */
    public $grnNo;

    /**
     * The comment for the transaction
     *
     * @var string
     */
    public $comment;

    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode  = 'GRNC';
        $this->transClass = 'C';
        $this->grnNo      = '';
        $this->comment    = '';
    }

    /**
     * Returns the object id for inserting into the database
     *
     * The object id for the GRNCC transaction is the first 40 characters of
     * the comment
     *
     * @return string
     */
    public function getObjectId()
    {
        $text = substr($this->comment, 0, 40);
        if ($text === false) {
            return '';
        } else {
            return $text;
        }
    }

    /**
     * Returns the location for inserting into the database
     *
     * The location for the GRNCC transaction if characters 41-50 of the comment
     *
     * @return string
     */
    public function getLocation()
    {
        $text = substr($this->comment, 40, 10);
        if ($text === false) {
            return '';
        } else {
            return $text;
        }
    }

    /**
     * Returns the sublocation for inserting into the database
     *
     * The sublocation is the GRN for the transaction
     *
     * @return string
     */
    public function getSubLocation()
    {
        return $this->grnNo;
    }

    /**
     * Returns the reference for inserting into the database
     *
     * The reference is characters 51-90 of the comment
     *
     * @return string
     */
    public function getReference()
    {
        $text = substr($this->comment, 50, 40);
        if ($text === false) {
            return '';
        } else {
            return $text;
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
        return 1;
    }
}
