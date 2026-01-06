<?php

namespace MinderNG\PageMicrocode\Command;

use MinderNG\PageMicrocode\Component\Form;

class ValidateForm extends Command {
    const COMMAND_NAME = 'ValidateForm';

    function __construct(Form $form)
    {
        $this->_name = static::COMMAND_NAME;
        $this->_args = func_get_args();
    }

    public function setValidationErrors(array $errors) {
        $this->setResponse($errors);
    }

    public function getValidationErrors() {
        return $this->getResponse();
    }

    public function isValid() {
        return count($this->getValidationErrors()) < 1;
    }
}