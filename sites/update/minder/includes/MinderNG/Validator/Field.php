<?php

namespace MinderNG\Validator;

class Field implements ValidatorInterface {
    private $_name;
    private $_title;
    /** @var ValidatorInterface  */
    private $_validator;

    /**
     * Field constructor.
     * @param $name
     * @param $title
     * @param ValidatorInterface $validator
     */
    public function __construct($name, $title, ValidatorInterface $validator) {
        $this->_name = $name;
        $this->_title = $title;
        $this->_validator = $validator;
    }

    /**
     * @param $value
     * @param $context
     * @return string[]
     */
    public function validate($value, $context)
    {
        return $this->_formatErrors($this->_validator->validate($value, $context));
    }

    private function _formatErrors($errors)
    {
        return $errors; //todo:
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }
}