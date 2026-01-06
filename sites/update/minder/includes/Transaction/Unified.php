<?php
/**
 * Transaction_Unified
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
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_Unified Extends Transaction
{
    /**
     * @var string
     */
    public $objectId;

    public $locnId;

    public $whId;

    public $reference;

    public $qty;

    public $subLocation;

    public $prodId;

    public $companyId;

    public $orderNo;

    public $orderType;

    public $orderSubType;

    public $trnClass;
 	


    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct($transCode, $transClass, $objectId = '', $locnId = '', $whId = '', $reference = '', $qty ='1', $subLocation = '', $prodId='', $companyId='', $orderNo='', $orderType='', $orderSubType='', $transFamily='', $trnDate='', $inputSource='')
    {
        $this->transCode  = $transCode;
        $this->transClass = $transClass;
        $this->objectId   = $objectId;
        $this->locnId     = $locnId;
        $this->whId       = $whId;
        $this->reference  = $reference;
        $this->subLocation = $subLocation;
        $this->qty         = $qty;
        $this->prodId    = $prodId;
	$this->companyId = $companyId;
	$this->orderNo = $orderNo;
	$this->orderType = $orderType;
	$this->orderSubType = $orderSubType;
	$this->transFamily = $transFamily;
	$this->date = $trnDate;
        $this->inputSource = $inputSource;
	
    }

    /**
     * Return object ID
     *
     * @return string
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * Return reference
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
        return $this->whId . $this->locnId;
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
    
    
    public function getInputSource()
    {
        return $this->inputSource;
    }

}
