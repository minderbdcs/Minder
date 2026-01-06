<?php

/**
 * Class PackId_PackId
 * @property string DESPATCH_ID
 */
class PackId_PackId extends ArrayObject {
    public function __construct($input = null)
    {
        $input = array_merge(array(
            'PACK_ID' => '',
            'DESPATCH_ID' => '',
            'DESPATCH_LABEL_NO' => '',
        ), $input);
        parent::__construct($input, static::ARRAY_AS_PROPS, "ArrayIterator");
    }

}