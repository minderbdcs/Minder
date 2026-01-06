<?php

namespace MinderNG\PageMicrocode\Error;

class FormNotFound implements ErrorInterface {
    private $_message;

    function __construct($formName)
    {
        $this->_message = $formName . ' not found.';
    }

    public function getMessage()
    {
        return $this->_message;
    }

    public function getCode()
    {
        return ErrorInterface::FORM_NOT_FOUND;
    }
}