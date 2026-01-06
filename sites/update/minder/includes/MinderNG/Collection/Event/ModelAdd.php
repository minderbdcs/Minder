<?php

namespace MinderNG\Collection\Event;

use MinderNG\Events\AbstractEvent;
use MinderNG\Collection;

class ModelAdd extends AbstractEvent {
    const EVENT_NAME = 'add';

    function __construct(Collection\ModelAggregateInterface $model, Collection\Collection $collection = null)
    {
        $this->_args = func_get_args();
        $this->_name = self::EVENT_NAME;
    }


}