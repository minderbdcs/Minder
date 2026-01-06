<?php

class Minder_JSResponse extends stdClass {
    public $errors   = array();
    public $warnings = array();
    public $messages = array();

    public function hasErrors() {
        return count($this->errors) > 0;
    }

    public function addMessages($messages) {
        $messages = is_array($messages) ? $messages : array($messages);
        $this->messages = array_merge($this->messages, $messages);
    }

    public function addErrors($messages) {
        $messages = is_array($messages) ? $messages : array($messages);
        $this->errors = array_merge($this->errors, $messages);
    }

    public function addWarnings($messages) {
        $messages = is_array($messages) ? $messages : array($messages);
        $this->warnings = array_merge($this->warnings, $messages);
    }

    public function merge(Minder_JSResponse $anotherResponse) {
        $this->addMessages($anotherResponse->messages);
        $this->addWarnings($anotherResponse->warnings);
        $this->addErrors($anotherResponse->errors);
    }
}
