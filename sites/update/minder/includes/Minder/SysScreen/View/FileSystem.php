<?php

class Minder_SysScreen_View_FileSystem implements Minder_SysScreen_View_SourceInterface, Minder_SysScreen_Model_Interface {
    const PKEY_SEPARATOR = "{-}";

    protected $_paths = array();
    protected $_foundFiles = null;
    protected $_pKeys;
    protected $_condition = array();
    protected $_staticConditions = array();

    function __construct(array $paths)
    {
        $this->_setPaths($paths);
    }

    /**
     * Set conditions for query. Replaces existent conditions.
     *
     * @param array $conditions - conditions array
     * @return Minder_SysScreen_Model_Interface
     */
    public function setConditions($conditions = array())
    {
        $this->_foundFiles = null;
        $this->_condition = $conditions;
        return $this;
    }

    public function getConditions()
    {
        return $this->_condition;
    }

    /**
     * Add conditions for query.
     *
     * @param array $conditions - conditions array
     * @return Minder_SysScreen_Model_Interface
     */
    public function addConditions($conditions = array())
    {
        $this->_foundFiles = null;
        $this->_condition = is_null($this->_condition) ? array() : $this->_condition;
        $this->_condition = array_merge($this->_condition, $conditions);
        return $this;
    }

    /**
     * Remove conditions for query.
     *
     * @param array $conditions - conditions array. If empty remove all conditions.
     * @return Minder_SysScreen_Model_Interface
     */
    public function removeConditions($conditions = array())
    {
        $this->_foundFiles = null;
        // TODO: Implement removeConditions() method.
    }

    public function makeConditionsFromSearch($searchFields = array())
    {
        // TODO: Implement makeConditionsFromSearch() method.
    }

    public function makeConditionsFromId($ids = '', $exlude = false)
    {
        $ids = is_array($ids) ? $ids : array($ids);

        $condition = new Minder_SysScreen_View_FileSystem_FieldValueInList($this->getPrimaryKeyAlias(), $ids);

        if ($exlude) {
            $condition = new Minder_SysScreen_View_FileSystem_Not($condition);
        }

        return array($condition);
    }

    /**
     * Returns an collection of items for a page.
     *
     * @param  integer $offset Rows offset
     * @param  integer $itemCountPerPage Number of items per page
     * @param  boolean $getPKeysOnly get all fields or primary key expression only
     * @return array
     */
    public function getItems($rowOffset, $itemCountPerPage, $getPKeysOnly = false)
    {
        $result = array();
        foreach (array_slice($this->_getFoundFiles(), $rowOffset, $itemCountPerPage) as $row) {
            $result[$row[$this->getPrimaryKeyAlias()]] = $row;
        }

        return $result;
    }

    public function getPKeyAlias()
    {
        return $this->getPrimaryKeyAlias();
    }

    public function removeMasterSelectionConditions()
    {
        // TODO: Implement removeMasterSelectionConditions() method.
    }

    public function setEmptyMasterSelectionConditions()
    {
        // TODO: Implement setEmptyMasterSelectionConditions() method.
    }

    public function createEmptyMasterSelectionConditions()
    {
        // TODO: Implement createEmptyMasterSelectionConditions() method.
    }

    public function addMasterSelectionConditions($conditions)
    {
        // TODO: Implement addMasterSelectionConditions() method.
    }

    public function createMasterSelectionConditions($relation, $filterValues)
    {
        // TODO: Implement createMasterSelectionConditions() method.
    }

    public function selectForeignKeyValues($relation, $offset, $limit)
    {
        // TODO: Implement selectForeignKeyValues() method.
    }

    /**
     * @param string $mode
     * @return Minder_SysScreen_Model_MasterSlaveInterface
     */
    public function startMasterSelectionConditions($mode = Minder_SysScreen_ModelCondition::OPERATOR_OR)
    {
        // TODO: Implement startMasterSelectionConditions() method.
    }

    /**
     * @param $relation
     * @param $filterValues
     * @return Minder_SysScreen_Model_MasterSlaveInterface
     */
    public function createAndAddMasterSelectionConditions($relation, $filterValues)
    {
        // TODO: Implement createAndAddMasterSelectionConditions() method.
    }

