<?php

class Transaction_Response_DSOT implements Transaction_Response_Interface {
    protected $_success;
    protected $_despatchId;
    protected $_awbConsignmentNo;
    protected $_message;

    function __construct($message)
    {
        $this->_message = $message;
        $this->_parseResponseMessage($message);
    }

    protected function _parseResponseMessage($message) {
        $parts = explode('|', $message);

        $this->_parseSuccessPart($parts[0]);
        $this->_parseDespatchIdPart(isset($parts[1]) ? $parts[1] : 'PICK_DESPATCH.DESPATCH_ID=');
        $this->_parseConnoteNoPart(isset($parts[2]) ? $parts[2] : 'AWB_CONSIGNMENT_NO=');
    }

    protected function _parseSuccessPart($part) {
        $this->_success = (stripos($part, 'successfully') !== false);
    }

    protected function _parseDespatchIdPart($messagePart) {
        $parts = explode('=', $messagePart);
        $this->_despatchId = isset($parts[1]) ? $parts[1] : '';
    }

    protected function _parseConnoteNoPart($messagePart) {
        $parts = explode('=', $messagePart);
        $this->_awbConsignmentNo = isset($parts[1]) ? $parts[1] : '';
    }

    public function isSuccess()
    {
        return $this->_success;
    }

    /**
     * @return string
     */
    public function getDespatchId()
    {
        return $this->_despatchId;
    }

    /**
     * @return string
     */
    public function getAwbConsignmentNo()
    {
        return $this->_awbConsignmentNo;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }
}