<?php

/**
 * @property string CARRIER_ID
 * @property string ACCOUNT
 * @property string CUSTOMER_CODE
 * @property string FTP_USER
 * @property string FTP_MANIFEST_GENERATOR
 * @property string FTP_MANIFEST_START_NO
 * @property string FTP_MANIFEST_END_NO
 * @property string FTP_MANIFEST_PREFIX
 * @property string FTP_ID_GENERATOR
 */
class ManifestBuilder_Model_Carrier extends ArrayObject {
    public function __construct($input = null, $iterator_class = "ArrayIterator")
    {
        parent::__construct($input, static::ARRAY_AS_PROPS, $iterator_class);
    }


}