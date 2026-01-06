<?php

namespace MinderNG\PageMicrocode\Component;


use MinderNG\Collection\Model;

/**
 * Class Field
 * @package MinderNG\PageMicrocode\Component
 *
 * @property string SS_NAME
 * @property string SS_VARIANCE
 * @property string SSV_ALIAS
 * @property string SSV_TITLE
 * @property string SSV_EXPRESSION
 * @property string SSV_DROPDOWN_SQL
 * @property string SSV_FIELD_TYPE
 * @property string SSV_FIELD_STATUS
 * @property string SSF_NAME
 * @property string SSV_TAB
 * @property string DEFAULT
 * @property InputMethod METHOD
 * @property InputMethod METHOD_NEW
 */
class Field extends Model {

    const ID_DELIMITER = '/';

    const STATUS_COMMENTED = 'CM';
    const STATUS_OK = 'OK';

    const INPUT_METHOD_NONE     = 'NONE';
    const INPUT_METHOD_GI       = 'GI';

    const TYPE_SE               = 'SE';
    const TYPE_SR               = 'SR';
    const TYPE_ER               = 'ER';

    const FIELD_SCREEN_NAME         = 'SS_NAME';
    const FIELD_VARIANCE            = 'SS_VARIANCE';
    const FIELD_TYPE                = 'SSV_FIELD_TYPE';
    const FIELD_GLOBAL_INPUT        = 'globalInput';
    const FIELD_FORM_NAME           = 'SSF_NAME';
    const FIELD_ALIAS               = 'SSV_ALIAS';
    const FIELD_STATUS              = 'SSV_FIELD_STATUS';
    const FIELD_INPUT_METHOD        = 'SSV_INPUT_METHOD';
    const FIELD_INPUT_METHOD_NEW    = 'SSV_INPUT_METHOD_NEW';
    const FIELD_DATA_SOURCE         = 'DATA_SOURCE';
    const FIELD_TABLE               = 'SSV_TABLE';
    const FIELD_NAME                = 'SSV_NAME';
    const FIELD_EXPRESSION          = 'SSV_EXPRESSION';
    const FIELD_DATA_ID_LIST        = 'dataIdList';
    const FIELD_DATA_TYPE_LIST      = 'dataTypeList';
    const FIELD_METHOD              = 'METHOD';
    const FIELD_METHOD_NEW          = 'METHOD_NEW';
    const FIELD_FILTER              = 'FILTER';
    const FIELD_VALIDATOR           = 'VALIDATOR';
    const FIELD_DECORATOR           = 'DECORATOR';

    public static function calculateId(array $attributes = array())
    {
        $attributes = static::_parse($attributes);

        return implode(static::ID_DELIMITER, array(
            $attributes[static::FIELD_SCREEN_NAME],
            $attributes[static::FIELD_VARIANCE],
            $attributes[static::FIELD_TYPE],
            $attributes[static::FIELD_GLOBAL_INPUT],
            $attributes[static::FIELD_FORM_NAME],
            $attributes[static::FIELD_ALIAS],
            $attributes[static::FIELD_STATUS],
        ));
    }

    /**
     * @param array $attributes
     * @return array
     */
    private static function _parseInputMethod(array $attributes) {
        $method = InputMethod::fromJSON(isset($attributes[static::FIELD_INPUT_METHOD]) ? trim($attributes[static::FIELD_INPUT_METHOD]) : '');

        $attributes[static::FIELD_METHOD]       = $method;
        $attributes[static::FIELD_VALIDATOR]    = $method->validator;
        $attributes[static::FIELD_FILTER]       = $method->filter;
        $attributes[static::FIELD_DECORATOR]    = $method->decorator;
        $attributes[static::FIELD_INPUT_METHOD] = empty($method->method) ? static::INPUT_METHOD_NONE : strtoupper($method->method);

        return $attributes;
    }

