<?php

class Minder_OtcProcess_State_ToolTransaction {
    public $descriptionLabel = '';
    public $prefix = '';
    public $reference = '';
    public $type = '';
    public $message = '';
    public $executed = false;
    public $error = false;

    function __construct($descriptionLabel = '', $executed = false)
    {
        $this->descriptionLabel = $descriptionLabel;
        $this->prefix = substr($descriptionLabel, 0, 2);
        $this->reference = substr($descriptionLabel, 2);
        $this->executed = $executed;
    }

    public function setError($message) {
        $this->error = true;
        $this->message = $message;
    }

    public function setMessage($message) {
        $this->message = $message;
    }
}