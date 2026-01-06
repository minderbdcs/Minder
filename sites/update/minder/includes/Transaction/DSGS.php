<?php

abstract class Transaction_DSGS extends Transaction {
    public $SSCC = '';
    public $status = 'CL';
    public $length = 0;
    public $height = 0;
    public $width  = 0;
    public $volume = 0;
    public $weight = 0;
    public $totalOuters = 1;
    public $dimUom = '';
    public $volumeUom = '';
    public $weightUom = '';
    public $packType = '';
    public $outerBarcode = '';
    public $labelPrinter = '';
    public $scannedItems = 1;
    public $despatchPCLocnId = '';

    public function getType()
    {
        return 'DSGS';
    }

    /**
     * @return string
     */
    public function getObjectId()
    {
        return $this->SSCC;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return implode('|', array(
            '',
            $this->status,
            number_format($this->length, 3, '.', ''),
            number_format($this->width, 3, '.', ''),
            number_format($this->height, 3, '.', ''),
            number_format($this->volume, 3, '.', ''),
            number_format($this->weight, 3, '.', ''),
            $this->totalOuters,
            $this->dimUom,
            $this->volumeUom,
            $this->weightUom,
            $this->packType,
            $this->outerBarcode,
            'SYS_EQUIP.DEVICE_ID=' . $this->labelPrinter,
            '',
        ));
    }

    /**
     * @return string
     */
    public function getQuantity()
    {
        return $this->scannedItems;
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
        return $this->despatchPCLocnId;
    }

    /**
     * @return string
     */
    public function getSubLocation()
    {
        return '';
    }

    /**
     * @param string $transactionResponse
     * @return Transaction_Response_DSGS|Transaction_Response_Interface
     */
    public function parseResponse($transactionResponse)
    {
        return new Transaction_Response_DSGS($transactionResponse, $this->SSCC);
    }


}