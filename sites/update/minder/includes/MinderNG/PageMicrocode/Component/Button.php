<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\Model;

/**
 * Class Button
 * @package MinderNG\PageMicrocode\Component
 *
 * @property string SSB_ACTION
 * @property string INTERNAL_HANDLER
 * @property int SSB_SEQUENCE
 */
class Button extends Model {
    const FIELD_SCREEN_NAME = 'SS_NAME';
    const FIELD_FORM_NAME = 'SSF_NAME';
    const FIELD_TYPE = 'SSB_BUTTON_TYPE';
    const FIELD_STATUS = 'SSB_TAB_STATUS';
    const FIELD_NAME = 'SSB_BUTTON_NAME';
    const FIELD_ACTION = 'SSB_ACTION';
    const FIELD_INTERNAL_HANDLER = 'INTERNAL_HANDLER';
    const FIELD_TITLE = 'SSB_TITLE';
    const FIELD_SEQUENCE = 'SSB_SEQUENCE';

    const STATUS_OK = 'OK';
    const STATUS_COMMENTED = 'CM';

    const ID_DELIMITER = '/';

    const BUTTON_SAVE = '__SAVE__';
    const BUTTON_RESET = '__RESET__';

    public static function parseId($id) {
        return array_combine(array(
            static::FIELD_SCREEN_NAME,
            static::FIELD_TYPE,
            static::FIELD_FORM_NAME,
            static::FIELD_STATUS,
            static::FIELD_NAME,
        ), explode(static::ID_DELIMITER, $id));
    }

    public static function calculateId(array $attributes = array())
    {
        $attributes = static::_parse($attributes);

        return implode(static::ID_DELIMITER, array(
            $attributes[static::FIELD_SCREEN_NAME],
            $attributes[static::FIELD_TYPE],
            $attributes[static::FIELD_FORM_NAME],
            $attributes[static::FIELD_STATUS],
            $attributes[static::FIELD_NAME],
        ));
    }

    /**
     * @param $attributes
     * @return array
     */
    public function parse($attributes)
    {
        return static::_parse($attributes);
    }

    private static function _parse($attributes) {
        $attributes[static::FIELD_SCREEN_NAME] = isset($attributes[static::FIELD_SCREEN_NAME]) ? $attributes[static::FIELD_SCREEN_NAME] : '';
        $attributes[static::FIELD_FORM_NAME] = isset($attributes[static::FIELD_FORM_NAME]) ? $attributes[static::FIELD_FORM_NAME] : '';
        $attributes[static::FIELD_NAME] = isset($attributes[static::FIELD_NAME]) ? $attributes[static::FIELD_NAME] : '';

        $attributes[static::FIELD_TYPE] = isset($attributes[static::FIELD_TYPE]) ? strtoupper(trim($attributes[static::FIELD_TYPE])) : '';
        $attributes[static::FIELD_TYPE] = empty($attributes[static::FIELD_TYPE]) ? Form::SEARCH_RESULT_FORM : $attributes[static::FIELD_TYPE];

        $attributes[static::FIELD_STATUS] = isset($attributes[static::FIELD_STATUS]) ? strtoupper(trim($attributes[static::FIELD_STATUS])) : '';
        $attributes[static::FIELD_STATUS] = empty($attributes[static::FIELD_STATUS]) ? static::STATUS_COMMENTED : $attributes[static::FIELD_STATUS];

        $attributes[static::FIELD_ACTION] = isset($attributes[static::FIELD_ACTION]) ? $attributes[static::FIELD_ACTION] : '';

        $attributes[static::FIELD_INTERNAL_HANDLER] = isset($attributes[static::FIELD_INTERNAL_HANDLER]) ? $attributes[static::FIELD_INTERNAL_HANDLER] : '';
        $attributes[static::FIELD_SEQUENCE] = isset($attributes[static::FIELD_SEQUENCE]) ? intval($attributes[static::FIELD_SEQUENCE]) : '';

        return $attributes;
    }


}