<?php

/**
 * Class Minder_SysScreen_InheritanceSettings
 *
 * @property bool $CUSTOM_TABLES
 * @property string $settingsString
 */
class Minder_SysScreen_InheritanceSettings extends ArrayObject {
    const CUSTOM_TABLES  = 'CUSTOM_TABLES';
    const SETTINGS_STRING = 'settingsString';

    public function __construct()
    {
        $input = array(
            static::CUSTOM_TABLES      => false,
            static::SETTINGS_STRING    => '',
        );

        parent::__construct($input, ArrayObject::ARRAY_AS_PROPS, "ArrayIterator");
    }


    public function offsetSet($index, $newval)
    {
        switch ($index) {

            case (static::SETTINGS_STRING) :
                $this->_setSettingsString($newval);
                break;

            default:
                parent::offsetSet($index, $newval);
        }
    }

    protected function _setSettingsString($value) {
        foreach (explode('|', $value) as $partString) {
            $keyValue = explode('=', $partString);
            $key = strtoupper($keyValue[0]);
            $value = isset($keyValue[1]) ? strtoupper($keyValue[1]) : 'T';

            $this->offsetSet($key, $value === 'T');
        }
    }
}