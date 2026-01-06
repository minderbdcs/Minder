<?php

/**
 * @property string CARRIER_ID
 * @property string SERVICE_TYPE
 * @property string ACCOUNT
 * @property string SERVICE_CHARGE_CODES
 */
class ManifestBuilder_AustPost_Model_CarrierService extends ArrayObject {
    public function __construct($input = null, $iterator_class = "ArrayIterator")
    {
        parent::__construct(empty($input) ? array() : $input, static::ARRAY_AS_PROPS, $iterator_class);
    }
}