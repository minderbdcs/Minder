<?php

class Minder_Page_FormBuilder_InputMethodParcer {
    protected $_method = '';

    protected $_parts = null;

    /**
     * @param string $val
     * @return Minder_Page_FormBuilder_InputMethodParcer
     */
    protected function _setMethod($val) {
        $this->_method = strval($val);
        $this->_parts  = null;
        return $this;
    }

    /**
     * @return string
     */
    protected function _getMethod() {
        return $this->_method;
    }

    /**
     * @return array
     */
    protected function _fetchParts() {
        $tmpParts = explode('|', $this->_getMethod());
        $result = array();

        foreach ($tmpParts as $part) {
            if (empty($part))
                continue;

            $tmpPartArray = explode('=', $part);
            $key = $tmpPartArray[0];
            $value = isset($tmpPartArray[1]) ? $tmpPartArray[1] : null;
            $result[$key]  = $value;
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function _getParts() {
        if (is_null($this->_parts)) {
            $this->_parts = $this->_fetchParts();
        }

        return $this->_parts;
    }

    /**
     * @return string
     */
    protected function _getInputMethod() {
        $parts = $this->_getParts();

        if (empty($parts))
            return Minder_Page_FormBuilder_InputMethod::NONE;

        $result = current(array_keys($parts));

        switch ($result) {
            case Minder_Page_FormBuilder_InputMethod::CHECK_BOX:
            case Minder_Page_FormBuilder_InputMethod::COMBO_BOX:
            case Minder_Page_FormBuilder_InputMethod::DATE_PICKER:
            case Minder_Page_FormBuilder_InputMethod::DROP_DOWN:
            case Minder_Page_FormBuilder_InputMethod::INPUT:
            case Minder_Page_FormBuilder_InputMethod::NONE:
            case Minder_Page_FormBuilder_InputMethod::RADIO_GROUP:
            case Minder_Page_FormBuilder_InputMethod::READ_ONLY:
            case Minder_Page_FormBuilder_InputMethod::ELEMENT_GROUP:
            case Minder_Page_FormBuilder_InputMethod::GLOBAL_INPUT:
            case Minder_Page_FormBuilder_InputMethod::COMPLEX_DROPDOWN_NAME:
            case Minder_Page_FormBuilder_InputMethod::COMPLEX_DROPDOWN_VALUESLIST:
                return $result;
        }

        return Minder_Page_FormBuilder_InputMethod::NONE;
    }

    protected function _getCheckedValue() {
        $parts = $this->_getParts();
        return isset($parts['CHECHED_VALUE']) ? $parts['CHECHED_VALUE'] : null;
    }

    protected function _getUnCheckedValue() {
        $parts = $this->_getParts();
        return isset($parts['UNCHECKED_VALUE']) ? $parts['UNCHECKED_VALUE'] : null;
    }

    protected function _getRequred() {
        $parts = $this->_getParts();
        return array_key_exists('REQ', $parts);
    }

    protected function _getColumns() {
        $parts = $this->_getParts();
        return isset($parts['COLS']) ? $parts['COLS'] : null;
    }

    protected function _getRows() {
        $parts = $this->_getParts();
        return isset($parts['ROWS']) ? $parts['ROWS'] : 1;
    }

    protected function _getCells() {
        $parts = $this->_getParts();
        return isset($parts['CELLS']) ? $parts['CELLS'] : 1;
    }

    protected function _getGroupName() {
        $parts = $this->_getParts();
        return isset($parts['GROUP']) ? $parts['GROUP'] : null;
    }

    protected function _getFilters() {
        $result = array();
        $parts = $this->_getParts();

        foreach (array('UPPERCASE', 'LOWERCASE', 'INT', 'FLOAT') as $filter) {
            if (array_key_exists($filter, $parts))
                $result[] = $filter;
        }

        return $result;
    }

    protected function _getWildCardType() {
        $parts = $this->_getParts();
        return isset($parts['WCARD']) ? $parts['WCARD'] : 'BOTH';
    }

    protected function _getDateType() {
        $parts = $this->_getParts();
        return isset($parts[Minder_Page_FormBuilder_InputMethod::DATE_TYPE])
            ? $parts[Minder_Page_FormBuilder_InputMethod::DATE_TYPE]
            : Minder_Page_FormBuilder_InputMethod::DATE_DAY;

    }

    protected function _getGroupNo() {
        $keys  = array_keys($this->_getParts());
        $inputMethod = $this->_getInputMethod();
        return ($inputMethod == Minder_Page_FormBuilder_InputMethod::COMPLEX_DROPDOWN_NAME ||
                $inputMethod == Minder_Page_FormBuilder_InputMethod::COMPLEX_DROPDOWN_VALUESLIST) ? $keys[1] : null;
    }

    /**
     * @param $method
     * @return Minder_Page_FormBuilder_InputMethod
     */
    public function parse($method) {
        $result = new Minder_Page_FormBuilder_InputMethod();
        $this->_setMethod($method);

        $result->inputMethod = $this->_getInputMethod();
        $result->checkedValue = $this->_getCheckedValue();
        $result->uncheckedValue = $this->_getUnCheckedValue();
        $result->required = $this->_getRequred();
        $result->columns = $this->_getColumns();
        $result->rows = $this->_getRows();
        $result->cells = $this->_getCells();
        $result->groupName = $this->_getGroupName();
        $result->filters = $this->_getFilters();
        $result->wildcardType = $this->_getWildcardType();
        $result->groupNo = $this->_getGroupNo();
        $result->dateType = $this->_getDateType();

        return $result;
    }
}