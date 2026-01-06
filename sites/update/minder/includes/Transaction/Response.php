<?php

class Transaction_Response implements Transaction_Response_Interface {
    protected $_success = false;
    protected $_message = '';

    function __construct($message)
    {
        $this->_parseMessage($message);
    }

    protected function _parseMessage($message) {
        $this->_message = $message;
        $this->_success = (false !== stripos($message, 'success'));
    }

    public function isSuccess()
    {
        return $this->_success;
    }
}