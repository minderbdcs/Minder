<?php

namespace MinderNG\Filter;

class Substring implements FilterInterface {
    private $_start;
    private $_length;

    function __construct($start, $length = null)
    {
        $this->_start = $start;
        $this->_length = $length;
    }

    public function filter($value)
    {
        return is_null($this->_length) ? substr($value, $this->_start) : substr($value, $this->_start, $this->_length);
    }
}