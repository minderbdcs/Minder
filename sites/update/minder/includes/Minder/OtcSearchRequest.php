<?php

class Minder_OtcSearchRequest {
    const TYPE_DESCRIPTION          = 'DESCRIPTION';
    const TYPE_TOOL                 = 'TOOL';
    const TYPE_LOCATION             = 'LOCATION';
    const TYPE_BORROWER             = 'BORROWER';
    const TYPE_BORROWER_PARTIAL     = 'BORROWER_PARTIAL';
    const TYPE_PRODUCT              = 'PROD_ID';
    const TYPE_COST_CENTER          = 'COST_CENTER';
    const TYPE_LEGACY_TOOL_CODE     = 'ALT_BARCODE';
    const TYPE_TOOL_SERIAL_NUMBER   = 'SERIAL NUMBER';

    protected $_queryType;
    protected $_paramName;
    protected $_paramValue;
    protected $_warehouse;
    protected $_isLoaned;
    protected $_matchCase = false;

    function __construct($queryType = '', $parameterName = '', $parameterValue = '', $warehouse = '', $isLoaned = '', $matchCase = false)
    {
        $this->_queryType = $queryType;
        $this->_paramName = strtoupper($parameterName);
        $this->_paramValue = $parameterValue;
        $this->_warehouse = $warehouse;
        $this->_isLoaned = $isLoaned;
        $this->_matchCase = $matchCase;
    }

    public function isLoaned() {
        return $this->_isLoaned;
    }

    public function ignoreCase() {
        return !$this->_matchCase;
    }

    public function exactWarehouse() {
        return strtolower($this->_warehouse) != 'all';
    }

    public function getWarehouse() {
        return $this->_warehouse;
    }

    public function getType() {
        return empty($this->_queryType) ? static::TYPE_DESCRIPTION : $this->_queryType;
    }

    public function getParameterValue() {
        return $this->_paramValue;
    }

    public function setParameterValue($value) {
        $this->_paramValue = $value;
    }

    public function isProductRequest() {
        return $this->getType() == static::TYPE_PRODUCT;
    }

    public function isPartialBorrowerRequest() {
        return $this->getType() == static::TYPE_BORROWER_PARTIAL;
    }

    public function isBorrowerRequest() {
        return $this->getType() == static::TYPE_BORROWER;
    }

    public function isLocationRequest() {
        return $this->getType() == static::TYPE_LOCATION;
    }

    public function isToolRequest() {
        return $this->getType() == static::TYPE_TOOL;
    }

    public function isCostCenterRequest() {
        return $this->getType() == static::TYPE_COST_CENTER;
    }

    public function isLegacyToolCodeRequest() {
        return $this->getType() == static::TYPE_LEGACY_TOOL_CODE;
    }

    public function isToolSerialNumberRequest() {
        return $this->getType() == static::TYPE_TOOL_SERIAL_NUMBER;
    }
}