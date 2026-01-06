<?php

namespace MinderNG\Collection\Event;

use MinderNG\Collection\Collection;
use MinderNG\Events\AbstractEvent;

class Sort extends AbstractEvent {
    const EVENT_NAME = 'Sort';

    public function __construct(Collection $collection)
    {
        $this->_name = static::EVENT_NAME;
        $this->_args = func_get_args();
    }

}