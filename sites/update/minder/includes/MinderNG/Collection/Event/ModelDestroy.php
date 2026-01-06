<?php

namespace MinderNG\Collection\Event;

use MinderNG\Collection;
use MinderNG\Events\AbstractEvent;

class ModelDestroy extends AbstractEvent {
    const EVENT_NAME = 'destroy';

    protected $_silent = false;

    function __construct(Collection\Model $model, Collection\Collection $collection = null, $silent = false)
    {
        $this->_args = func_get_args();
        $this->_name = self::EVENT_NAME;
        $this->_silent = $silent;
    }

    /**
     * @return boolean
     */
    public function silent()
    {
        return $this->_silent;
    }


}