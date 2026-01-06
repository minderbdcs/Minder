<?php

/**
 * @property string D_TITLE
 * @property string D_FIRST_NAME
 * @property string D_LAST_NAME
 * @property string P_TITLE
 * @property string P_FIRST_NAME
 * @property string P_LAST_NAME
 * @property string P_ADDRESS_LINE1
 * @property string P_ADDRESS_LINE2
 * @property string P_ADDRESS_LINE3
 * @property string P_ADDRESS_LINE4
 * @property string P_ADDRESS_LINE5
 * @property string D_ADDRESS_LINE1
 * @property string D_ADDRESS_LINE2
 * @property string D_ADDRESS_LINE3
 * @property string D_ADDRESS_LINE4
 * @property string D_ADDRESS_LINE5
 * @property string P_POST_CODE
 * @property string P_CITY
 * @property string P_STATE
 * @property string D_POST_CODE
 * @property string D_CITY
 * @property string D_STATE
 *
 */
class ManifestBuilder_Model_DeliveryAddress extends ArrayObject
{
    public function __construct($input = null, $iterator_class = "ArrayIterator")
    {
        parent::__construct($input, static::ARRAY_AS_PROPS, $iterator_class);
    }

    public function getName() {
        $title      = empty($this->D_FIRST_NAME) ? $this->P_TITLE : $this->D_TITLE;
        $firstName  = empty($this->D_FIRST_NAME) ? $this->P_FIRST_NAME : $this->D_FIRST_NAME;
        $lastName   = empty($this->D_FIRST_NAME) ? $this->P_LAST_NAME : $this->D_LAST_NAME;

        return empty($title) ? $firstName : $title . ' ' . $firstName . ' ' . $lastName;
    }

    public function getAddress() {
        return empty($this->D_FIRST_NAME) ?
            trim($this->P_ADDRESS_LINE1) . ' ' . trim($this->P_ADDRESS_LINE2) . ' ' . trim($this->P_ADDRESS_LINE3) . ' ' . trim($this->P_ADDRESS_LINE4) . ' ' . trim($this->P_ADDRESS_LINE5) :
            trim($this->D_ADDRESS_LINE1) . ' ' . trim($this->D_ADDRESS_LINE2) . ' ' . trim($this->D_ADDRESS_LINE3) . ' ' . trim($this->D_ADDRESS_LINE4) . ' ' . trim($this->D_ADDRESS_LINE5);
    }

    public function getPostCode() {
        return empty($this->D_FIRST_NAME) ? $this->P_POST_CODE : $this->D_POST_CODE;
    }

    public function getState() {
        return empty($this->D_FIRST_NAME) ? $this->P_STATE : $this->D_STATE;
    }

    public function getSuburb() {
        return empty($this->D_FIRST_NAME) ? $this->P_CITY : $this->D_CITY;
    }
}
