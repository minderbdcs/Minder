<?php

/**
 * @property string $SSN_ID
 * @property string $SSN_TYPE
 * @property string $GENERIC
 * @property string $SSN_SUB_TYPE
 * @property string $BRAND
 * @property string $MODEL
 * @property string $SERIAL_NUMBER
 * @property string $LEGACY_ID
 * @property string $COMPANY_ID
 * @property string $LOCN_ID
 * @property string $WH_ID
 * @property string $ALT_NAME
 * @property string $PURCHASE_PRICE
 * @property string $PO_ORDER
 *
 * @property string LOAN_SAFETY_CHECK
 * @property string LOAN_LAST_SAFETY_CHECK_DATE
 * @property string LOAN_SAFETY_PERIOD_NO
 * @property string LOAN_SAFETY_PERIOD
 *
 * @property string LOAN_INSPECT_CHECK
 * @property string LOAN_LAST_INSPECT_CHECK_DATE
 * @property string LOAN_INSPECT_PERIOD_NO
 * @property string LOAN_INSPECT_PERIOD
 *
 * @property string LOAN_CALIBRATE_CHECK
 * @property string LOAN_LAST_CALIBRATE_CHECK_DATE
 * @property string LOAN_CALIBRATE_PERIOD_NO
 * @property string LOAN_CALIBRATE_PERIOD
 *
 * @property string $location
 *
 */
class Minder2_Model_Tool extends Minder2_Model {
    const FIELD_TYPE_STRING  = 'STRING';
    const FIELD_TYPE_BOOLEAN = 'BOOLEAN';

    protected static $_tableName = 'SSN';
    protected static $_meta = null;

    function __get($name)
    {
        switch ($name) {
            case 'location':
                return $this->WH_ID . $this->LOCN_ID;
        }
        return parent::__get($name);
    }

    function __isset($name)
    {
        switch ($name) {
            case 'location':
                return true;
        }
        return parent::__isset($name);
    }


    protected function _getDbFieldType($name) {
        //todo
        return self::FIELD_TYPE_STRING;
    }

    protected function _getDbFieldValue($name) {
        if (!isset($this->_dbFields[$name]))
            return null;

        switch ($this->_getDbFieldType($name)) {
            case self::FIELD_TYPE_BOOLEAN:
                return $this->_dbFields[$name] == 'T';
            case self::FIELD_TYPE_STRING:
            default:
                return $this->_dbFields[$name];
        }
    }
}