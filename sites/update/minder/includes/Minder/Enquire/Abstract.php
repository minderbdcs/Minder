<?php

/**
 * Class Minder_Enquire_Enquire
 * @property string dataType
 * @property string primaryScreen
 * @property string description
 * @property string screens
 * @property string[] screensArray
 */
class Minder_Enquire_Abstract extends ArrayObject {
    const SCREENS = 'screens';
    const SCREENS_ARRAY = 'screensArray';
    const PRIMARY_SCREEN = 'primaryScreen';

    public function __construct()
    {
        $input = array(
            'dataType' => '',
            static::PRIMARY_SCREEN => '',
            'description' => '',
            static::SCREENS => '',
        );

        parent::__construct($input, ArrayObject::ARRAY_AS_PROPS, "ArrayIterator");
    }

    public function offsetSet($index, $newval)
    {
        switch ($index) {
            case (static::SCREENS):
                parent::offsetSet(static::SCREENS_ARRAY, explode('|', $newval));
                list($primaryScreen) = parent::offsetGet(static::SCREENS_ARRAY);
                parent::offsetSet(static::PRIMARY_SCREEN, $primaryScreen);
                parent::offsetSet($index, $newval);
                break;
            default:
                parent::offsetSet($index, $newval);
        }
    }


}