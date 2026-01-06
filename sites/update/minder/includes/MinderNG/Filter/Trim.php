<?php

namespace MinderNG\Filter;

class Trim implements FilterInterface {
    function __construct($charList = " \t\n\r\0\x0B")
    {
        $this->_charList = $charList;
    }

    public function filter($value)
    {
        return trim($value, $this->_charList);
    }
}