<?php

class Minder_LabelPrinter_LabelField {

    protected $_sysLabelVar = array();
    protected $_fieldParts  = null;

    public function __construct($sysLabelVarData = array()) {
        $this->setSysLabelVar($sysLabelVarData);
    }

    public function setSysLabelVar($data = array()) {
        $this->_sysLabelVar = $data;
        return $this;
    }


    protected function _fetchFieldParts() {
        $slvName = isset($this->_sysLabelVar['SLV_NAME']) ? $this->_sysLabelVar['SLV_NAME'] : '';

        return explode('.', trim($slvName, ' %'));
    }

    protected function _getFieldParts() {
        if (is_null($this->_fieldParts)) {
            $this->_fieldParts = $this->_fetchFieldParts();
        }

        return $this->_fieldParts;
    }

    public function getFieldName() {
        $parts = $this->_getFieldParts();

        return isset($parts[1]) ? $parts[1] : $parts[0];
    }

    public function getTable() {
        $parts = $this->_getFieldParts();

        return isset($parts[1]) ? $parts[0] : '';
    }

    public function hasTable() {
        $parts = $this->_getFieldParts();
        return !empty($parts[1]);
    }
}