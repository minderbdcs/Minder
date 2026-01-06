<?php

namespace MinderNG\Filter;

class SubField implements FilterInterface {
    /**
     * @var string
     */
    private $_delimiter;
    /**
     * @var int
     */
    private $_position;

    public function __construct($position = 0, $delimiter = '|') {

        $this->_delimiter = $delimiter;
        $this->_position = $position;
    }

    public function filter($value)
    {
        $subFields = explode($this->_delimiter, $value);
        return isset($subFields[$this->_position]) ? $subFields[$this->_position] : null;
    }
}