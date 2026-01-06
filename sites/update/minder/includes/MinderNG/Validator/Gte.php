<?php

namespace MinderNG\Validator;

class Gte implements ValidatorInterface {
    /**
     * @var
     */
    private $_value;
    private $_message;

    /**
     * Gte constructor.
     * @param $value
     */
    public function __construct($value) {
        $this->_value = $value;
        $this->_message = '%FIELD_NAME% value should be not less then ' . $value . '.';
    }

    /**
     * @param $value
     * @param $context
     * @return string[]
     */
    public function validate($value, $context)
    {
        return ($value < $this->_value) ? array($this->_message) : array();
    }
}