    /**
     * @param $relation
     * @return Minder_SysScreen_Model_MasterSlaveInterface
     */
    public function addEmptyMasterSelectionConditions($relation)
    {
        // TODO: Implement addEmptyMasterSelectionConditions() method.
    }

    /**
     * @return Minder_SysScreen_Model_MasterSlaveInterface
     */
    public function applyMasterSelectionConditions()
    {
        // TODO: Implement applyMasterSelectionConditions() method.
    }

    public function setCustomOrderFields($sortFields = array())
    {
        // TODO: Implement setCustomOrderFields() method.
    }

    public function setOrder(array $value = array())
    {
        // TODO: Implement setOrder() method.
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->_getFoundFiles());
    }

    /**
     * @param $limit
     * @param bool $withOrder
     * @return Minder_SysScreen_View_QueryPart
     */
    public function getSelectQuery($limit, $withOrder = true)
    {
        // TODO: Implement getSelectQuery() method.
    }

    public function setFields($fields)
    {
        // TODO: Implement setFields() method.
    }

    public function reorderResultFields($newOrder)
    {
        // TODO: Implement reorderResultFields() method.
    }

    /**
     * @param $limit
     * @return Minder_SysScreen_View_QueryPart
     */
    public function getKeysQuery($limit)
    {
        // TODO: Implement getKeysQuery() method.
    }

    /**
     * @return Minder_SysScreen_View_QueryPart[]
     */
    public function getCountQueries()
    {
        // TODO: Implement getCountQueries() method.
    }

    public function getPrimaryKeyAlias()
    {
        return 'PKEY_EXPR';
    }

    public function getName()
    {
        // TODO: Implement getName() method.
    }

    public function setSearchFields($fields)
    {
        // TODO: Implement setSearchFields() method.
    }

    public function getAllResultFields()
    {
        // TODO: Implement getAllResultFields() method.
    }

    public function init()
    {
        // TODO: Implement init() method.
    }

    public function setStaticConditions($conditions = array())
    {
        $this->_staticConditions = $conditions;
        return $this;
    }

    public function setPrimaryKeys($keys)
    {
        $this->_pKeys = $keys;
    }

    /**
     * @return array
     */
    protected function _getPaths()
    {
        return $this->_paths;
    }

    /**
     * @param array $paths
     * @return $this
     */
    protected function _setPaths($paths)
    {
        $this->_paths = $paths;
        return $this;
    }

    protected function _fetchAll() {
        $result = array();
        foreach($this->_getPaths() as $path) {
            $path = rtrim($path, '/') . '/*';

            foreach(glob($path) as $filePath) {
                $record = $this->_makeRecord($filePath);
                if ($this->_isValid($record)) {
                    $result[] = $record;
                }
            }
        }

        return $result;
    }

    protected function _makeRecord($filePath) {
        $result = pathinfo($filePath);
        $result = array_change_key_case($result, CASE_UPPER);

        $result['FULL_PATH'] = $filePath;

        $pKeyParts = array();
        foreach ($this->_getPKeys() as $pKey) {
            $fullName = Minder_SysScreen_Model::getFieldAlias($pKey);
            $pKeyParts[] = isset($result[$fullName]) ? $result[$fullName] : '';
        }

        $result[$this->getPrimaryKeyAlias()] = implode(static::PKEY_SEPARATOR, $pKeyParts);

        return $result;
    }

    protected function _isValid($rowData) {
        return $this->_isValidAgainstConditionList($rowData, $this->_getStaticConditions())
                && $this->_isValidAgainstConditionList($rowData, $this->getConditions());
    }

    /**
     * @param $rowData
     * @param Minder_SysScreen_View_FileSystem_ConstraintInterface[] $conditions
     * @return bool
     */
    protected function _isValidAgainstConditionList($rowData, $conditions) {
        foreach($conditions as $condition) {
            if (!$condition->isValid($rowData))
                return false;
        }

        return true;
    }

    function __wakeup()
    {
        $this->_foundFiles = null;
    }


    /**
     * @return null
     */
    protected function _getFoundFiles()
    {
        if (is_null($this->_foundFiles)) {
            $this->_foundFiles = $this->_fetchAll();
        }

        return $this->_foundFiles;
    }

    /**
     * @return mixed
     */
    protected function _getPKeys()
    {
        return $this->_pKeys;
    }

    /**
     * @return array
     */
    protected function _getStaticConditions()
    {
        return $this->_staticConditions;
    }


}