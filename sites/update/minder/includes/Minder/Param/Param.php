<?php

/**
 * Class Minder_Param_Param
 * @property string DATA_ID
 * @property string DATA_EXPRESSION
 */
class Minder_Param_Param extends ArrayObject {
    public function __construct($input)
    {
        $input = array_replace(array(
            'DATA_ID' => '',
            'DATA_EXPRESSION' => ''
        ), $input);

        parent::__construct($input, static::ARRAY_AS_PROPS, 'ArrayIterator');
    }

    public function isValid($value) {
        return empty($this->DATA_EXPRESSION) || (preg_match('/' . $this->DATA_EXPRESSION . '/', $value) === 1);
    }
}