<?php

namespace MinderNG\Collection\Event;

use MinderNG\Collection;
use MinderNG\Events\AbstractEvent;

class ModelChange extends AbstractEvent {
    const EVENT_NAME = 'change';

    function __construct(Collection\Model $model)
    {
        $this->_name = self::EVENT_NAME;
        $this->_args = func_get_args();
    }

}