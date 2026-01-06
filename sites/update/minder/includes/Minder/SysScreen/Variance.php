<?php

/**
 * Class Minder_SysScreen_Variance
 * @property string primaryScreen
 * @property string[] variances
 * @property string variancesString
 */
class Minder_SysScreen_Variance extends ArrayObject {
    const PRIMARY_SCREEN = 'primaryScreen';
    const VARIANCES = 'variances';
    const VARIANCES_STRING  ='variancesString';

    public function __construct()
    {
        $input = array(
            static::PRIMARY_SCREEN      => '',
            static::VARIANCES           => array(),
            static::VARIANCES_STRING    => '',
        );

        parent::__construct($input, ArrayObject::ARRAY_AS_PROPS, "ArrayIterator");
    }

    public function offsetGet($index)
    {
        switch ($index) {
            case (static::VARIANCES) :
                $result = explode('|', $this->variancesString);
                $result[] = $this->primaryScreen;

                return array_unique($result);

            default:
                return parent::offsetGet($index);
        }

    }


}