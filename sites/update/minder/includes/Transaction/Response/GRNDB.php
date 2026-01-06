<?php

class Transaction_Response_GRNDB extends Transaction_Response {
    public $grnNo = '';
    public $load  = '';

    protected function _parseMessage($message)
    {
        parent::_parseMessage($message);

        $parts = explode(':', $message);
        $this->grnNo = isset($parts[1]) ? $parts[1] : '';
        $this->load = isset($parts[3]) ? $parts[3] : '';
    }


}