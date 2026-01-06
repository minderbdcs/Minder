<?php

class Minder_SysScreen_View implements Minder_SysScreen_Model_Interface {

    /**
     * @var Minder_SysScreen_View_SourceInterface
     */
    protected $_viewSource;

    /**
     * Set conditions for query. Replaces existent condotions.
     *
     * @param array $conditions - conditions array
     * @return Minder_SysScreen_Model_Interface
     */
    public function setConditions($conditions = array())
    {
        return $this->_getViewSource()->setConditions($conditions);
    }

    /**
     * Add conditions for query.
     *
     * @param array $conditions - conditions array
     * @return Minder_SysScreen_Model_Interface
     */
    public function addConditions($conditions = array())
    {
        return $this->_getViewSource()->addConditions($conditions);
    }

    /**
     * Remove conditions for query.
     *
     * @param array $conditions - conditions array. If empty remove all conditions.
     * @return Minder_SysScreen_Model_Interface
     */
    public function removeConditions($conditions = array())
    {
        return $this->_getViewSource()->removeConditions($conditions);
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
        if ($getPKeysOnly) {
            $query = $this->_getViewSource()->getSelectQuery($rowOffset + $itemCountPerPage);
        } else {
            $query = $this->_getViewSource()->getSelectQuery($rowOffset + $itemCountPerPage);
        }

        $args = array_merge(array($query->query), $query->args);

        try {
            $fetchedRows = call_user_func_array(array($this->_getMinder(), 'fetchAllAssoc'), $args);
            $fetchedRows = array_slice($fetchedRows, $rowOffset, $itemCountPerPage);
        } catch (Exception $e) {
            throw $e;
        }
        $result = array();

        if (is_array($fetchedRows)) {
            foreach ($fetchedRows as $row)
                $result[$row[$this->_getViewSource()->getPrimaryKeyAlias()]] = $row;
        }

        return $result;
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
        $result = 0;

        try {
            $viewSource = $this->_getViewSource();

            if ($viewSource instanceof Countable) {
                $result = count($viewSource);
            } else {
                foreach ($this->_getViewSource()->getCountQueries() as $countQuery) {
                    $args = array_merge(array($countQuery->query), $countQuery->args);
                    $result += (int)call_user_func_array(array($this->_getMinder(), 'findValue'), $args);
                }
            }
        } catch (Exception $e) {
            throw $e;
        }

        return $result;
    }

    /**
     * @return Minder_SysScreen_View_SourceInterface
     */
    protected function _getViewSource()
    {
        return $this->_viewSource;
    }

    /**
     * @param Minder_SysScreen_View_SourceInterface $viewSource
     * @return $this
     */
    public function setViewSource(Minder_SysScreen_View_SourceInterface $viewSource)
    {
        $this->_viewSource = $viewSource;
        return $this;
    }

    protected function _getMinder() {
        return Minder::getInstance();
    }

    public function removeMasterSelectionConditions()
    {
        $this->_getViewSource()->removeMasterSelectionConditions();
    }

    public function setEmptyMasterSelectionConditions()
    {
        $this->_getViewSource()->setEmptyMasterSelectionConditions();
    }

    public function createEmptyMasterSelectionConditions()
    {
        return $this->_getViewSource()->createEmptyMasterSelectionConditions();
    }

    public function addMasterSelectionConditions($conditions)
    {
        $this->_getViewSource()->addMasterSelectionConditions($conditions);
    }

    public function createMasterSelectionConditions($relation, $filterValues)
    {
        $this->_getViewSource()->createMasterSelectionConditions($relation, $filterValues);
    }

    public function makeConditionsFromSearch($searchFields = array())
    {
        return $this->_getViewSource()->makeConditionsFromSearch($searchFields);
    }

    public function setCustomOrderFields($sortFields = array())
    {
        return $this->_getViewSource()->setCustomOrderFields($sortFields);
    }

    public function setOrder(array $value = array())
    {
        return $this->_getViewSource()->setOrder($value);
    }

    public function makeConditionsFromId($ids = '', $exlude = false)
    {
        return $this->_getViewSource()->makeConditionsFromId($ids, $exlude);
    }

    public function getConditions() {
        return $this->_getViewSource()->getConditions();
    }

    public function selectForeignKeyValues($relation, $offset, $limit)
    {
        return $this->_getViewSource()->selectForeignKeyValues($relation, $offset, $limit);
    }

    public function getPKeyAlias()
    {
        return $this->_getViewSource()->getPrimaryKeyAlias();
    }

    /**
     * @param string $mode
     * @return Minder_SysScreen_Model_MasterSlaveInterface
     */
    public function startMasterSelectionConditions($mode = Minder_SysScreen_ModelCondition::OPERATOR_OR)
    {
        $this->_getViewSource()->startMasterSelectionConditions();
        return $this;
    }

    /**
     * @param $relation
     * @param $filterValues
     * @return Minder_SysScreen_Model_MasterSlaveInterface
     */
    public function createAndAddMasterSelectionConditions($relation, $filterValues)
    {
        if ($relation['SLAVE_SCREEN'] == $this->_getViewSource()->getName()) {
            $this->_getViewSource()->createAndAddMasterSelectionConditions($relation, $filterValues);
        }
        return $this;
    }

    /**
     * @param $relation
     * @return Minder_SysScreen_Model_MasterSlaveInterface
     */
    public function addEmptyMasterSelectionConditions($relation)
    {
        if ($relation['SLAVE_SCREEN'] == $this->_getViewSource()->getName()) {
            $this->_getViewSource()->addEmptyMasterSelectionConditions($relation);
        }
        return $this;
    }

    /**
     * @return Minder_SysScreen_Model_MasterSlaveInterface
     */
    public function applyMasterSelectionConditions()
    {
        $this->_getViewSource()->applyMasterSelectionConditions();
        return $this;
    }

    public function setStaticConditions($conditions = array())
    {
        $this->_getViewSource()->setStaticConditions($conditions);
    }
}