<?php

namespace MinderNG\PageMicrocode\Command;

use MinderNG\PageMicrocode\Component\Form;

class InitEditForm extends Command {
    const COMMAND_NAME = 'InitEditForm';

    /**
     * InitEditForm constructor.
     * @param Form $form
     */
    public function __construct(Form $form)
    {
        $this->_name = static::COMMAND_NAME;
        $this->_args = func_get_args();
    }

}