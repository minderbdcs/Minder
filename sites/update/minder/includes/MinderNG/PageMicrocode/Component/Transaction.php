<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\Model;

/**
 * Class Transaction
 * @package MinderNG\PageMicrocode\Component
 *
 * @property string SS_NAME
 * @property string SS_VARIANCE
 * @property string SST_ACTION
 * @property string SST_TRN_TYPE
 * @property string SST_TRN_CLASS
 */
class Transaction extends Model {

    const FIELD_SCREEN_NAME = 'SS_NAME';
    const FIELD_VARIANCE = 'SS_VARIANCE';
    const FIELD_ACTION = 'SST_ACTION';
    const FIELD_TRANSACTION_TYPE = 'SST_TRN_TYPE';
    const FIELD_TRANSACTION_CLASS = 'SST_TRN_CLASS';

    const ACTION_UPDATE = 'UPDATE';
    const ACTION_ADD = 'ADD';

    public function filterUpdateTransactionFields(TransactionFieldCollection $fields) {
        return $fields->where(array(
            'SST_TRN_TYPE' => $this->SST_TRN_TYPE,
            'SST_TRN_CLASS' => $this->SST_TRN_CLASS,
            'SST_ACTION' => $this->SST_ACTION,
            'SS_VARIANCE' => $this->SS_VARIANCE,
            'SS_NAME' => $this->SS_NAME,
            'SST_FIELD_ROLE' => TransactionField::ROLE_UPDATE
        ));
    }

    public function filterTransactionFields(TransactionFieldCollection $fields) {
        return $fields->where(array(
            'SST_TRN_TYPE' => $this->SST_TRN_TYPE,
            'SST_TRN_CLASS' => $this->SST_TRN_CLASS,
            'SST_ACTION' => $this->SST_ACTION,
            'SS_VARIANCE' => $this->SS_VARIANCE,
            'SS_NAME' => $this->SS_NAME,
        ));
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
        ));
    }

    public function parse($attributes)
    {
        return static::_parse($attributes);
    }

    private function _parse($attributes) {
        $attributes[static::FIELD_SCREEN_NAME] = isset($attributes[static::FIELD_SCREEN_NAME]) ? $attributes[static::FIELD_SCREEN_NAME] : '';
        $attributes[static::FIELD_VARIANCE] = empty($attributes[static::FIELD_VARIANCE]) ? $attributes[static::FIELD_SCREEN_NAME] : $attributes[static::FIELD_VARIANCE];
        $attributes[static::FIELD_ACTION] = isset($attributes[static::FIELD_ACTION]) ? strtoupper(trim($attributes[static::FIELD_ACTION])) : static::ACTION_UPDATE;
        $attributes[static::FIELD_TRANSACTION_TYPE] = isset($attributes[static::FIELD_TRANSACTION_TYPE]) ? $attributes[static::FIELD_TRANSACTION_TYPE] : '';
        $attributes[static::FIELD_TRANSACTION_CLASS] = isset($attributes[static::FIELD_TRANSACTION_CLASS]) ? $attributes[static::FIELD_TRANSACTION_CLASS] : '';

        /** seems this is not required anymore */
        $attributes['SST_FIELD_ROLE'] = isset($attributes['SST_FIELD_ROLE']) ? strtoupper(trim($attributes['SST_FIELD_ROLE'])) : 'UPDATE';
        $attributes['SST_SCREEN_TYPE'] = isset($attributes['SST_SCREEN_TYPE']) ? strtoupper(trim($attributes['SST_SCREEN_TYPE'])) : 'SR';
        /** ---------------------------------- */

        return $attributes;
    }
}