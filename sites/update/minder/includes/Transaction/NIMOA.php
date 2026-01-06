<?php


require_once 'Transaction.php';


class Transaction_NIMOA Extends Transaction
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
     * The SSN.MODEL Field to be updated with this value.
     *
     * @var string
     */
    public $model;

    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode      = 'NIMO';
        $this->transClass     = 'A';
        $this->ssnId       = '';
        $this->model 	  = '';
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
     * Returns the BRAND value for update
     *
     * @return string
     */
    public function getReference()
    {
        return $this->model;
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
     * The location for the NIBC transaction is empty string
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
     * The sublocation for the NIBC transaction is empty string
     *
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }
}
