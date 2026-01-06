<?php

class Minder_SysScreen_PartBuilder_SEFields extends Minder_SysScreen_PartBuilder {
    protected $_partName                      = 'SEARCH_FIELDS';
    protected $_tableName                     = 'SYS_SCREEN_VAR';
    protected $_orderByFieldName              = 'SSV_SEQUENCE';

    protected $_staticLimitsUserExpression    = 'SSV_USER_TYPE';
    protected $_staticLimitsDeviceExpression  = 'SSV_DEVICE_TYPE';

    protected function _isExpandable()
    {
        return !$this->_getInheritanceSettings()->CUSTOM_TABLES;
    }

    protected function _getPartFilters()
    {
        return array(
            'SS_NAME = ?' => array($this->_ssRealName),
            'SSV_FIELD_TYPE =?' => array('SE'),
            'SSV_FIELD_STATUS = ?' => array('OK')
        );
    }

    protected function _validateFieldAlias(&$fieldDesc) {
        //if no alias for field defined - use SSV_NAME as SSV_ALIAS
        if (empty($fieldDesc['SSV_ALIAS']))
            $fieldDesc['SSV_ALIAS'] = $fieldDesc['SSV_NAME'];

        //if SSV_ALIAS still empty - raise error, as this is incorrect
        if (empty($fieldDesc['SSV_ALIAS']))
            throw new Minder_SysScreen_Builder_Exception("Sys Screen Var #" . $fieldDesc['RECORD_ID'] . " has no SSV_NAME and no SSV_ALIAS.");

        return $this;
    }

    protected function _validateFieldExpression(&$fieldDesc) {
        //if no SSV_EXPRESSION for field i.e. we use raw field value
        //assume SSV_EXPRESSION = SSV_TABLE.SSV_NAME for easy later use
        if (empty($fieldDesc['SSV_EXPRESSION']))
            if (empty($fieldDesc['SSV_TABLE']))
                $fieldDesc['SSV_EXPRESSION'] = $fieldDesc['SSV_NAME'];
            else
                $fieldDesc['SSV_EXPRESSION'] = $fieldDesc['SSV_TABLE'] . '.' . $fieldDesc['SSV_NAME'];

        //if SSV_EXPRESSION still empty raise an error, as this is incorrect
        if (empty($fieldDesc['SSV_EXPRESSION']))
            throw new Minder_SysScreen_Builder_Exception("Sys Screen Var #" . $fieldDesc['RECORD_ID'] . " has no SSV_NAME and no SSV_EXPRESSION.");

        return $this;
    }

    protected function _fillFieldExpressionParams(&$fieldDesc) {
        $foundMatches = array();
        if (preg_match_all('/(%\w+%)/', $fieldDesc['SSV_EXPRESSION'], $foundMatches)) {
            $fieldDesc['EXPRESSION_PARAMS'] = $foundMatches[1];
        }

        return $this;
    }

    protected function _buildRBField(&$fieldDesc) {
        $this->_validateFieldAlias($fieldDesc)
            ->_validateFieldExpression($fieldDesc)
            ->_fillFieldExpressionParams($fieldDesc);

        $tmpInputMethod = explode('|', trim($fieldDesc['SSV_INPUT_METHOD'], '|'));
        unset($tmpInputMethod[0]);

        $fieldDesc['OPTIONS'] = array();

        foreach ($tmpInputMethod as $optionDesc) {
            $tmpOptionDescArr = explode('=', $optionDesc);

            $optionCaption = $tmpOptionDescArr[0];
            $optionValue   = (empty($tmpOptionDescArr[0])) ? 'ON' : strtoupper($tmpOptionDescArr[0]);

            $fieldDesc['OPTIONS'][$optionValue] = $optionCaption;

            if (isset($tmpOptionDescArr[1]) && strtoupper($tmpOptionDescArr[1]) == 'T') {
                $fieldDesc['DEFAULT_VALUE'] = $optionValue;
            }
        }

        if (empty($fieldDesc['OPTIONS'])) {
            $fieldDesc['OPTIONS']['ON'] = '';
        }

        return $this;
    }

