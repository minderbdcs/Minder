<?php

class Minder2_DataSet_SysScreenModel implements Minder2_DataSet_Interface {
    protected $_model;
    protected $_datasetRegister;

    protected $_slaveDataSets = array();

    protected $_masterDataSets = array();

    protected $_tableKeys = array();

    function __construct(Minder_SysScreen_Model $model, $datasetRegister = null)
    {
        $this->_model = $model;
        $this->_datasetRegister = $datasetRegister;
    }

    public function count() {
        return count($this->_model);
    }

    public function getItems($rowOffset = null, $itemCountPerPage = null, $conditions = null) {
        if (is_null($itemCountPerPage))
            $itemCountPerPage = count($this);

        $rowOffset = is_null($rowOffset) ? 0 : $rowOffset;

        $result = array();

        if (!is_null($conditions))
            $this->_model->setConditionObject($conditions);

        $items = $this->_model->getItems($rowOffset, $itemCountPerPage);
        foreach ($items as &$row) {
            $row['__rowId'] = $row[$this->_model->getPKeyAlias()];
            $result[] = $row;
        }

        return $result;
    }

    public function fetchFields($fields, $conditions = null, $rowOffset = null, $itemCountPerPage = null, $distinct = false)
    {
        $this->_model->setConditionObject($conditions);

        $selectExpression = ($distinct) ? 'DISTINCT ' . implode(', ', $fields) : implode(', ', $fields);

        return $this->_model->selectArbitraryExpression($rowOffset, $itemCountPerPage, $selectExpression);
    }

    public function fetchAggregatedValue($field, $conditions = null)
    {
        $this->_model->setConditionObject($conditions);
        return $this->_model->getAggregateValue($field);
    }

    public function countRows($extraConditions = null)
    {
        $this->_model->setConditionObject($extraConditions);
        return count($this->_model);
    }

    public function makeFindConditions($rows, $exclude = false)
    {
        return new Minder_SysScreen_ModelCondition($this->_model->makeConditionsFromId($rows, $exclude));
    }

    /**
     * @param array $searchFieldsDescription
     * @return Minder_SysScreen_ModelCondition
     */
    public function makeConditionsFromSearch($searchFieldsDescription)
    {
        $conditions = $this->_model->makeConditionsFromSearch($searchFieldsDescription);
        if ($conditions instanceof Minder_SysScreen_ModelCondition)
            return $conditions;

        return new Minder_SysScreen_ModelCondition($conditions);
    }

    protected function _aliasExists($alias) {
        foreach ($this->_model->getFields() as $field) {
            if ($field['SSV_ALIAS'] == $alias) {
                return $field;
            }
        }
        return false;
    }

    protected function _fieldExists($fieldName) {
        foreach ($this->_model->getFields() as $field) {
            if ($field['SSV_NAME'] == $fieldName) {
                return $field;
            }
        }
        return false;
    }

    protected function _fetchTableKeys($table) {
        $sql = "
            SELECT
                RC.RDB\$RELATION_NAME AS TABLE_NAME,
                RF.RDB\$FIELD_NAME AS FIELD_NAME
            FROM
                RDB\$RELATION_FIELDS RF
                LEFT JOIN RDB\$RELATION_CONSTRAINTS RC ON RF.RDB\$RELATION_NAME = RC.RDB\$RELATION_NAME
                LEFT JOIN RDB\$INDEX_SEGMENTS IDX ON RF.RDB\$FIELD_NAME = IDX.RDB\$FIELD_NAME AND RC.RDB\$INDEX_NAME = IDX.RDB\$INDEX_NAME
            WHERE
                RC.RDB\$CONSTRAINT_TYPE = 'PRIMARY KEY'
                AND RC.RDB\$RELATION_NAME = ?
                AND NOT IDX.RDB\$FIELD_POSITION IS NULL
        ";

        return Minder::getInstance()->fetchAllAssoc($sql, $table);
    }

    protected function _getTableKeys($table) {
        if (!isset($this->_tableKeys[$table])) {
            $this->_tableKeys[$table] = $this->_fetchTableKeys($table);
        }

        return $this->_tableKeys[$table];
    }

    protected function _fillKeys($updatedRow, $originalRow) {
        $result = array();
        foreach ($updatedRow as $table => $fields) {
            foreach ($this->_getTableKeys($table) as $keyDescription) {
                $keyDescription['FIELD_NAME'] = trim($keyDescription['FIELD_NAME']);
                if (false === ($fieldDesc = $this->_fieldExists($keyDescription['FIELD_NAME']))) {
                    return array();
                }

                $result[$table][$keyDescription['FIELD_NAME']] = $originalRow[$fieldDesc['SSV_ALIAS']];
            }
        }

        return $result;
    }

    protected function _findRow($row) {
        $conditions = $this->makeFindConditions($row['__rowId']);
        $originalRow = $this->getItems(0, 1, $conditions);
        if (count($originalRow) < 1)
            throw new Exception('Row #' . $row['__rowId'] . ' not found.');

        return current($originalRow);
    }

    protected function _fillUpdateRow($row, $originalRow) {
        $result = array();

        foreach ($row as $fieldName => $fieldValue) {
            if (isset($originalRow[$fieldName]) && $fieldValue != $originalRow[$fieldName]) {

                if (false ===($fieldDesc = $this->_aliasExists($fieldName)))
                    continue;

                if (empty($fieldDesc['SSV_TABLE']))
                    continue;

                $result[$fieldDesc['SSV_TABLE']][$fieldName] = $fieldValue;
            }
        }

        return $result;
    }

    protected function _prepareUpdateQuery($table, $fields, $keys) {
        $tmpFieldsUpdateExpression = array();
        $args = array();
        foreach ($fields as $fieldName => $fieldValue) {
            $tmpFieldsUpdateExpression[] = $fieldName . ' = ?';
            $args[] = $fieldValue;
        }

        $tmpKeyExpression = array();

        foreach ($keys as $key => $value) {
            $tmpKeyExpression[] = $key . ' = ?';
            $args[] = $value;
        }

        $query = 'UPDATE ' . $table . ' SET ' . implode(', ', $tmpFieldsUpdateExpression) . ' WHERE ' . implode(' AND ', $tmpKeyExpression);
        array_unshift($args, $query);

        return $args;
    }

    protected function _executeUpdateQuery($query) {
        if (false === Minder::getInstance()->execSQL(array_shift($query), $query)) {
            throw new Exception(Minder::getInstance()->lastError);
        }
    }

    protected function _doUpdate($updatedRow, $keys) {
        foreach($updatedRow as $table => $fields) {
            $this->_executeUpdateQuery($this->_prepareUpdateQuery($table, $fields, $keys[$table]));
        }
    }

    protected function _updateRow($row) {
        $originalRow = $this->_findRow($row);
        $updatedRow  = $this->_fillUpdateRow($row, $originalRow);
        $keys        = $this->_fillKeys($updatedRow, $originalRow);
        $this->_doUpdate($updatedRow, $keys);
    }

    public function update($rows) {
        $result = new Minder_JSResponse();
        $result->items = array();
        $updatedRows = array();

        foreach ($rows as $row) {
            try {
                $this->_updateRow($row);
                unset($row['__changed']);
                $updatedRows[] = $row;
            } catch (Exception $e) {
                $result->errors[] = 'Error updating row #' . $row['__rowId'] . ': ' . $e->getMessage();
            }
            $result->items[] = $row;
        }

        if (count($updatedRows) > 0) {
            $result->messages[] = count($updatedRows) . ' row(s) updated.';
        }
        return $result;
    }
}