<?php

class Transaction_PKILD extends Transaction {
    public $directDeliveryLocation = '';
    public $pickLabelNo = '';
    public $userId = '';
    public $pickDevice = '';
    public $comment = '';

    public function __construct()
    {
        $this->transCode  = 'PKIL';
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
        return $this->pickLabelNo;
    }

    /**
     * Returns the location for inserting into the database
     *
     * This function should be defined by subclasses of Transaction
     *
     * @return string
     */
    public function getLocation() {
        return $this->directDeliveryLocation;
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
        return $this->userId . '|' . $this->pickDevice . '|' . $this->comment;
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