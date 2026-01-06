<?php

class Transaction_TRPKo extends Transaction {

    public $orderNo = '';
    public $fromDevice = '';
    public $comment = '';
    public $toDevice = '';

    public function __construct()
    {
        $this->transCode  = 'TRPK';
        $this->transClass = 'o';
    }

    /**
     * Returns the location for inserting into the database
     *
     * This function should be defined by subclasses of Transaction
     *
     * @return string
     */
    public function getObjectId() {
        return $this->orderNo;
    }

    /**
     * Returns the location for inserting into the database
     *
     * This function should be defined by subclasses of Transaction
     *
     * @return string
     */
    public function getLocation() {
        return $this->fromDevice;
    }

    /**
     * Returns the sublocation for inserting into the database
     *
     * This function should be defined by subclasses of Transaction
     *
     * @return string
     */
    public function getSubLocation() {
        return $this->toDevice;
    }

    /**
     * Returns the reference for inserting into the database
     *
     * This function should be defined by subclasses of Transaction
     *
     * @return string
     */
    public function getReference() {
        return $this->comment;
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