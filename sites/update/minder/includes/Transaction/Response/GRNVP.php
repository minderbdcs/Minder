<?php

class Transaction_Response_GRNVP extends Transaction_Response {
    public $issn1 = null;
    public $qtyOfLabels1 = null;
    public $qtyOnLabels1 = null;

    public $issn2 = null;
    public $qtyOfLabels2 = null;
    public $qtyOnLabels2 = null;

    public $printerId = null;

    protected function _parseMessage($message)
    {
        parent::_parseMessage($message);

        $tmpResponseArr = explode('|', $message);
        $this->issn1        = isset($tmpResponseArr[0]) ? $tmpResponseArr[0] : null;
        $this->qtyOfLabels1 = isset($tmpResponseArr[1]) ? $tmpResponseArr[1] : null;
        $this->qtyOnLabels1 = isset($tmpResponseArr[2]) ? $tmpResponseArr[2] : null;

        $this->issn2        = isset($tmpResponseArr[3]) ? $tmpResponseArr[3] : null;
        $this->qtyOfLabels2 = isset($tmpResponseArr[4]) ? $tmpResponseArr[4] : null;
        $this->qtyOnLabels2 = isset($tmpResponseArr[5]) ? $tmpResponseArr[5] : null;

        $this->printerId    = isset($tmpResponseArr[6]) ? $tmpResponseArr[6] : null;
    }


}