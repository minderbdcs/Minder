<?php

namespace MinderNG\Filter;

class ZeroValue implements FilterInterface {
    /**
     * @var
     */
    private $_value;

    /**
     * ZeroValue constructor.
     */
    public function __construct($value)
    {
        $this->_value = $value;
    }


    public function filter($value)
    {
        return empty($value) ? $this->_value : $value;
    }
}