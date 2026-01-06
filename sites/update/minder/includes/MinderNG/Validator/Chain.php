<?php

namespace MinderNG\Validator;

class Chain implements ValidatorInterface {
    const CHAIN = 'Chain';
    const SET = 'Set';

    /**
     * @var
     */
    private $_validators;
    /**
     * @var bool
     */
    private $_breakOnError;

    /**
     * Chain constructor.
     * @param ValidatorInterface[] $validators
     * @param bool $breakOnError
     */
    public function __construct($validators, $breakOnError = true) {
        $this->_validators = $validators;
        $this->_breakOnError = $breakOnError;
    }

    public static function instance(Factory $factory, $validators, $breakOnError) {
        return new static(array_map(function($validator, $name) use($factory) {
            return ($validator instanceof ValidatorInterface) ? $validator : $factory->build($name, $validator);
        }, $validators, array_keys($validators)), $breakOnError);

    }

    public function validate($value, $context)
    {
        $result = array();

        foreach($this->_validators as $validator) {
            $result += $validator->validate($value, $context);

            if ($this->_breakOnError && count($result)  > 0) {
                return $result;
            }
        }

        return $result;
    }
}