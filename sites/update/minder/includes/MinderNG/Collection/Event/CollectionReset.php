<?php

namespace MinderNG\Collection\Event;

use MinderNG\Collection;
use MinderNG\Events\AbstractEvent;

class CollectionReset extends AbstractEvent {
    const EVENT_NAME = 'reset';

    function __construct(Collection\Collection $collection, $previousModels = null)
    {
        $this->_args = func_get_args();
        $this->_name = self::EVENT_NAME;
    }


}