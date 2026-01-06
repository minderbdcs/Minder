<?php

namespace MinderNG\PageMicrocode\Component\SearchSpecification;

class EqualTo {
    private $_expression;
    private $_value;

    function __construct($expression, $value)
    {
        $this->_expression = $expression;
        $this->_value = $value;
    }

    /**
     * @return mixed
     */
    public function getExpression()
    {
        return $this->_expression;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->_value;
    }

}