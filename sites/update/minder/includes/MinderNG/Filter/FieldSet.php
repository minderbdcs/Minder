<?php

namespace MinderNG\Filter;

class FieldSet implements FilterInterface {
    /**
     * @var Field[]
     */
    private $fields;

    /**
     * @param Field[] $fields
     */
    function __construct($fields)
    {
        $this->fields = $fields;
    }


    public function filter($value)
    {
        foreach ($this->fields as $field) {
            $value[$field->getName()] = $field->filter($value);
        }

        return $value;
    }
}