<?php

namespace MinderNG\Events;

final class GenericEvent extends AbstractEvent {
    const CLASS_NAME = 'MinderNG\\Events\\GenericEvent';

    /**
     * @param $name
     * @param ... $args
     */
    function __construct($name)
    {
        $this->_args = func_get_args();
        $this->_name = array_shift($this->_args);
    }
}