<?php
/**
 * Transaction_UILDB
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
 * Transaction_UILDB
 * ‘UILD’ will be used to Update the ISSN’s LABEL_DATE as well as Print or reprint an ISSN label.
 * This Transaction should be called at the end of RePacking a selection of ISSN’s.
 * Or the Labels are printed after RePrint of an ISSN or ReStacking, RePacking, ReSorting or
 * PrePacking of a group of ISSN’s and therefore need to be re-labelling
 * This Transaction may be called at any time and not just as part of Picking.
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_UILDB Extends Transaction
{
    /**
     * The ISSN to be updated
     *
     * @var string
     */
    public $objectId;

    /**
     * The Printer ID at which the Label is to printed
     *
     * @var string
     */
    public $printerId;

    /**
     * If >0 Indicates quantity of each ISSN Labels needs to be Printed.
     * If = 0 then not printed (because users retained original labels) but Date still updated
     *
     * @var string
     */
    public $qty;

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

    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode  = 'UILD';
        $this->transClass = 'B';
        $this->objectId   = '';
        $this->qty        = '0';
        $this->printerId  = '';
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
     * Returns the Printer ID at which the Label is to printed
     *
     * @return string
     */
    public function getReference()
    {
        return $this->printerId . '|';
    }

    /**
     * Returns the quantity for inserting into the database
     * If >0 Indicates quantity of each ISSN Labels needs to be Printed.
     * If = 0 then not printed (because users retained original labels) but Date still updated.
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
        return $this->whId . $this->locationId;
    }

    /**
     * Returns the sublocation for inserting into the database
     *
     * The sublocation for the UILD transaction is empty string
     *
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }
}
