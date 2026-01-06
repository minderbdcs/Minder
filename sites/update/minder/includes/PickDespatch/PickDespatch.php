<?php

/**
 * Class PickDespatch_PickDespatch
 * @property string PICKD_CARRIER_ID
 * @property string PICKD_EXIT
 * @property string AWB_CONSIGNMENT_NO
 * @property string PICKD_ADDRESS_QTY
 */
class PickDespatch_PickDespatch extends ArrayObject {
    public function __construct($input)
    {
        $input = array_merge(array(), $input);

        parent::__construct($input, static::ARRAY_AS_PROPS, "ArrayIterator");
    }

}