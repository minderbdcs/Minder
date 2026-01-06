<?php

class Minder_Page_SysScreenMapper_Ssn extends Minder_Page_SysScreenMapper_Default {
    public function __construct($type)
    {
        parent::__construct('SSN', $type);
    }

    protected function _getVirtualFields($realTableName, $realFieldName) {
        $dbTable = $this->_getDbTable();
        $realFieldName = strtoupper($realFieldName);
        $realTableName = strtoupper($realTableName);

        $result = array();

        foreach ($dbTable->info(Zend_Db_Table::METADATA) as $fieldAlias => $fieldMetadata) {
            $tableName = $dbTable->getRealFieldTableName($fieldAlias);
            $fieldName = $dbTable->getRealFieldColumnName($fieldAlias);

            if ($tableName == $realTableName && $fieldName == $realFieldName)
                $result[] = $fieldAlias;
        }

        return $result;
    }

    protected function _fetchSsnId($data) {
        $dbTable = $this->_getDbTable();
        $rowClass = $dbTable->getRowClass();

        $row = new $rowClass(array('data' => $data, 'table' => $dbTable));
        $ssnId = '';
        $fieldToFetch = $dbTable->formatRealFieldIndex('SSN', 'SSN_ID');

        try {
            $row = $this->_fetchAdditionalFields($row, array($fieldToFetch));
            $ssnId = $row[$fieldToFetch];
        } catch (Exception $e) {
        }

        return $ssnId;
    }

    public function getSsnId($data) {

        foreach ($this->_getVirtualFields('SSN', 'SSN_ID') as $fieldAlias) {
            if (isset($data[$fieldAlias]))
                return $data[$fieldAlias];
        }

        return $this->_fetchSsnId($data);
    }
}