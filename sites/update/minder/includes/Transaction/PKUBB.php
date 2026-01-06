<?php

class Transaction_PKUBB extends Transaction {
    public $pickOrder = '';
    public $qty       = 0;
    public $whId      = '';
    public $locnId    = '';
    public $reference = '';

    public function __construct()
    {
        $this->transCode  = 'PKUB';
        $this->transClass = 'B';
    }

    /**
     * Returns the ISSN which should be updated
     * Max 10 characters
     *
     * @return string
     */
    public function getObjectId()
    {
        return $this->pickOrder;
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
         return $this->qty;
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
        return $this->whId . $this->locnId;
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
        return '';
    }
}