<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\Model;

/**
 * Class TransactionField
 * @package MinderNG\PageMicrocode\Component
 *
 * @property string SS_NAME
 * @property string SS_VARIANCE
 * @property string SST_ACTION
 * @property string SST_TRN_TYPE
 * @property string SST_TRN_CLASS
 * @property string SST_FIELD_ROLE
 * @property string SST_COLUMN_EXPRESSION
 * @property string SST_TRN_FIELD
 * @property string SST_COLUMN
 * @property string SST_TRN_FIELD_EXPRESSION
 */
class TransactionField extends Model {

    const FIELD_SCREEN_NAME = 'SS_NAME';
    const FIELD_VARIANCE = 'SS_VARIANCE';
    const FIELD_ACTION = 'SST_ACTION';
    const FIELD_TRANSACTION_TYPE = 'SST_TRN_TYPE';
    const FIELD_TRANSACTION_CLASS = 'SST_TRN_CLASS';
    const FIELD_ROLE = 'SST_FIELD_ROLE';
    const FIELD_COLUMN = 'SST_COLUMN';
    const FIELD_TABLE = 'SST_TABLE';
    const FIELD_TRANSACTION_FIELD = 'SST_TRN_FIELD';

    const ROLE_KEY = 'KEY';
    const ROLE_UPDATE = 'UPDATE';
    const ROLE_CONST = 'CONST';
    const ROLE_RESULT = 'RESULT';

    public function isRoleKey() {
        return $this->SST_FIELD_ROLE === static::ROLE_KEY;
    }

    public function isRoleUpdate() {
        return $this->SST_FIELD_ROLE === static::ROLE_UPDATE;
    }

    public function isRoleResult() {
        return $this->SST_FIELD_ROLE === static::ROLE_RESULT;
    }

    public static function calculateId(array $attributes = array())
    {
        $attributes = static::_parse($attributes);

        return implode('/', array(
            $attributes[static::FIELD_SCREEN_NAME],
            $attributes[static::FIELD_VARIANCE],
            $attributes[static::FIELD_ACTION],
            $attributes[static::FIELD_TRANSACTION_TYPE],
            $attributes[static::FIELD_TRANSACTION_CLASS],
            $attributes[static::FIELD_ROLE],
            $attributes[static::FIELD_TRANSACTION_FIELD],
            $attributes[static::FIELD_COLUMN],
        ));
    }

    public function parse($attributes)
    {
        return static::_parse($attributes);
    }

    private static function _parse($attributes) {
        $attributes[static::FIELD_SCREEN_NAME] = isset($attributes[static::FIELD_SCREEN_NAME]) ? $attributes[static::FIELD_SCREEN_NAME] : '';
        $attributes[static::FIELD_VARIANCE] = empty($attributes[static::FIELD_VARIANCE]) ? $attributes[static::FIELD_SCREEN_NAME] : $attributes[static::FIELD_VARIANCE];
        $attributes[static::FIELD_ACTION] = isset($attributes[static::FIELD_ACTION]) ? strtoupper(trim($attributes[static::FIELD_ACTION])) : Transaction::ACTION_UPDATE;
        $attributes[static::FIELD_TRANSACTION_TYPE] = isset($attributes[static::FIELD_TRANSACTION_TYPE]) ? $attributes[static::FIELD_TRANSACTION_TYPE] : '';
        $attributes[static::FIELD_TRANSACTION_CLASS] = isset($attributes[static::FIELD_TRANSACTION_CLASS]) ? $attributes[static::FIELD_TRANSACTION_CLASS] : '';
        $attributes[static::FIELD_TRANSACTION_FIELD] = isset($attributes[static::FIELD_TRANSACTION_FIELD]) ? $attributes[static::FIELD_TRANSACTION_FIELD] : '';

        $attributes[static::FIELD_ROLE] = isset($attributes[static::FIELD_ROLE]) ? strtoupper(trim($attributes[static::FIELD_ROLE])) : static::ROLE_UPDATE;

        $attributes[static::FIELD_COLUMN] = isset($attributes[static::FIELD_COLUMN]) ? $attributes[static::FIELD_COLUMN] : '';
        if (!empty($attributes[static::FIELD_TABLE])) {
            $attributes[static::FIELD_COLUMN] = $attributes[static::FIELD_TABLE] . '_' . $attributes[static::FIELD_COLUMN];
        }

        /** seems this is not required anymore */
        $attributes['SST_SCREEN_TYPE'] = isset($attributes['SST_SCREEN_TYPE']) ? strtoupper(trim($attributes['SST_SCREEN_TYPE'])) : 'SR';
        /** ---------------------------------- */

        return $attributes;
    }

}