<?php

abstract class Minder2_LabelPrinter_Abstract implements Minder2_LabelPrinter_Interface {
    protected $_sysScreenName = '';
    protected $_labelName = '';

    public function __construct($sysScreenName, $labelName) {
        $this->setLabelName($labelName)->setSysScreenName($sysScreenName);
    }

    public function setSysScreenName($value) {
        $this->_sysScreenName = strval($value);
        return $this;
    }

    public function setLabelName($value) {
        $this->_labelName = strval($value);
        return $this;
    }

    protected function _extractFieldValue($data, $fieldName) {
        $result = array();
        foreach ($data as $dataRow) {
            $result[] = $dataRow[$fieldName];
        }

        return $result;
    }

    protected function _getMinder() {
        return Minder::getInstance();
    }
}