<?php

class Minder_SysScreen_PartBuilder_SRFields extends Minder_SysScreen_PartBuilder {
    protected $_partName                      = 'FIELDS';
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
            'SSV_FIELD_TYPE =?' => array('SR'),
            'SSV_FIELD_STATUS = ?' => array('OK')
        );
    }

    protected function _doBuild()
    {
        $result = array();
        $rows = $this->_selectDbRows();

        foreach ($rows as $row) {
            $row = $this->_prepareDbRow($row);

            if ($row['SSV_INPUT_METHOD'] != 'NONE') {
                $result[$row['RECORD_ID']] = $row;
            }
        }

        return $result;
    }

    protected function _getNumberFormat($row) {
        $ssvOther = isset($row['SSV_FORMAT']) ? $row['SSV_FORMAT'] : null;

        if (empty($ssvOther))
            return null;

        $pattern = '/^(NUMBERFORMAT)\((.*)\)$/i';

        $matches = array();

        if (!preg_match($pattern, $ssvOther, $matches))
            return null; //no number format

        return $matches[2];
    }

    protected function _prepareDbRow($row)
    {
        $inputMethodParser = new Minder_SysScreen_PartBuilder_InputMethodParser();
        $row = $inputMethodParser->parseSysScreenVarInputMethods($row);

        //if no alias for field defined - use SSV_NAME as SSV_ALIAS
        if (empty($row['SSV_ALIAS']))
            $row['SSV_ALIAS'] = $row['SSV_NAME'];

        //if SSV_ALIAS still empty - raise error, as this is incorrect
        if (empty($row['SSV_ALIAS']))
            throw new Minder_SysScreen_Builder_Exception("Sys Screen Var #" . $row['RECORD_ID'] . " has no SSV_NAME and no SSV_ALIAS.");

        //if no SSV_EXPRESSION for field i.e. we use raw field value
        //assume SSV_EXPRESSION = SSV_TABLE.SSV_NAME for easy later use
        if (empty($row['SSV_EXPRESSION']))
            if (empty($row['SSV_TABLE']))
                $row['SSV_EXPRESSION'] = $row['SSV_NAME'];
            else
                $row['SSV_EXPRESSION'] = $row['SSV_TABLE'] . '.' . $row['SSV_NAME'];

        //if SSV_EXPRESSION still empty raise an error, as this is incorrect
        if (empty($row['SSV_EXPRESSION']))
            throw new Minder_SysScreen_Builder_Exception("Sys Screen Var #" . $row['RECORD_ID'] . " has no SSV_NAME and no SSV_EXPRESSION.");

        //add service field for sorting later
        $row['ORDER_BY_FIELD_NAME'] = 'SSV_SEQUENCE';
        $row['COLOR_FIELD_ALIAS']   = 'COLOR_FIELD_' . $row['RECORD_ID'];

        //check for access rights
        if (!$this->_UserCategoryIsValid($row)) {
            $row['SSV_INPUT_METHOD']     = 'NONE';
            $row['SSV_INPUT_METHOD_NEW'] = 'NONE';
        }

        $row['EXPRESSION_PARAMS']  = array();
        $foundMatches = array();
        if (preg_match_all('/(%\w+%)/', $row['SSV_EXPRESSION'], $foundMatches)) {
            $row['EXPRESSION_PARAMS'] = $foundMatches[1];
        }

        $row['SQL_PARAMS']         = array();
        $row['DEFAULT_SQL_PARAMS'] = array();
        if (trim($row['SSV_DROPDOWN_DATA_FROM']) == 'SQL') {
            $row['SQL_PARAMS']         = $this->prepareSql($row['SSV_DROPDOWN_SQL']);
            $row['DEFAULT_SQL_PARAMS'] = $this->prepareSql($row['SSV_DROPDOWN_DEFAULT']);
        }

        $row['NUMBER_FORMAT'] = $this->_getNumberFormat($row);

        return $row;
    }


}