<?php

namespace MinderNG\Collection\Helper;

use Iterator;

class CallbackFilterIterator extends \FilterIterator {
    private $_callback;

    public function __construct(Iterator $iterator, $callback)
    {
        $this->_callback = $callback;
        parent::__construct($iterator);
    }


    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Check whether the current element of the iterator is acceptable
     * @link http://php.net/manual/en/filteriterator.accept.php
     * @return bool true if the current element is acceptable, otherwise false.
     */
    public function accept()
    {
        return call_user_func($this->_callback, parent::current(), parent::key(), parent::getInnerIterator());
    }
}