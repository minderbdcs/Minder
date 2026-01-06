<?php
/**
 * Transaction_UICQA
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
 * Transaction_UICQA
 * ‘UICQ’ will be used to adjust a set of ISSN’s such their combined SUM of CURRENT_QTY
 * remains the same afterwards.
 * The PROD_ID must be the same on all ISSN’s to be updated.
 * The objective is to rearrange a Set of ISSN’s to match the ReStacking or even RePacking,
 * ReSorting of Pallets at Fresh Produce.
 * One UICQ Transaction will be sent for each ISSN to be adjusted.
 * The set of ISSN’s to be adjusted maybe all those Picked and listed in the PICK_ITEM_DETAIL
 * table for one PICK_ LABEL_NO or it maybe a set of ISSN’s selected in Location or as selected
 * (scanned) by a User who is about to perform the ReStacking or RePacking.
 * This Transaction may be called at any time and not just as part of Picking, but RePacking
 * limited to ISSN’s with same ORIGINAL_SSN.
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_UICQA Extends Transaction
{
    /**
     * The ISSN to be updated
     *
     * @var string
     */
    public $objectId;

    /**
     * The ISSN.CURRENT_QTY Field to be updated with this value
     *
     * @var string
     */
    public $currentQty;

    /**
     *  The Location ID of the ISSN
     *
     * @var string
     */
    public $locationId;

    /**
     *  The WH ID of the ISSN
     *
     * @var string
     */
    public $whId;
    
    public $reference;

    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode  = 'UICQ';
        $this->transClass = 'A';
        $this->objectId   = '';
        $this->currentQty = '1';
        $this->locationId = '';
        $this->whId       = '';
        $this->reference  = '';   
    }

    /**
     * Returns the ISSN which should be updated
     * Max 12 characters
     *
     * @return string
     */
    public function getObjectId()
    {
        if (strlen($this->objectId) > 12) {
            return substr($this->objectId, 0, 12);
        } else {
            return $this->objectId;
        }
    }

    /**
     * Returns the refernce value for update
     *
     * @return string
     */
    public function getReference()
    {
        if(empty($this->reference)){
            return 'RePack|' . $this->getObjectId() . '|';    
        } else {
            return 'On-screen Stock Adjustment';    
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
        return $this->currentQty;
    }

    /**
     * Returns the location for inserting into the database
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->whId . $this->locationId;
    }

    /**
     * Returns the sublocation for inserting into the database
     *
     * The sublocation for the UICQ transaction is empty string
     *
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }
}
