<?php

class Minder_Process_ImportMappedOrders_MapField extends Zend_Db_Table_Row
{

    protected $_columnIndex = null;

    protected function _calculateColumnIndex()
    {
        $tmpColumn = $this->offsetGet('MAP_IMPORT_COL');
        $realColumn = 0;
        $base = 1;
        $offset = 0;

        while (strlen($tmpColumn) > 0) {
            $nextChar = substr($tmpColumn, -1);
            $tmpColumn = substr($tmpColumn, 0, -1);

            $realColumn += (ord($nextChar) - 65 + $offset) * $base;
            $offset = 1;
            $base *= 26;
        }

        return $realColumn;
    }

    public function getColumnIndex()
    {
        if (is_null($this->_columnIndex))
            $this->_columnIndex = $this->_calculateColumnIndex();
        return $this->_columnIndex;
    }

    public function getParamNames() {
        $val = $this->offsetGet('MAP_IMS_DATA_ID');
        return empty($val) ? array() : explode('|', $val);
    }

    public function getFieldName()
    {
        return $this->offsetGet('MAP_IMS_FIELDNAME');
    }

    public function getTable() {
        return $this->offsetGet('MAP_IMS_TABLE');
    }

    public function formatValue($rawValue)
    {
        return empty($rawValue) ? $this->offsetGet('MAP_IMS_DEFAULT') : $this->_formatValue($rawValue);
    }

    protected function _doReplace($value) {
        return str_replace($this->offsetGet('MAP_IMS_FIND'), $this->offsetGet('MAP_IMS_REPLACE'), $value);
    }

    protected function _parseDateFormat($dateFormat) {
        return (strtoupper($dateFormat) == 'DD-MONTH-YYYY') ? (Zend_Date::DAY . '-' . Zend_Date::MONTH_NAME . '-' . Zend_Date::YEAR) : $dateFormat;
    }

    protected function _applyFormat($value) {
        if ($this->offsetGet('MAP_IMS_FIELDTYPE') != 35)
            return $value;

        $dateFormat = $this->offsetGet('MAP_IMS_FORMAT');

        if (empty($dateFormat)) {
            return $value; //no conversion needed
        }

        try {
            //todo: use global locale setup
            $date = new Zend_Date($value, $this->_parseDateFormat($dateFormat), 'en_AU');
        } catch (Exception $e) {
            throw new Minder_Exception('Error converting date "' . $value . '" using date format "' . $dateFormat . '". ' . $e->getMessage());
        }

        return $date->toString(Zend_Date::YEAR . '-' . Zend_Date::MONTH . '-' . Zend_Date::DAY . ' ' . Zend_Date::HOUR . ':' . Zend_Date::MINUTE . ':' . Zend_Date::SECOND);
    }

    private function _formatValue($value)
    {
        $filteredValue = $this->_applyFormat($this->_doReplace(trim($value)));
        return $this->offsetGet('MAP_IMS_PREFIX') . $filteredValue . $this->offsetGet('MAP_IMS_SUFFIX');
    }
}