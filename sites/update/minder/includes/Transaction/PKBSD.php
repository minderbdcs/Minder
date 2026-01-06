<?php

class Transaction_PKBSD extends Transaction {
    public $pickUser = '';
    public $deviceId = '';
    public $comment = '';

    public function __construct()
    {
        $this->transCode  = 'PKBS';
        $this->transClass = 'D';
    }

    /**
     * Returns the location for inserting into the database
     *
     * This function should be defined by subclasses of Transaction
     *
     * @return string
     */
    public function getObjectId() {
        return '';
    }

    /**
     * Returns the location for inserting into the database
     *
     * This function should be defined by subclasses of Transaction
     *
     * @return string
     */
    public function getLocation() {
        return '';
    }

    /**
     * Returns the sublocation for inserting into the database
     *
     * This function should be defined by subclasses of Transaction
     *
     * @return string
     */
    public function getSubLocation() {
        return '';
    }

    /**
     * Returns the reference for inserting into the database
     *
     * This function should be defined by subclasses of Transaction
     *
     * @return string
     */
    public function getReference() {
        return $this->pickUser . '|' . $this->deviceId . '|' . $this->comment;
    }

    /**
     * Returns the quantity for inserting into the database
     *
     * This function should be defined by subclasses of Transaction
     *
     * @return string
     */
    public function getQuantity() {
        return '0';
    }

}