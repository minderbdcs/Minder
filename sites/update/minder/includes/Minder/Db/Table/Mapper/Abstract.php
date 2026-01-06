<?php

abstract class Minder_Db_Table_Mapper_Abstract {
    /**
     * @var Zend_Db_Table_Abstract
     */
    protected $_dbTable = null;

    public function setDbTable($table) {
        if (is_string($table))
            $table = new $table();

        if (!$table instanceof Zend_Db_Table_Abstract)
            throw new Exception('Table should be instanceof Zend_Db_Table_Abstract.');

        $this->_dbTable = $table;
    }

    /**
     * @return Zend_Db_Table_Abstract
     */
    public function getDbTable() {
        if (is_null($this->_dbTable))
            $this->setDbTable($this->getDefaultTableClassName());

        return $this->_dbTable;
    }

    /**
     * @abstract
     * @return string
     */
    abstract public function getDefaultTableClassName();

    /**
     * @return Minder2_Model_Company
     */
    protected function _getCurrentCompany() {
        return Minder2_Environment::getInstance()->getCurrentCompany();
    }

    /**
     * @return Minder2_Model_Warehouse
     */
    protected function _getCurrentWarehouse() {
        return Minder2_Environment::getCurrentWarehouse();
    }

    /**
     * @return Minder2_Model_SysEquip
     */
    protected function _getCurrentDevice() {
        return Minder2_Environment::getCurrentDevice();
    }

    protected function _getCurrentUser() {
        return Minder2_Environment::getCurrentUser();
    }

    /**
     * @param $sql
     * @return Zend_Db_Statement_Interface
     */
    protected function _query($sql) {
        $bind = func_get_args();
        array_shift($bind);

        return $this->getDbTable()->getAdapter()->query($sql, $bind);
    }

    /**
     * @param $sql
     * @param mixed $_ [optional]
     * @return Zend_Db_Table_Rowset_Abstract
     */
    protected function _fetchAll($sql, $_ = null) {
        $args = func_get_args();
        /**
         * @var Zend_Db_Statement_Interface $queryResult
         */
        $queryResult = call_user_func_array(array($this, '_query'), $args);

        $dbTable = $this->getDbTable();

        $rowsetClass = $dbTable->getRowsetClass();
        $rowClass    = $dbTable->getRowClass();

        return new $rowsetClass(array('table' => $dbTable, 'rowClass' => $rowClass, 'data' => $queryResult->fetchAll(Zend_Db::FETCH_ASSOC), 'stored' => true));
    }
}
