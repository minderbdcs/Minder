<?php

class Minder_Process_ImportMappedOrders_Status {
    protected $_proceed = 0;
    protected $_skipped = 0;
    protected $_inserted = 0;
    protected $_error = false;
    protected $_errorMessage = '';

    public function skip() {
        $this->_skipped++;
    }

    public function success($insertedRecords) {
        $this->_proceed++;
        $this->_inserted += $insertedRecords;
    }

    public function error($message) {
        $this->_error = true;
        $this->_errorMessage = $message;
    }

    public function isError() {
        return $this->_error;
    }

    public function getProceed() {
        return $this->_proceed;
    }

    public function getSkipped() {
        return $this->_skipped;
    }

    public function getInserted() {
        return $this->_inserted;
    }

    public function getErrorMessage() {
        return $this->_errorMessage;
    }
}