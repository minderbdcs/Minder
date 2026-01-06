<?php

namespace MinderNG\Validator;

class FieldSet implements ValidatorInterface {
    /**
     * @var Field[]
     */
    private $_fields;

    /**
     * FieldSet constructor.
     * @param Field[] $fields
     */
    public function __construct($fields) {
        $this->_fields = $fields;
    }

    /**
     * @param $value
     * @param $context
     * @return string[]
     */
    public function validate($value, $context)
    {
        $result = array();

        foreach ($this->_fields as $field) {
            $result += $field->validate($value[$field->getName()], $value);
        }

        return $result;
    }
}