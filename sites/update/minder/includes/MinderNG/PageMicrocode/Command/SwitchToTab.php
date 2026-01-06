<?php

namespace MinderNG\PageMicrocode\Command;

use MinderNG\PageMicrocode\Component;

class SwitchToTab extends Command {
    const COMMAND_NAME = 'SwitchToTab';

    function __construct(Component\Form $form, Component\Tab $tab)
    {
        $this->_name = static::COMMAND_NAME;
        $this->_args = func_get_args();
    }

}