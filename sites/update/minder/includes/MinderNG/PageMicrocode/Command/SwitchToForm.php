<?php

namespace MinderNG\PageMicrocode\Command;

use MinderNG\PageMicrocode\Component\Form;

class SwitchToForm extends Command {

    const COMMAND_NAME = '';

    function __construct(Form $form)
    {
        $this->_name = static::COMMAND_NAME;
        $this->_args = func_get_args();
    }

}