    protected function _doBuild()
    {
        $dlRepository = array();
        $result = array();
        $inputMethodParser = new Minder_SysScreen_PartBuilder_InputMethodParser();

        foreach ($this->_selectDbRows() as $row) {

            $row = $inputMethodParser->parseSysScreenVarInputMethods($row);

            $tmpInputMethod = explode('|', $row['SSV_INPUT_METHOD']);

            $row['EXPRESSION_PARAMS']  = array();
            switch ($tmpInputMethod[0]) {
                case 'DD':
                case 'dl':
                case 'DP':
                case 'IN':
                case 'RO':
                case 'GI':
                case 'NONE':
                    $this->_validateFieldAlias($row)
                        ->_validateFieldExpression($row)
                        ->_fillFieldExpressionParams($row);

                    break;
                case 'RB':
                    $this->_buildRBField($row);
                    
                    break;
                case 'DL':
                    if (isset($dlRepository[$tmpInputMethod[1]]))
                        throw new Minder_SysScreen_Builder_Exception("Sys Screen Vars #" . $row['RECORD_ID'] . " and #" . $dlRepository[$tmpInputMethod[1]]['RECORD_ID'] . " has same DL number " . $row['INPUT_METHOD'] .  ".");

                    if (empty($row['SSV_ALIAS'])) {
                        $row['SSV_ALIAS'] = 'DL_' . $tmpInputMethod[1] . '_CONTAINER';
                    }

                    $dlRepository[$tmpInputMethod[1]] = $row;
                    break;
                default:
                    throw new Minder_SysScreen_Builder_Exception("Unsupported input method '" . $row['SSV_INPUT_METHOD'] . "' for Sys Screen Var #" . $row['RECORD_ID'] . ".");
            }

            if (!empty($row['SSV_DROPDOWN_DEFAULT'])) {
                $row['DEFAULT_VALUE'] = $this->minder->findValue($row['SSV_DROPDOWN_DEFAULT']);
            }

            //add service field for sorting later
            $row['ORDER_BY_FIELD_NAME'] = 'SSV_SEQUENCE';
            $row['SEARCH_VALUE']        = '';

            $row['SQL_PARAMS']         = array();
            $row['DEFAULT_SQL_PARAMS'] = array();
            if (trim($row['SSV_DROPDOWN_DATA_FROM']) == 'SQL') {
                $row['SQL_PARAMS']         = $this->prepareSql($row['SSV_DROPDOWN_SQL']);
                $row['DEFAULT_SQL_PARAMS'] = $this->prepareSql($row['SSV_DROPDOWN_DEFAULT']);
            }
            $result[$row['RECORD_ID']] = $row;
        }

        foreach ($result as $fieldDesc) {
            $tmpInputMethod = explode('|', $fieldDesc['SSV_INPUT_METHOD']);
            if ($tmpInputMethod[0] == 'dl') {
                if (!isset($dlRepository[$tmpInputMethod[1]]))
                    throw new Minder_SysScreen_Builder_Exception("Some search fields with INPUT_METHOD = 'dl|" . $tmpInputMethod[1] . "|' was defined but no search fields with INPUT_METHOD='DL|" . $tmpInputMethod[1] . "|' was found in '" . $this->_ssRealName . "'.");

                $tmpDLfieldDesc = $dlRepository[$tmpInputMethod[1]];

                if (!isset($result[$tmpDLfieldDesc['RECORD_ID']]['ELEMENTS']))
                    $result[$tmpDLfieldDesc['RECORD_ID']]['ELEMENTS'] = array();

                if (isset($result[$tmpDLfieldDesc['RECORD_ID']]['ELEMENTS'][$fieldDesc['SSV_SEQUENCE']])) {
                    $tmpDesc = $result[$tmpDLfieldDesc['RECORD_ID']]['ELEMENTS'][$fieldDesc['SSV_SEQUENCE']];
                    throw new Minder_SysScreen_Builder_Exception("Sys Screen Vars #" . $fieldDesc['RECORD_ID'] . " and #" . $tmpDesc['RECORD_ID'] . " has same SSV_SEQUENCE " . $fieldDesc['SSV_SEQUENCE'] .  ".");
                }

                $result[$tmpDLfieldDesc['RECORD_ID']]['ELEMENTS'][$fieldDesc['SSV_SEQUENCE']] = $fieldDesc;
            }
        }

        return $result;
    }
}