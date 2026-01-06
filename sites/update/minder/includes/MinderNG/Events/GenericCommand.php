<?php

namespace MinderNG\Events;

final class GenericCommand extends AbstractCommand {
    const CLASS_NAME = 'MinderNG\\Events\\GenericCommand';

    function __construct($commandName)
    {
        $args = func_get_args();
        $this->_name = array_shift($args);
        $this->_args = $args;
    }

}