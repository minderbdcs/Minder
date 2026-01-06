<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\Model;

/**
 * Class Table
 * @package MinderNG\PageMicrocode\Component
 *
 * @property string SST_JOIN
 * @property string SS_VARIANCE
 * @property string SST_TABLE_STATUS
 * @property string SST_TABLE
 * @property string SST_ALIAS
 * @property string SST_VIA
 */
class Table extends Model {
    const STATUS_COMMENTED = 'CM';
    const STATUS_OK = 'OK';

    const FIELD_SCREEN_NAME = 'SS_NAME';
    const FIELD_SCREEN_VARIANCE = 'SS_VARIANCE';
    const FIELD_TABLE = 'SST_TABLE';
    const FIELD_ALIAS = 'SST_ALIAS';
    const FIELD_STATUS = 'SST_TABLE_STATUS';
    const FIELD_SEQUENCE = 'SST_SEQUENCE';

    public static function calculateId(array $attributes = array())
    {
        $attributes = static::_parse($attributes);

        return implode('/', array(
            $attributes[static::FIELD_SCREEN_NAME],
            $attributes[static::FIELD_SCREEN_VARIANCE],
            $attributes[static::FIELD_ALIAS],
            $attributes[static::FIELD_STATUS],
        ));
    }

    public function parse($attributes)
    {
        return $this->_parse($attributes);
    }

    public function isStatusOk() {
        return $this->SST_TABLE_STATUS == static::STATUS_OK;
    }

    private static function _parse($attributes) {
        $attributes[static::FIELD_SCREEN_NAME] = isset($attributes[static::FIELD_SCREEN_NAME]) ? $attributes[static::FIELD_SCREEN_NAME] : '';
        $attributes[static::FIELD_TABLE] = isset($attributes[static::FIELD_TABLE]) ? $attributes[static::FIELD_TABLE] : '';
        $attributes[static::FIELD_SCREEN_VARIANCE] = empty($attributes[static::FIELD_SCREEN_VARIANCE]) ? $attributes[static::FIELD_SCREEN_NAME] : $attributes[static::FIELD_SCREEN_VARIANCE];
        $attributes[static::FIELD_ALIAS] = empty($attributes[static::FIELD_ALIAS]) ? $attributes[static::FIELD_TABLE] : $attributes[static::FIELD_ALIAS];
        $attributes[static::FIELD_STATUS] = empty($attributes[static::FIELD_STATUS]) ? static::STATUS_COMMENTED : strtoupper(trim($attributes[static::FIELD_STATUS]));

        return $attributes;
    }
}