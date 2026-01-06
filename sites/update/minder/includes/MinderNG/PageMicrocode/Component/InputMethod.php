<?php

namespace MinderNG\PageMicrocode\Component;

class InputMethod {
    const FIELD_METHOD              = 'METHOD';
    const FIELD_FILTER              = 'FILTER';
    const FIELD_VALIDATOR           = 'VALIDATOR';
    const FIELD_DECORATOR           = 'DECORATOR';
    const FIELD_EXTENSION           = 'EXTENSION';

    public $method;
    /**
     * @var array
     */
    public $filter;
    /**
     * @var array
     */
    public $validator;
    /**
     * @var array
     */
    public $decorator;
    /**
     * @var array
     */
    public $extension;

    /**
     * InputMethod constructor.
     * @param $method
     * @param array $filter
     * @param array $validator
     * @param array $decorator
     * @param array $extension
     */
    public function __construct($method, array $filter, array $validator, array $decorator, array $extension)
    {
        $this->method = $method;
        $this->filter = $filter;
        $this->validator = $validator;
        $this->decorator = $decorator;
        $this->extension = $extension;
    }

    public static function fromJSON($inputMethodString) {
        $validator =  array();
        $filter =  array();
        $decorator =  array();
        $extension = array();

        $method = json_decode($inputMethodString, true);

        if (is_array($method)) {
            $methodParts = array_change_key_case($method, CASE_UPPER);

            $method = isset($methodParts[static::FIELD_METHOD]) ? trim($methodParts[static::FIELD_METHOD]) : '';

            $validator = isset($methodParts[static::FIELD_VALIDATOR]) ? $methodParts[static::FIELD_VALIDATOR] : array();
            $filter = isset($methodParts[static::FIELD_FILTER]) ? $methodParts[static::FIELD_FILTER] : array();
            $decorator = isset($methodParts[static::FIELD_DECORATOR]) ? $methodParts[static::FIELD_DECORATOR] : array();
            $extension = isset($methodParts[static::FIELD_EXTENSION])  ? $methodParts[static::FIELD_EXTENSION] : array();
        } elseif (is_null($method)) {
            $method = $inputMethodString;
        }

        return new static($method, $filter, $validator, $decorator, $extension);
    }
}