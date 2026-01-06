<?php
/**
 * Transaction_GRNXG
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
 * This class implements GRNXG transaction
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_GRNXG Extends Transaction
{
    /**
     * @var string
     */
    public $qty;

    public $subLocation;

    private $_userId;

    /**
     * Initialise the transaction
     *
     * @param integer $qty         Quantity
     * @param string  $subLocation GRN to cancel
     * @param string  $userId      User who made cancel
     *
     * @return void
     */
    public function __construct($subLocation = '', $userId = '', $qty = 0)
    {
        $this->transCode   = 'GRNX';
        $this->transClass  = 'G';
        $this->subLocation = $subLocation;
        $this->_userId     = $userId;
    }

    /**
     * Return object ID
     *
     * @return string
     */
    public function getObjectId()
    {
        return substr($this->getComment(), 0, 30);
    }

    /**
     * Return reference
     *
     * @return string
     */
    public function getReference()
    {
        return substr($this->getComment(), 40, 40);
    }

    /**
     * Returns the quantity for inserting into the database
     *
     * By default quantity is always the value 1
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
        return substr($this->getComment(), 30, 10);
    }

    /**
     * Returns the sublocation for inserting into the database
     *
     * @return string
     */
    public function getSubLocation()
    {
        return $this->subLocation;
    }

    public function __get($name) {        if ($name == 'userId') {
            $this->_userId = $value;
        } else {
            throw new Exception ('Property \'' . $name . '\' not found.');
        }
    }

    public function __set($name, $value)
    {        if ($name == 'userId') {            $this->_userId = $value;        } else {            throw new Exception ('Property \'' . $name . '\' not found.');        }

    }

    private function getComment()
    {        return 'Cancelled at ' . date('Y-m-d H:i:s') . ' by ' . $this->_userId;    }
}
