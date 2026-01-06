<?php

abstract class Minder_LabelPrinter_Abstract implements Minder_LabelPrinter_Interface {
    /**
     * @var Minder_JSResponse
     */
    protected $_resultObject = null;

    /**
     * @var array
     */
    protected $_labelIds = array();

    /**
     * @var Minder_Printer_Abstract
     */
    protected $_printer      = null;

    /**
     * @var Minder_LabelPrinter_LabelDescriptor
     */
    protected $_labelDescriptor = null;

    protected $_labelType = null;

    function __construct($labelType)
    {
        $this->_setLabelType($labelType);
    }


    /**
     * @param Minder_JSResponse|null $val
     * @return Minder_LabelPrinter_Abstract
     */
    protected function _setResultObject(Minder_JSResponse $val = null) {
        $this->_resultObject = $val;
        return $this;
    }

    /**
     * @return Minder_JSResponse
     */
    protected function _getResultObject() {
        if (is_null($this->_resultObject))
            $this->_resultObject = new Minder_JSResponse();

        return $this->_resultObject;
    }

    /**
     * @param Minder_Printer_Abstract|null $val
     * @return Minder_LabelPrinter_Abstract
     */
    protected function _setPrinter($val) {
        $this->_printer = $val;
        return $this;
    }

    /**
     * @return Minder_Printer_Abstract
     */
    protected function _getPrinter() {
        if (is_null($this->_printer))
            $this->_printer = $this->_getMinder()->getPrinter();

        return $this->_printer;
    }

    /**
     * @return Minder
     */
    protected function _getMinder() {
        return Minder::getInstance();
    }

    /**
     * @param string|array $labelIds
     * @return Minder_LabelPrinter_Abstract
     */
    protected function _setLabelIds($labelIds) {
        $this->_labelIds = is_array($labelIds) ? $labelIds : array($labelIds);
        return $this;
    }

    /**
     * @return array
     */
    protected function _getLabelIds() {
        return $this->_labelIds;
    }

    /**
     * @return Minder_LabelPrinter_LabelDescriptor
     */
    protected function _getLabelDescriptor() {
        if (is_null($this->_labelDescriptor))
            $this->_labelDescriptor = new Minder_LabelPrinter_LabelDescriptor($this->_getPrinter(), $this->_getLabelType());

        return $this->_labelDescriptor;
    }

    protected function _getSysLabelTables() {
        return $this->_getLabelDescriptor()->getSysLabelTables();
    }

    protected function _getTableFields($tableName) {
        $result = array();
        foreach ($this->_getLabelDescriptor()->getTableFields($tableName) as $sysLabelField) {
            /**
             * @var Minder_LabelPrinter_LabelField $sysLabelField
             */
            $result[] = $sysLabelField->getTable() . '.' . $sysLabelField->getFieldName();
        }

        return $result;
    }

    protected function _fetchSysLabelData($labelId) {
        $result = array();
        foreach ($this->_getSysLabelTables() as $tableName) {
            $fields    = $this->_getTableFields($tableName);
            $labelData = $this->_fetchLabelDataFromTable($tableName, $labelId);

            if (empty($labelData)) {
                $labelData = array_fill_keys($fields, '');
            } else {
                $labelData = empty($fields) ? $labelData : array_intersect_key($labelData, array_flip($fields));
            }


            $result = array_merge($labelData, $result);
        }

        return $result;
    }

    protected function _doPrint($labelData) {
        $printResult = $this->_printLabel($labelData);

        if ($printResult['RES'] < 0) {
            $this->_getResultObject()->errors[] = ' Error printing ' . $this->_getLabelType() . ': ' . $printResult['ERROR_TEXT'];
            return false;
        } else {
            return true;
        }
    }

    protected function _formatLabelId($labelId) {
        return $labelId;
    }

    public function doPrint($labelIds, $prinder, Minder_JSResponse $resultObject = null) {
        $this->_setLabelIds($labelIds)->_setResultObject($resultObject)->_setPrinter($prinder);
        $printedAmount = 0;
        foreach($this->_getLabelIds() as $labelId) {
            try {
                if ($this->_doPrint($this->_fetchSysLabelData($labelId)))
                    $printedAmount++;
            } catch (Exception $e) {
                $this->_getResultObject()->errors[] = 'Error printing ' . $this->_getLabelType() . ' #' . $this->_formatLabelId($labelId) . ': ' . $e->getMessage();
            }
        }

        $this->_getResultObject()->messages[] = $printedAmount . ' ' . $this->_getLabelType() . ' label(s) was printed.';
        return $this->_getResultObject();
    }

    protected function _getAllFields() {
        $result = array();
        foreach ($this->_getLabelDescriptor()->getSysLabelFields() as $sysLabelField) {
            /**
             * @var Minder_LabelPrinter_LabelField $sysLabelField
             */
            $result[] = $sysLabelField->hasTable() ? $sysLabelField->getTable() . '.' . $sysLabelField->getFieldName() : $sysLabelField->getFieldName();
        }

        return $result;
    }

    /**
     * @param array $labelData
     * @param Minder_Printer_Abstract $printer
     * @param Minder_JSResponse $resultObject
     * @return Minder_JSResponse
     */
    public function directPrint($labelData, $printer, $resultObject = null)
    {
        $this->_setPrinter($printer)->_setResultObject($resultObject);
        $fields = array_flip($this->_getAllFields());
        $printedAmount = 0;

        foreach ($labelData as $dataRow) {
            try {
                $dataRow = empty($fields) ? $dataRow : array_intersect_key($dataRow, $fields);
                if ($this->_doPrint($dataRow))
                    $printedAmount++;
            } catch (Exception $e) {
                $this->_getResultObject()->errors[] = 'Error printing ' . $this->_getLabelType() . ': ' . $e->getMessage();
            }
        }

        if(count($labelData) == 1 && isset($labelData[0]["COST_CENTRE.labelqty"])){
            $printedAmount = $labelData[0]["COST_CENTRE.labelqty"];
        }

        $this->_getResultObject()->messages[] = $printedAmount . ' ' . $this->_getLabelType() . ' label(s) was printed.';
        return $this->_getResultObject();
    }

    abstract protected function _fetchLabelDataFromTable($tableName, $labelId);

    abstract protected function _printLabel($labeldata);

    protected function _getLabelType() {
        return $this->_labelType;
    }

    protected function _setLabelType($type) {
        $this->_labelType = $type;
    }
}