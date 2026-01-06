<?php

class Minder2_Validate_Param extends Zend_Validate_Abstract {

    protected $_validParams = array();

    protected $_params = null;

    protected $_prefixMap = null;

    protected $_forceValidWithoutPrefix;

    function __construct($validParams, $forceValidWithoutPrefix = false) {
        $this->_validParams = is_array($validParams) ? $validParams : array($validParams);
        $this->_forceValidWithoutPrefix = (boolean)$forceValidWithoutPrefix;
    }

    /**
     * @return Minder2_Model_Mapper_Param
     */
    protected function _getParamMapper() {
        return new Minder2_Model_Mapper_Param();
    }

    protected function _buildParams() {
        $result = array();
        $paramMapper = $this->_getParamMapper();

        foreach ($this->_validParams as $paramDataId) {
            $tmpParam = $paramMapper->find($paramDataId);
            if ($tmpParam->existed)
                $result[] = $tmpParam;
        }

        return $result;
    }

    protected function _getParams() {
        if (is_null($this->_params))
            $this->_params = $this->_buildParams();

        return $this->_params;
    }

    protected function _prefixSortCallback($a, $b) {
        return strlen($b) - strlen($a);
    }

    protected function _buildPrefixMap() {
        $result = array();

        /**
         * @var Minder2_Model_Param $param
         */
        foreach ($this->_getParams() as $param) {
            foreach ($param->prefixes as $prefix) {
                if (!isset($result[$prefix]))
                    $result[$prefix] = array();

                $result[$prefix][$param->DATA_ID] = $param;
            }

            if ($this->_forceValidWithoutPrefix)
                $result[''][$param->DATA_ID] = $param;
        }

        uksort($result, array($this, '_prefixSortCallback'));

        return $result;
    }

    protected function _getPrefixMap() {
        if (is_null($this->_prefixMap))
            $this->_prefixMap = $this->_buildPrefixMap();

        return $this->_prefixMap;
    }

    /**
     * @param string $val
     * @param string $prefix
     * @param Minder2_Model_Param $param
     * @return bool
     */
    protected function _isValid($val, $prefix, $param) {
        if (strlen($prefix) > 0 && strpos($val, $prefix) !== 0)
            return false;

        $val = substr($val, strlen($prefix));

        if ($param->FIXED_LENGTH && strlen($val) != $param->MAX_LENGTH)
            return false;

        if ($param->MAX_LENGTH > 0 && $param->MAX_LENGTH < strlen($val))
            return false;

        return (boolean)preg_match('/' . $param->DATA_EXPRESSION . '/', $val);
    }


    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value
     * @return boolean
     * @throws Zend_Valid_Exception If validation of $value is impossible
     */
    public function isValid($value)
    {
        foreach ($this->_getPrefixMap() as $prefix => $params) {
            /**
             * @var Minder2_Model_Param $param
             */
            foreach ($params as $param)
                if ($this->_isValid($value, $prefix, $param))
                    return true;
        }

        return false;
    }

}