<?php

/**
 * Class State
 *
 * @property string $CODE
 * @property string $NAME
 */
class State extends ArrayObject {
    public function __construct()
    {
        $input = array(
            'CODE' => '',
            'NAME' => ''
        );

        parent::__construct($input, ArrayObject::ARRAY_AS_PROPS, "ArrayIterator");
    }

}