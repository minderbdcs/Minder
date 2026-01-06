<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\Model;

/**
 * Class Tab
 * @package MinderNG\PageMicrocode\Component
 *
 * @property string SS_NAME
 * @property string SS_VARIANCE
 * @property string SSF_NAME
 * @property string SST_FIELD_TYPE
 * @property string SST_TAB_NAME
 * @property string SST_TAB_STATUS
 * @property boolean active
 */
class Tab extends Model {

    const DEFAULT_NAME = 'DEFAULT';

    const FIELD_SCREEN_NAME = 'SS_NAME';
    const FIELD_VARIANCE = 'SS_VARIANCE';
    const FIELD_FORM_NAME = 'SSF_NAME';
    const FIELD_TYPE = 'SST_FIELD_TYPE';
    const FIELD_TAB_NAME = 'SST_TAB_NAME';
    const FIELD_STATUS = 'SST_TAB_STATUS';
    const FIELD_ACTIVE = 'active';

    const TYPE_SEARCH_RESULT = 'SR';
    const STATUS_COMMENTED = 'CM';
    const STATUS_OK = 'OK';

    public static function parseId($id) {
        $parts = explode('/', $id);

        return array_combine(array(
            static::FIELD_SCREEN_NAME,
            static::FIELD_VARIANCE,
            static::FIELD_TYPE,
            static::FIELD_FORM_NAME,
            static::FIELD_TAB_NAME,
            static::FIELD_STATUS,
        ), $parts);
    }

    public static function calculateId(array $attributes = array())
    {
        $attributes = static::_parse($attributes);

        return implode(array(
            $attributes[static::FIELD_SCREEN_NAME],
            $attributes[static::FIELD_VARIANCE],
            $attributes[static::FIELD_TYPE],
            $attributes[static::FIELD_FORM_NAME],
            $attributes[static::FIELD_TAB_NAME],
            $attributes[static::FIELD_STATUS],
        ), '/');
    }

    public function parse($attributes)
    {
        return static::_parse($attributes);
    }

    private static function _parse($attributes) {
        $attributes[static::FIELD_SCREEN_NAME] = isset($attributes[static::FIELD_SCREEN_NAME]) ? $attributes[static::FIELD_SCREEN_NAME] : '';
        $attributes[static::FIELD_FORM_NAME] = isset($attributes[static::FIELD_FORM_NAME]) ? $attributes[static::FIELD_FORM_NAME] : '';
        $attributes[static::FIELD_TAB_NAME] = isset($attributes[static::FIELD_TAB_NAME]) ? $attributes[static::FIELD_TAB_NAME] : '';
        $attributes[static::FIELD_TYPE] = isset($attributes[static::FIELD_TYPE]) ? strtoupper(trim($attributes[static::FIELD_TYPE]))  : static::TYPE_SEARCH_RESULT;
        $attributes[static::FIELD_STATUS] = isset($attributes[static::FIELD_STATUS]) ? strtoupper(trim($attributes[static::FIELD_STATUS]))  : static::STATUS_COMMENTED;
        $attributes[static::FIELD_VARIANCE] = empty($attributes[static::FIELD_VARIANCE]) ? $attributes[static::FIELD_SCREEN_NAME] : $attributes[static::FIELD_VARIANCE];
        $attributes[static::FIELD_ACTIVE] = isset($attributes[static::FIELD_ACTIVE]) ? !!$attributes[static::FIELD_ACTIVE] : false;

        return $attributes;
    }
}