<?php
/**
 * Transaction_GSMDC
 *
 * PHP version 5.2.4
 *
 * @category  Minder
 * @package   Minder
 * @author    Suhinin Dmitriy <suhinin.dmitriy@binary-studio.com>
 * @copyright 2009 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 *
 */

/**
 * Include the definition of the Transaction base class
 */
require_once 'Transaction.php';


class Transaction_GSMDC Extends Transaction
{
    /**
     * @var string
     */
    
    public $reference;

    
    
    /**
     * Returns the ISSN which should be updated
     * Max 10 characters
     *
     * @return string
     */
    public function getObjectId()
    {
        
    }
    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode  = 'GSMD';
        $this->transClass = 'C';
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
     * The quantity is always the value 1
     *
     * @return string
     */
    public function getQuantity()
    {
        
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
        
    }
}
