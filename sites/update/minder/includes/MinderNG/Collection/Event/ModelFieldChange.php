<?php

namespace MinderNG\Collection\Event;

use MinderNG\Collection;
use MinderNG\Events\AbstractEvent;

class ModelFieldChange extends AbstractEvent {
    function __construct($fieldName, Collection\Model $model, $newValue)
    {
        $this->_args = func_get_args();
        $this->_name = self::eventName(array_shift($this->_args));
    }

    public static function eventName($fieldName) {
        return 'change:' . $fieldName;
    }
}