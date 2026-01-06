<?php

/**
 * @property string CARRIER_ID
 * @property string CONNOTE_EXPORT_METHOD
 */
class ManifestBuilder_Model_CarrierOutputFormat extends ArrayObject {
    public function __construct($input = null, $iterator_class = "ArrayIterator")
    {
        parent::__construct($input, static::ARRAY_AS_PROPS, $iterator_class);
    }


}