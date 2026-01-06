<?php

namespace MinderNG\Collection\Event;

use MinderNG\Collection;
use MinderNG\Events\AbstractEvent;

class ModelRemove extends AbstractEvent {
    const EVENT_NAME = 'remove';

    function __construct(Collection\ModelAggregateInterface $model, Collection\Collection $collection = null, $index = null)
    {
        $this->_args = func_get_args();
        $this->_name = self::EVENT_NAME;
    }


}