<?php
/**
 * Transaction_NID3A
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
 * Transaction_NID3A
 *
 * This class implements the NID3A transaction that is used to:
 * Updates an SSNs SSN_SUB_TYPE Field with a value
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_NID3A Extends Transaction
{
    /**
     * The SSN to be updated
     *
     * @var string
     */

    public $ssnId;
    public $whId;
    public $locnId;
    /**
     * The SSN.SSN_SUB_TYPE Field to be updated with this value. We
     * now prefer to refer to this field as Type III instead of SSN
     * Sub Type
     *
     * @var string
     */
    public $ssnSubTypeValue;

    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode       = 'NID3';
        $this->transClass      = 'A';
        $this->ssnId        = '';
        $this->ssnSubTypeValue = '';
    }

    /**
     * Returns the SSN to be updated
     *
     * @return string
     */
    public function getObjectId()
    {
        return $this->ssnId;
    }

    /**
     * Returns the SSN.SUB_TYPE value for update
     *
     * @return string
     */
    public function getReference()
    {
        return $this->ssnSubTypeValue;
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
     * The location for the NID3 transaction is empty string
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
     * The sublocation for the NID3 transaction is empty string
     *
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }
}
