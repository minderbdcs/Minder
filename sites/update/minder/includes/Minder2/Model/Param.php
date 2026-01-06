<?php

/**
 * @property string $DATA_ID
 * @property int $MAX_LENGTH
 * @property string $DATA_TYPE
 * @property boolean $FIXED_LENGTH
 * @property string $SYMBOLOGY_PREFIX
 * @property string $DATA_EXPRESSION
 * @property array $prefixes
 */
class Minder2_Model_Param extends Minder2_Model {
    function __get($name)
    {
        switch ($name) {
            case 'FIXED_LENGTH':
                return $this->_getBooleanFieldsValue($name);

            case 'prefixes':
                return $this->_getPrefixes();

            default:
                return parent::__get($name);
        }
    }

    function __set($name, $value)
    {
        switch ($name) {
            case 'FIXED_LENGTH':
                return $this->_setBooleanFieldValue($name, $value);

            default:
                return parent::__set($name, $value);
        }
    }

    function __isset($name)
    {
        if ($name == 'prefixes') return true;
        
        return parent::__isset($name);
    }


    protected function _getPrefixes() {
        if (is_null($this->_getFieldValue('prefixes'))) {
            $tmpPrefixes = explode(';', $this->SYMBOLOGY_PREFIX);
            if (empty($tmpPrefixes) && strlen($this->SYMBOLOGY_PREFIX) > 0)
                $tmpPrefixes = array($this->SYMBOLOGY_PREFIX); //assume single prefix without ending ';'
            else
                unset($tmpPrefixes[count($tmpPrefixes) - 1]);

            $this->prefixes = $tmpPrefixes;
        }

        return $this->_getFieldValue('prefixes');
    }
}