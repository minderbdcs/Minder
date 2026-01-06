<?php

/**
 * @property string PERSON_ID
 * @property string ADDRESS_LINE1
 * @property string ADDRESS_LINE2
 * @property string ADDRESS_LINE3
 * @property string ADDRESS_LINE4
 * @property string ADDRESS_LINE5
 * @property string POST_CODE
 * @property string STATE
 * @property string COUNTRY
 * @property string CITY
 */
class ManifestBuilder_Model_Company extends ArrayObject
{
    public function __construct($input = null, $iterator_class = "ArrayIterator")
    {
        parent::__construct($input, static::ARRAY_AS_PROPS, $iterator_class);
    }

    public function getAddress() {
        return trim($this->ADDRESS_LINE1) .' ' . trim($this->ADDRESS_LINE2) .' ' . trim($this->ADDRESS_LINE3) . ' ' . trim($this->ADDRESS_LINE4) .' ' . trim($this->ADDRESS_LINE5);
    }
}
