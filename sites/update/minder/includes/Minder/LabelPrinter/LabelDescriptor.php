<?php


class Minder_LabelPrinter_LabelDescriptor {

    /**
     * @var Minder_Printer_Abstract
     */
    protected $_printer = null;
    protected $_labelType = null;

    protected $_sysLabelTables = array();
    protected $_sysLabelFields = array();
    protected $_sysLabelName   = null;

    public function __construct($printer, $labelType) {
        $this->setPrinter($printer)->setLabelType($labelType);
    }

    public function setPrinter($printer) {
        $this->_printer = $printer;
        $this->_sysLabelFields = array();
        $this->_sysLabelFields = array();
        $this->_sysLabelName   = null;
        return $this;
    }

    public function setLabelType($type) {
        $this->_labelType = $type;
        $this->_sysLabelFields = array();
        $this->_sysLabelFields = array();
        $this->_sysLabelName   = null;
        return $this;
    }

    protected function _getPrinter() {
        return $this->_printer;
    }

    protected function _getLabelType() {
        return $this->_labelType;
    }

    protected function _getMinder() {
        return Minder::getInstance();
    }

    protected function _fetchSysLabelName() {
        $groupCode = strtoupper($this->_getPrinter()->getPrinter()) . '_FORMAT';
        $result = $this->_getMinder()->fetchOne('SELECT DESCRIPTION FROM OPTIONS WHERE GROUP_CODE = ? AND CODE = ?', $groupCode, $this->_getLabelType());

        return empty($result) ? '': $result;
    }

    protected function _getSysLabelName() {
        if (empty($this->_sysLabelName))
            $this->_sysLabelName = $this->_fetchSysLabelName();

        return $this->_sysLabelName;
    }

    protected function _fetchSysLabelFields() {
        $minder = $this->_getMinder();
        $result = array();

        $sysLabelName = $this->_getSysLabelName();

        if (empty($sysLabelName))
            return $result;

        foreach ($minder->fetchAllAssoc('SELECT * FROM SYS_LABEL_VAR WHERE SLV_SEQUENCE	= 0 AND SL_NAME = ?', $sysLabelName) as $resultRow) {
            $result[] = new Minder_LabelPrinter_LabelField($resultRow);
        };

        return $result;
    }

    public function getSysLabelFields() {
        if (empty($this->_sysLabelFields)) {
            $this->_sysLabelFields = $this->_fetchSysLabelFields();
        }

        return $this->_sysLabelFields;
    }

    protected function _fetchSysLabelTables() {
        $result = array();
        foreach ($this->getSysLabelFields() as $sysLabelField) {
            /**
             * @var Minder_LabelPrinter_LabelField $sysLabelField
             */

            if ($sysLabelField->hasTable())
                $result[$sysLabelField->getTable()] = array();
        }

        return $result;
    }

    public function getSysLabelTables() {
        if (empty($this->_sysLabelTables)) {
            $this->_sysLabelTables = $this->_fetchSysLabelTables();
        }

        return array_keys($this->_sysLabelTables);
    }

    protected function _fetchTableFields($tableName) {
        $result = array();

        if (empty($tableName))
            return $result;

        foreach ($this->getSysLabelFields() as $sysLabelField) {
            /**
             * @var Minder_LabelPrinter_LabelField $sysLabelField
             */

            if ($sysLabelField->getTable() == $tableName)
                $result[] = $sysLabelField;
        }

        return $result;
    }

    public function getTableFields($tableName) {
        $tables = $this->getSysLabelTables();

        if (empty($tables[$tableName]))
            $tables[$tableName] = $this->_fetchTableFields($tableName);

        return $tables[$tableName];
    }
}