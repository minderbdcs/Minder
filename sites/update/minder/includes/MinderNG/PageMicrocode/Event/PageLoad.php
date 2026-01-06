<?php

namespace MinderNG\PageMicrocode\Event;

class PageLoad extends AbstractEvent {
    const EVENT_NAME = 'pageLoad';

    function __construct(\Minder2_Environment $environment)
    {
        $this->_name = static::EVENT_NAME;
        $this->_args = func_get_args();
    }

}