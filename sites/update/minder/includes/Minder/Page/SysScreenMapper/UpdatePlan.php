<?php

class Minder_Page_SysScreenMapper_UpdatePlan {
    protected $_transactions = array();
    protected $_tables = array();

    protected function _getTable($tableName) {
        $tables = $this->getTables();
        return isset($tables[$tableName]) ? $tables[$tableName] : new Minder_Page_SysScreenMapper_UpdatePlan_Table($tableName);
    }

    /**
     * @param Minder_Page_SysScreenMapper_UpdatePlan_Table $table
     * @return Minder_Page_SysScreenMapper_UpdatePlan
     */
    protected function _setTable(Minder_Page_SysScreenMapper_UpdatePlan_Table $table) {
        $this->_tables[$table->getName()] = $table;

        return $this;
    }

    /**
     * @param Minder_Page_SysScreenMapper_UpdatePlan_Table $table
     * @return Minder_Page_SysScreenMapper_UpdatePlan
     */
    public function addTable(Minder_Page_SysScreenMapper_UpdatePlan_Table $table) {
        $this->_setTable($this->_getTable($table->getName())->merge($table));
        return $this;
    }

    public function setKey($tableName, $fieldName, $value) {
        $this->_setTable($this->_getTable($tableName)->setKey($fieldName, $value));
        return $this;
    }

    /**
     * @param Minder_Db_SysScreenTable $dbTable
     * @return array
     */
    public function getEmptyKeys($dbTable) {
        $result = array();
        foreach ($this->getTables() as $table) {
            /**
             * @var Minder_Page_SysScreenMapper_UpdatePlan_Table $table
             */

            $result = array_merge($result, $table->getEmptyKeys($dbTable));
        }

        return $result;
    }

    public function setField($tableName, $fieldName, $value) {
        $this->_setTable($this->_getTable($tableName)->setField($fieldName, $value));
        return $this;
    }

    public function getTables() {
        return $this->_tables;
    }

    /**
     * @param array $rowData
     * @param Minder_Db_SysScreenTable $dbTable
     * @return Minder_Page_SysScreenMapper_UpdatePlan
     */
    public function fillKeys($rowData, $dbTable) {
        foreach ($rowData as $fieldName => $fieldValue) {
            $realFieldName = $dbTable->getRealFieldColumnName($fieldName);
            $realTableName = $dbTable->getRealFieldTableName($fieldName);
            $this->setKey($realTableName, $realFieldName, $fieldValue);
        }

        return $this;
    }

    /**
     * @param array $rowData
     * @param Minder_Db_SysScreenTable $dbTable
     * @return Minder_Page_SysScreenMapper_UpdatePlan
     */
    public function fillValues($rowData, $dbTable) {
        foreach ($rowData as $fieldName => $fieldValue) {
            $realFieldName = $dbTable->getRealFieldColumnName($fieldName);
            $realTableName = $dbTable->getRealFieldTableName($fieldName);
            $this->setField($realTableName, $realFieldName, $fieldValue);
        }

        return $this;
    }
}