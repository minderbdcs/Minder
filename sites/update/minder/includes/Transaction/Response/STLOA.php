<?php

class Transaction_Response_STLOA implements Transaction_Response_Interface {
    protected $_success = false;
    protected $_issnQty = 0;

    function __construct($message)
    {
        $this->_message = $message;
        $this->_parseResponse($message);
    }

    protected function _parseResponse($message) {
        $this->_success = (false !== stripos($message, 'processed successfully'));
        $parts = explode('|', $message);

        $this->_issnQty = isset($parts[1]) ? intval($parts[1]) : 0;
    }

    public function getMessage() {
        return $this->_message;
    }

    public function isSuccess()
    {
        return $this->_success;
    }

    public function getIssnQty() {
        return $this->_issnQty;
    }
}