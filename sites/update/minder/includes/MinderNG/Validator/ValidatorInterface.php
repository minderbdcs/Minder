<?php

namespace MinderNG\Validator;

interface ValidatorInterface {
    /**
     * @param $value
     * @param $context
     * @return string[]
     */
    public function validate($value, $context);
}