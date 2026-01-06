<?php

/**
 * Class Minder_PackSscc_PackSscc
 *
 * @property string PS_PICK_ORDER
 * @property string PS_PICK_ORDER_LINE_NO
 * @property string PS_PICK_LABEL_NO
 * @property string PS_SSCC
 * @property string PS_SSCC_STATUS
 * @property string PS_DEL_TO_DC_NO
 */
class Minder_PackSscc_PackSscc extends ArrayObject {
    public function __construct($data = array())
    {
        $defaults = array(
            'RECORD_ID' => '',
        );

        parent::__construct(array_merge($defaults, $data), ArrayObject::ARRAY_AS_PROPS, "ArrayIterator");
    }

}