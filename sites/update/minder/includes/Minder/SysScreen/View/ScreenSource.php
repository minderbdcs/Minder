<?php

class Minder_SysScreen_View_ScreenSource extends Minder_SysScreen_Model implements Minder_SysScreen_View_SourceInterface {

    protected $_screenName;
    protected $_searchFields;

    public function __construct($screenName)
    {
        $this->_screenName = $screenName;

        parent::__construct();
    }

    public function setMainTable($table) {
        $this->mainTable = $table;
    }

    public function setTables($tables) {
        $this->tables = $tables;
    }

    public function setPrimaryKeys($keys) {
        $this->pkeys = $keys;
    }

    public function setFields($fields) {
        $this->fields = $fields;
    }

    public function setColorFields($fields) {
        $this->colorFields = $fields;
    }

    /**
     * @param $limit
     * @param bool $withOrder
     * @return Minder_SysScreen_View_QueryPart
     */
    public function getSelectQuery($limit, $withOrder = true)
    {
        $result = new Minder_SysScreen_View_QueryPart();

        $tmpFieldsSet =  array($this->getPrimaryIdExpression() . ' AS ' . $this->pkeysExpressionAlias);

        $tmpFieldsSet = array_merge($tmpFieldsSet, array_map(array($this, '__getFieldExpressionWithAlias'), $this->fields), array_map(array($this, '__getFieldExpressionWithAlias'), $this->colorFields));
        $colorArgs    = array_reduce(array_map(create_function('$el', 'return $el["ARGS"];'), $this->colorFields), create_function('$res, $item', '$res = (is_array($res)) ? $res : array(); return array_merge($res, $item);'), null);
        $colorArgs    = (is_array($colorArgs)) ? $colorArgs : array();

        list($where, $args) = $this->__getWhereAndArgs();
        $result->args       = array_merge($colorArgs, $args);

        $selectSQL = 'SELECT ' . $this->_getLimitExpression(0, $limit) . ' ' . $this->_getDistinct() . ' ' . implode(', ', $tmpFieldsSet) . ' FROM ' . $this->getFromExpression() . $where . ' ' . $this->_getGroupByExpression();

        if ($withOrder) {
            $selectSQL .= ' ' . $this->_getOrderByExpression();
        }

        $result->query = $selectSQL;

        return $result;
    }

    /**
     * @return Minder_SysScreen_View_QueryPart[]
     */
    public function getCountQueries()
    {
        $result                         = new Minder_SysScreen_View_QueryPart();

        $result->query                  = 'SELECT count(' . $this->_getDistinct() . ' ' . $this->getPrimaryIdExpression() . ') FROM ' . $this->getFromExpression();
        list($where, $result->args)     = $this->__getWhereAndArgs();

        $result->query                  .= $where;

        return array($result);
    }

    /**
     * @param $limit
     * @return Minder_SysScreen_View_QueryPart
     */
    public function getKeysQuery($limit)
    {
        $result = new Minder_SysScreen_View_QueryPart();

        $tmpFieldsSet =  array($this->getPrimaryIdExpression() . ' AS ' . $this->pkeysExpressionAlias);

        $colorArgs = array();
        $tmpFieldsSet = array_merge($tmpFieldsSet, array_map(array($this, '__getFieldExpressionWithAlias'), $this->pkeys));

        list($where, $args) = $this->__getWhereAndArgs();
        $result->args       = array_merge($colorArgs, $args);

        $result->query = 'SELECT ' . $this->_getLimitExpression(0, $limit) . ' ' . $this->_getDistinct() . ' ' . implode(', ', $tmpFieldsSet) . ' FROM ' . $this->getFromExpression() . $where . ' ' . $this->_getGroupByExpression();

        return $result;
    }

    public function getPrimaryKeyAlias()
    {
        return $this->pkeysExpressionAlias;
    }

    public function getName()
    {
        return $this->_screenName;
    }

    public function setSearchFields($fields)
    {
        $this->_searchFields = $fields;
    }

    protected function _getSearchFields($alias) {
        foreach ($this->_searchFields as $fieldDescription) {
            if ($fieldDescription['SSV_ALIAS'] == $alias) {
                return $fieldDescription;
            }
        }

        return null;
    }

    public function makeConditionsFromId($ids = '', $exlude = false)
    {
        if (!is_array($ids))
            $ids = array($ids);

        $screenName = $this->getName();
        $nameLength = strlen($screenName . static::$pKeySeparator);

        $ids = array_filter($ids, function($id)use($screenName){
            return strpos($id, $screenName) === 0;
        });

        if (empty($ids)) {
            if ($exlude) {
                return array('1=1' => array());
            } else {
                return array('1=2' => array());
            }
        }

        array_walk($ids, function(&$id)use($nameLength){
            $id = substr($id, $nameLength);
        });

        return parent::makeConditionsFromId($ids, $exlude);
    }

    public function getPrimaryIdExpression()
    {
        $parts = array_map(array($this, '__getPKeyExpression'), $this->pkeys);
        array_unshift($parts,"'" . $this->getName() . "'");

        return '(' . implode(" || '" . self::$pKeySeparator . "' || ", $parts) . ')';
    }


    protected function makeConditionsFromSearchField($fieldDescription)
    {
        $field = $this->_getSearchFields($fieldDescription['SSV_ALIAS']);

        if (empty($field)) {
            return array('', array());
        }

        $field['SEARCH_VALUE'] = $fieldDescription['SEARCH_VALUE'];

        return parent::makeConditionsFromSearchField($field);
    }


    public function getAllResultFields()
    {
        return array_merge($this->fields, $this->colorFields);
    }

    public function reorderResultFields($newOrder)
    {
        $toAddFields = $newOrder;

        foreach ($this->fields as $fieldDescription) {
            if (isset($newOrder[$fieldDescription['SSV_ALIAS']])) {
                $fieldDescription['SSV_SEQUENCE'] = $newOrder[$fieldDescription['SSV_ALIAS']];
            }

            if (isset($toAddFields[$fieldDescription['SSV_ALIAS']])) {
                unset($toAddFields[$fieldDescription['SSV_ALIAS']]);
            }
        }

        foreach ($toAddFields as $alias => $order) {
            $this->fields[] = array(
                'SSV_SEQUENCE' => $order,
                'SSV_ALIAS' => $alias,
                'SSV_EXPRESSION' => "''",
            );
        }
    }
}