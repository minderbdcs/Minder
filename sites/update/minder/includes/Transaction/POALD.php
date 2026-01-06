<?php

class Transaction_POALD extends Transaction {
    public $trolleyDevice = '';
    public $orderNo = '';
    public $pickUser = '';
    public $allocatedDevice = '';
    public $comment = '';
    public $pickOrderWhId = '';

    public function __construct()
    {
        $this->transCode  = 'POAL';
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
        return substr($this->pickOrderWhId, 0, 2) . '        ';
    }

    /**
     * Returns the sublocation for inserting into the database
     *
     * This function should be defined by subclasses of Transaction
     *
     * @return string
     */
    public function getSubLocation() {
        return $this->trolleyDevice;
    }

    /**
     * Returns the reference for inserting into the database
     *
     * This function should be defined by subclasses of Transaction
     *
     * @return string
     */
    public function getReference() {
        return $this->pickUser . '|' . $this->allocatedDevice . '|' . $this->comment;
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