    private static function _parseInputMethodNew($attributes) {
        if ($attributes[static::FIELD_TYPE] == static::TYPE_SE) {
            //search fields use same input method for both record types
            $method = InputMethod::fromJSON(isset($attributes[static::FIELD_INPUT_METHOD]) ? trim($attributes[static::FIELD_INPUT_METHOD]) : '');
        } else {
            $method = InputMethod::fromJSON(isset($attributes[static::FIELD_INPUT_METHOD_NEW]) ? trim($attributes[static::FIELD_INPUT_METHOD_NEW]) : '');
        }

        $attributes[static::FIELD_METHOD_NEW]       = $method;
        $attributes[static::FIELD_INPUT_METHOD_NEW] = empty($method->method) ? static::INPUT_METHOD_NONE : strtoupper($method->method);

        return $attributes;
    }

    public function parse($attributes)
    {
        return $this->_parse($attributes);
    }

    public function isStatusOk() {
        return $this->SSV_FIELD_STATUS == static::STATUS_OK;
    }

    public function belongsToAllTabs() {
        return empty($this->SSV_TAB);
    }

    /**
     * @param $attributes
     * @return mixed
     */
    private static function _parse($attributes)
    {
        $attributes[static::FIELD_SCREEN_NAME] = isset($attributes[static::FIELD_SCREEN_NAME]) ? $attributes[static::FIELD_SCREEN_NAME] : '';
        $attributes[static::FIELD_VARIANCE] = empty($attributes[static::FIELD_VARIANCE]) ? $attributes[static::FIELD_SCREEN_NAME] : $attributes[static::FIELD_VARIANCE];
        $attributes[static::FIELD_ALIAS] = empty($attributes[static::FIELD_ALIAS]) ? static::_fullName($attributes, '_') : $attributes[static::FIELD_ALIAS];

        $attributes[static::FIELD_TYPE] = isset($attributes[static::FIELD_TYPE]) ? strtoupper(trim($attributes[static::FIELD_TYPE])) : '';
        $attributes[static::FIELD_TYPE] = empty($attributes[static::FIELD_TYPE]) ? static::TYPE_SR : $attributes[static::FIELD_TYPE];

        $attributes = self::_parseInputMethod($attributes);
        $attributes = self::_parseInputMethodNew($attributes);

        $attributes[static::FIELD_GLOBAL_INPUT] = ($attributes[static::FIELD_INPUT_METHOD] == static::INPUT_METHOD_GI);

        $attributes[static::FIELD_STATUS] = isset($attributes[static::FIELD_STATUS]) ? strtoupper(trim($attributes[static::FIELD_STATUS])) : '';
        $attributes[static::FIELD_STATUS] = empty($attributes[static::FIELD_STATUS]) ? static::STATUS_COMMENTED : $attributes[static::FIELD_STATUS];

        $attributes[static::FIELD_FORM_NAME] = isset($attributes[static::FIELD_FORM_NAME]) ? $attributes[static::FIELD_FORM_NAME] : '';
        $attributes[static::FIELD_DATA_SOURCE] = in_array($attributes[static::FIELD_TYPE], array(static::TYPE_SR, static::TYPE_ER));

        $attributes[static::FIELD_DATA_ID_LIST] = array();
        $attributes[static::FIELD_DATA_TYPE_LIST] = array();
        if ($attributes[static::FIELD_GLOBAL_INPUT]) {
            $dataString = isset($attributes[static::FIELD_EXPRESSION]) ? $attributes[static::FIELD_EXPRESSION] : '';
            foreach (explode('|' , $dataString) as $dataTypeOrId) {
                if (!empty($dataTypeOrId)) {
                    if ($dataTypeOrId[0] === '#') {
                        $attributes[static::FIELD_DATA_TYPE_LIST][] = substr($dataTypeOrId, 1);
                    } else {
                        $attributes[static::FIELD_DATA_ID_LIST][] = $dataTypeOrId;
                    }
                }
            }

            $attributes[static::FIELD_EXPRESSION] = static::_fullName($attributes);
        } else {
            $attributes[static::FIELD_EXPRESSION] = empty($attributes[static::FIELD_EXPRESSION]) ? static::_fullName($attributes) : $attributes[static::FIELD_EXPRESSION];
        }

        return $attributes;
    }

    static private function _fullName($attributes, $separator = '.') {
        return isset($attributes[static::FIELD_TABLE])
            ? $attributes[static::FIELD_TABLE] . $separator . $attributes[static::FIELD_NAME]
            : $attributes[static::FIELD_NAME];
    }
}