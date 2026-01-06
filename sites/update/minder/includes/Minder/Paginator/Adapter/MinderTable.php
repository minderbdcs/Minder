<?php

class Minder_Paginator_Adapter_MinderTable implements Zend_Paginator_Adapter_Interface {

    protected $_tableName = null;
    protected $_clause    = null;

    protected $_cachedCount = null;

    function __construct($tableName, $clause)
    {
        $this->_setTableName($tableName);
        $this->_setClause($clause);
    }

    protected function _setTableName($tableName) {
        $this->_tableName = strval($tableName);
    }

    protected function _setClause($clause) {
        $this->_clause = $clause;
    }

    protected function _getTableName() {
        if (is_null($this->_tableName))
            $this->_setTableName('');

        return $this->_tableName;
    }

    protected function _getClause() {
        if (is_null($this->_clause))
            $this->_setClause(array());

        return $this->_clause;
    }

    protected function _reduceHelper($initialValue, $valueToAdd) {
        $initialValue = is_null($initialValue) ? array() : $initialValue;
        return array_merge($initialValue, $valueToAdd);
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
        if (is_null($this->_cachedCount)) {
            $sql = "
                SELECT
                    COUNT(*)
                FROM
                    " . $this->_getTableName() . "
            ";

            $args = array();

            if (count($clause = $this->_getClause()) > 0) {
                $conditions = array();
                $args       = array();

                foreach ($clause as $condition) {
                    $conditions[] = key($condition);
                    $args[]       = current(current($condition));
                }

                $sql .= "
                    WHERE
                " . implode(' AND ', $conditions);

            }

            array_unshift($args, $sql);
            $this->_cachedCount = call_user_func_array(array(Minder::getInstance(), 'findValue'), $args);
        }

        return $this->_cachedCount;
    }

    /**
     * Returns an collection of items for a page.
     *
     * @param  integer $offset Page offset
     * @param  integer $itemCountPerPage Number of items per page
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $sql = "
            SELECT FIRST " . $itemCountPerPage . " SKIP " . $offset . "
                *
            FROM
                " . $this->_getTableName() . "
        ";

        $args = array();

        if (count($clause = $this->_getClause()) > 0) {
            $conditions = array();
            $args       = array();

            foreach ($clause as $condition) {
                $conditions[] = key($condition);
                $args[]       = current(current($condition));
            }

            $sql .= "
                WHERE
            " . implode(' AND ', $conditions);

        }

        array_unshift($args, $sql);

        return call_user_func_array(array(Minder::getInstance(), 'fetchAllAssoc'), $args);
    }
}