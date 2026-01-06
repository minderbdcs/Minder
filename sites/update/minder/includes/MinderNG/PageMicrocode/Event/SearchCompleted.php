<?php

namespace MinderNG\PageMicrocode\Event;

use MinderNG\PageMicrocode\Component\Screen;

class SearchCompleted extends AbstractEvent {
    const EVENT_NAME = 'SearchCompleted';

    function __construct(Screen $screen)
    {
        $this->_name = static::EVENT_NAME;
        $this->_args = func_get_args();
    }

}