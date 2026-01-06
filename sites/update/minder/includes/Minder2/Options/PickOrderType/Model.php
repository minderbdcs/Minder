<?php

/**
 * Class Minder2_Options_PickOrderType_Model
 *
 * @property string $orderSubType
 * @property string $type
 */
class Minder2_Options_PickOrderType_Model extends ArrayObject {
    const EDI = 'EDI';
    const GENERAL_ORDER = 'GO';

    public function __construct($orderSubType, $type)
    {
        $input = array(
            'orderSubType' => strtoupper($orderSubType),
            'type' => strtoupper($type),
        );

        parent::__construct($input, static::ARRAY_AS_PROPS, "ArrayIterator");
    }

    public function isEdi() {
        return $this->type === static::EDI;
    }

}