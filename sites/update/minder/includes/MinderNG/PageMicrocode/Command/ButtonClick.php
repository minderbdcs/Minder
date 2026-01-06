<?php

namespace MinderNG\PageMicrocode\Command;

use MinderNG\PageMicrocode\Component\Button;
use MinderNG\PageMicrocode\Component\Form;

class ButtonClick extends Command {
    const COMMAND_NAME = 'ButtonClick';


    function __construct(Form $form, Button $button)
    {
        $this->_name = static::COMMAND_NAME;
        $this->_args = func_get_args();
    }


}