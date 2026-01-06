<?php

/**
 * Class Minder_OtcProcess_LoanDefaults
 * @property string TOOL_TYPE
 * @property string TYPE
 * @property string CHECKED
 * @property string CHECK_DATE
 * @property string PERIOD_NO
 * @property string PERIOD
 */
class Minder_OtcProcess_LoanDefaults extends ArrayObject {
    public function __construct($input = null, $iterator_class = "ArrayIterator")
    {
        $input = empty($input) ? $this->_getDefaults() : $input;

        parent::__construct($input, ArrayObject::ARRAY_AS_PROPS, $iterator_class);
    }

    private function _getDefaults()
    {
        return array(
            'TOOL_TYPE' => '',
            'TYPE' => '',
            'CHECKED' => false,
            'CHECK_DATE' => '',
            'PERIOD_NO' => 6,
            'PERIOD' => 'M'
        );
    }


}