<?php

/**
 * @property string DESPATCH_LABEL_NO
 * @property string PACK_TYPE
 */
class ManifestBuilder_Model_Item extends ArrayObject {
    public function __construct($input = null, $iterator_class = "ArrayIterator")
    {
        parent::__construct($input, static::ARRAY_AS_PROPS, $iterator_class);
    }

}