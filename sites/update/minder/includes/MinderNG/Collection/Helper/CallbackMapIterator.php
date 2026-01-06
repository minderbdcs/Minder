<?php

namespace MinderNG\Collection\Helper;

use Traversable;

class CallbackMapIterator extends \IteratorIterator {
    private $_callback;

    public function __construct(Traversable $iterator, $callback)
    {
        $this->_callback = $callback;
        parent::__construct($iterator);
    }

    public function current()
    {
        return call_user_func($this->_callback, parent::current(), parent::key(), parent::getInnerIterator());
    }
}