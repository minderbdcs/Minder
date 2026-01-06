<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\Model;

/**
 * Class DataSourceField
 * @package MinderNG\PageMicrocode\Component
 *
 * @property string SSV_ALIAS
 * @property string SSV_EXPRESSION
 */
class DataSourceField extends Model {
    public static function calculateId(array $attributes = array())
    {
        $attributes = static::_parse($attributes);

        $parts = array(
            isset($attributes['SS_NAME']) ? $attributes['SS_NAME'] : '',
            isset($attributes['SS_VARIANCE']) ? $attributes['SS_VARIANCE'] : '',
            isset($attributes['SSV_ALIAS']) ? $attributes['SSV_ALIAS'] : '',
            isset($attributes['SSV_FIELD_STATUS']) ? $attributes['SSV_FIELD_STATUS'] : '',
        );

        return implode('/', $parts);
    }

    public function parse($attributes)
    {
        return $this->_parse($attributes);
    }

    private static function _parse($attributes) {

        if (empty($attributes['SS_VARIANCE'])) {
            $attributes['SS_VARIANCE'] = isset($attributes['SS_NAME']) ? $attributes['SS_NAME'] : '';
        }

        if (empty($attributes['SSV_ALIAS'])) {
            $attributes['SSV_ALIAS'] = static::_fullName($attributes, '_');
        }

        $attributes['SSV_FIELD_STATUS'] = empty($attributes['SSV_FIELD_STATUS']) ? 'CM' : strtoupper(trim($attributes['SSV_FIELD_STATUS']));
        $attributes['SSV_PRIMARY_ID'] = empty($attributes['SSV_PRIMARY_ID']) ? 'F' : strtoupper(trim($attributes['SSV_PRIMARY_ID']));

        return $attributes;
    }

    static private function _fullName($attributes, $separator = '.') {
        return isset($attributes['SSV_TABLE']) ? $attributes['SSV_TABLE'] . $separator . $attributes['SSV_NAME'] : $attributes['SSV_NAME'];
    }
}