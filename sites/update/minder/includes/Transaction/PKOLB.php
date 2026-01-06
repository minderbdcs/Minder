<?php

class Transaction_PKOLB extends Transaction {
    public $issnLocation = '';

    public $ssnId = '';
    public $pickQty = 0;
    public $pickLabelNo = '';

    public function __construct()
    {
        $this->transCode  = 'PKOL';
        $this->transClass = 'B';
    }

    /**
     * Returns the location for inserting into the database
     *
     * This function should be defined by subclasses of Transaction
     *
     * @return string
     */
    public function getObjectId() {
        return $this->ssnId;
    }

    /**
     * Returns the location for inserting into the database
     *
     * This function should be defined by subclasses of Transaction
     *
     * @return string
     */
    public function getLocation() {
        return $this->issnLocation;
    }

    /**
     * Returns the sublocation for inserting into the database
     *
     * This function should be defined by subclasses of Transaction
     *
     * @return string
     */
    public function getSubLocation() {
        return $this->pickLabelNo;
    }

    /**
     * Returns the reference for inserting into the database
     *
     * This function should be defined by subclasses of Transaction
     *
     * @return string
     */
    public function getReference() {
        return '';
    }

    /**
     * Returns the quantity for inserting into the database
     *
     * This function should be defined by subclasses of Transaction
     *
     * @return string
     */
    public function getQuantity() {
        return $this->pickQty;
    }

}