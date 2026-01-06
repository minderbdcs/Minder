<?php

class Minder_Page_SysScreenMapper_Default implements Minder_Page_SysScreenMapper_Interface {

    protected $_sysScreenName = null;
    protected $_formType = null;
    protected $_dbTable = null;

    public function __construct($sysScreenName, $type) {
        $this->_setSysScreenName($sysScreenName)->_setFormType($type);
    }

    protected function _setSysScreenName($sysScreenName) {
        $this->_sysScreenName = $sysScreenName;
        $this->_dbTable = null;
        return $this;
    }

    protected function _setFormType($type) {
        $this->_formType = $type;
        $this->_dbTable = null;
        return $this;
    }

    /**
     * @param string $recordId
     * @return Zend_Db_Table_RowSet
     */
    function find($recordId)
    {
        $args = func_get_args();
        return call_user_func_array(array($this->_getDbTable(), 'find'), $args);
    }

    /**
     * @param array $recordData
     * @return Zend_Db_Table_RowSet
     */
    function findRecord($recordData) {
        $findArgs = array();

        foreach ($this->_getDbTable()->info(Zend_Db_Table::PRIMARY) as $primaryKey) {
            $findArgs[] = isset($recordData[$primaryKey]) ? $recordData[$primaryKey] : '';
        }

        return call_user_func_array(array($this, 'find'), $findArgs);
    }

    function newRecord() {
        return $this->_getDbTable()->createRow();
    }

    protected function _getSchema() {
        switch ($this->_formType) {
            case Minder_Page::FORM_TYPE_EDIT_FORM:
            case Minder_Page::FORM_TYPE_NEW_FORM:
                return 'ER';
            case Minder_Page::FORM_TYPE_SEARCH_RESULT:
                return 'SR';
            default:
                throw new Exception('Bad mapper type: ' . $this->_formType);
        }
    }

    /**
     * @return Minder_Db_SysScreenTable
     */
    protected function _getDbTable() {
        if (is_null($this->_dbTable))
            $this->_dbTable = new Minder_Db_SysScreenTable(array(
                Zend_Db_Table::NAME   => $this->_sysScreenName,
                Zend_Db_Table::SCHEMA => $this->_getSchema()
            ));

        return $this->_dbTable;
    }

    /**
     * @param string $fieldName
     * @param array $fieldsMetadata
     * @return bool
     */
    protected function _isEditableField($fieldName, $fieldsMetadata) {
        if (!isset($fieldsMetadata[$fieldName]))
            return false;

        if (!isset($fieldsMetadata[$fieldName]['SSV_INPUT_METHOD']))
            return false;

        return !in_array($fieldsMetadata[$fieldName]['SSV_INPUT_METHOD'], array('RO', 'NONE'));
    }

    /**
     * @param array $updatedData
     * @param Zend_Db_Table_Row_Abstract $originalRow
     * @return array
     */
    protected function _getModifiedFields($updatedData, $originalRow) {
        $result = array();
        $fieldsMetadata = $originalRow->getTable()->info(Zend_Db_Table::METADATA);

        foreach ($updatedData as $fieldName => $fieldValue) {
            if (!$this->_isEditableField($fieldName, $fieldsMetadata))
                continue;

            if ($fieldValue === $originalRow[$fieldName])
                continue;

            $result[$fieldName] = $fieldValue;
        }

        return $result;
    }


    protected function _getUpdateTransactions($fieldName) {
        //todo
        return array();
    }

    /**
     * @param string $fieldName
     * @param Minder_Db_SysScreenTable $dbTable
     * @return Minder_Page_SysScreenMapper_UpdatePlan_Table
     */
    protected function _getUpdateTable($fieldName, $dbTable) {
        $realTableName     = $dbTable->getRealFieldTableName($fieldName);
        $realTableMetadata = $dbTable->describeRealTable($realTableName);
        $realFieldName     = $dbTable->getRealFieldColumnName($fieldName);

        if (!isset($realTableMetadata[Zend_Db_Table::METADATA][$realFieldName]))
            throw new Exception($realFieldName . ' is not in ' . $realTableName);

        $result        = new Minder_Page_SysScreenMapper_UpdatePlan_Table($realTableName);
        $result->pKeys = array_flip($realTableMetadata[Zend_Db_Table::PRIMARY]);
        array_walk($result->pKeys, create_function('&$item, $key', '$item = null;'));
        $result->fields[$realFieldName] = null;

        return $result;
    }

