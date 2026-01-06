<?php

class Transaction_GNNPP extends Transaction {

    public $oldGrn;
    public $oldOrderNo;
    public $oldLineNo;
    public $newOrderNo;
    public $newLineNo;
    public $qty;
    public $comment;
    public $whId;
    public $newProdId;

    public function __construct()
    {
        $this->transCode  = 'GNNP';
        $this->transClass = 'P';
    }


    /**
     * Returns the location for inserting into the database
     *
     * This function should be defined by subclasses of Transaction
     *
     * @return string
     */
    public function getObjectId()
    {
        return $this->newProdId;
    }

    /**
     * Returns the location for inserting into the database
     *
     * This function should be defined by subclasses of Transaction
     *
     * @return string
     */
    public function getLocation()
    {
        return '';
    }

    /**
     * Returns the sublocation for inserting into the database
     *
     * This function should be defined by subclasses of Transaction
     *
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }

    /**
     * Returns the reference for inserting into the database
     *
     * This function should be defined by subclasses of Transaction
     *
     * @return string
     */
    public function getReference()
    {
        return $this->oldGrn . '|'
            . $this->oldOrderNo .'|'
            . $this->oldLineNo . '|'
            . $this->newOrderNo . '|'
            . $this->newLineNo . '|'
            . $this->comment .'|';
    }

    /**
     * Returns the quantity for inserting into the database
     *
     * This function should be defined by subclasses of Transaction
     *
     * @return string
     */
    public function getQuantity()
    {
        return $this->qty;
    }
}