<?php

namespace MinderNG\PageMicrocode\Command;

use MinderNG\PageMicrocode\Component\Form;

class UpdateForm extends Command {

    const COMMAND_NAME = 'UpdateForm';

    /**
     * UpdateForm constructor.
     * @param Form $form
     */
    public function __construct(Form $form)
    {
        $this->_name = static::COMMAND_NAME;
        $this->_args = func_get_args();
    }

}