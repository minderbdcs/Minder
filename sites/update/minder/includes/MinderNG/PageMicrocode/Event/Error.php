<?php

namespace MinderNG\PageMicrocode\Event;

use MinderNG\PageMicrocode\Error\ErrorInterface;

class ErrorEvent extends AbstractEvent {
    const EVENT_NAME = 'Error';

    function __construct(ErrorInterface $error)
    {
        $this->_name = static::EVENT_NAME;
        $this->_args = func_get_args();
    }
}