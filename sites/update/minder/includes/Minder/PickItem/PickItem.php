<?php

/**
 * Class Minder_PickItem_PickItem
 *
 * @property string PICK_LABEL_NO
 * @property string PICK_LINE_STATUS
 */
class Minder_PickItem_PickItem extends ArrayObject {
    public function __construct($data = array())
    {
        $defaults = array(
            'PICK_ITEM' => '',
        );

        parent::__construct(array_merge($defaults, $data), ArrayObject::ARRAY_AS_PROPS, "ArrayIterator");
    }
}