<?php

namespace MinderNG\PageMicrocode\Command;

use MinderNG\PageMicrocode\Component\Form;

class Search extends Command {
    const COMMAND_NAME = 'Search';

    function __construct(Form $searchForm)
    {
        $this->_name = static::COMMAND_NAME;
        $this->_args = func_get_args();
    }

}