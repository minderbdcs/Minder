<?php

/**
 * Class Depot
 *
 * @property string  $RECORD_ID
 * @property string  $CD_CARRIER_ID
 * @property string  $CD_DEPOT_ID
 * @property string  $CD_STATE;
 * @property string  $CD_DESCRIPTION
 * @property integer $CD_SEQUENCE
 * @property string  $CD_DEPOT_STATUS
 * @property string  $CD_NOTES
 */
class Depot extends ArrayObject {
    public function __construct($input = array())
    {
        $input = array_merge(array(
            'RECORD_ID'         => '',
            'CD_CARRIER_ID'     => '',
            'CD_DEPOT_ID'       => '',
            'CD_STATE'          => '',
            'CD_DESCRIPTION'    => '',
            'CD_SEQUENCE'       => 0,
            'CD_DEPOT_STATUS'   => '',
            'CD_NOTES'          => '',
        ), $input);
        parent::__construct($input, ArrayObject::ARRAY_AS_PROPS, "ArrayIterator");
    }

}