    /**
     * @param Minder_Db_SysScreenTable $dbTable
     * @param array $modifiedFields
     * @return Minder_Page_SysScreenMapper_UpdatePlan
     */
    protected function _prepareUpdatePlan($dbTable, $modifiedFields) {
        $result = new Minder_Page_SysScreenMapper_UpdatePlan();

        foreach ($modifiedFields as $fieldName => $fieldValue) {
            $uptadeTransactions = $this->_getUpdateTransactions($fieldName);

            if (empty($uptadeTransactions)) {
                $result->addTable($this->_getUpdateTable($fieldName, $dbTable));
            }
        }

        return $result;
    }

    /**
     * @param Zend_Db_Table_Row_Abstract $originalRow
     * @param array $fieldsList
     */
    protected function _fetchAdditionalFields($originalRow, $fieldsList) {
        /**
         * @var Minder_Db_SysScreenTable $dbTable
         */
        $dbTable = $originalRow->getTable();
        $select = $originalRow->select()->columns($fieldsList);

        foreach ($originalRow->getTable()->info(Zend_Db_Table::PRIMARY) as $fieldName) {
            $fieldMetadata = $dbTable->describeField($fieldName);

            if (is_null($originalRow[$fieldName])) {
                $select->where($fieldMetadata['SSV_TABLE'] . '.' . $fieldMetadata['SSV_NAME'] . ' IS NULL');
            } else {
                $select->where($fieldMetadata['SSV_TABLE'] . '.' . $fieldMetadata['SSV_NAME'] . ' = ?', $originalRow[$fieldName]);
            }
        }

        $queryResult = $select->query()->fetch();

        if (empty($queryResult))
            throw new Exception('Row not found.');

        $rowClass = $dbTable->getRowClass();
        return new $rowClass(array('data' => $queryResult, 'table' => $dbTable));
    }

    /**
     * @param Minder_Page_SysScreenMapper_UpdatePlan $updatePlan
     * @return \Minder_Page_SysScreenMapper_Default
     */
    protected function _executeUpdatePlan($updatePlan) {
        foreach ($updatePlan->getTables() as $table) {
            /**
             * @var Minder_Page_SysScreenMapper_UpdatePlan_Table $table
             */

            $query = "UPDATE " . $table->getName() . ' SET ';
            $args = array();
            $fields = array();
            $conditions = array();
            foreach ($table->fields as $fieldName => $fieldValue) {
                if (is_null($fieldValue)) {
                    $fields[] = $fieldName . ' = NULL';
                } else {
                    $fields[] = $fieldName . ' = ?';
                    $args[] = $fieldValue;
                }
            }

            foreach ($table->pKeys as $fieldName => $fieldValue) {
                if (is_null($fieldValue)) {
                    $conditions[] = $fieldName . ' IS NULL';
                } else {
                    $conditions[] = $fieldName . ' = ?';
                    $args[] = $fieldValue;
                }
            }

            $query .= implode(', ', $fields) . ' WHERE ' . implode(' AND ', $conditions);

            $this->_getDbTable()->getAdapter()->query($query, $args);
        }

        return $this;
    }

    /**
     * @param $rowData
     * @return Zend_Db_Table_Row_Abstract
     * @throws Exception
     */
    public function update($rowData) {
        $existedRecords = $this->findRecord($rowData);

        if ($existedRecords->count() < 1)
            throw new Exception('Record not found.');

        $originalRow = $existedRecords->current();
        $modifiedFields = $this->_getModifiedFields($rowData, $originalRow);
        $updatedRow = $originalRow->setFromArray($modifiedFields);

        if (count($modifiedFields) < 1)
            return $updatedRow;

        /**
         * @var Minder_Db_SysScreenTable $dbTable
         */
        $dbTable = $originalRow->getTable();

        $updatePlan = $this->_prepareUpdatePlan($dbTable, $modifiedFields);
        $emptyKeys  = $updatePlan->fillKeys($originalRow->toArray(), $dbTable)->getEmptyKeys($dbTable);

        if (count($emptyKeys) > 0) {
            $originalRow = $this->_fetchAdditionalFields($originalRow, $emptyKeys);
            $updatePlan->fillKeys($originalRow->toArray(), $dbTable);
        }

        $updatePlan->fillValues($modifiedFields, $dbTable);

        $this->_executeUpdatePlan($updatePlan);

        return $updatedRow;
    }
}