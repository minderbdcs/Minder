<?php

class Minder_SysScreen_View_UnionSource implements Minder_SysScreen_View_SourceInterface, Minder_SysScreen_View_CompositeSourceInterface {


    /**
     * @var Minder_SysScreen_View_SourceInterface[]
     */
    protected $_subViews = array();
    protected $_screenName;
    protected $_searchFields;
    protected $_fields;
    protected $_fieldsOrder = array();
    protected $_customOrder = array();
    protected $_order = array();

    function __construct($screenName)
    {
        $this->_screenName = $screenName;
    }


    /**
     * @param $limit
     * @param bool $withOrder
     * @return Minder_SysScreen_View_QueryPart
     */
    public function getSelectQuery($limit, $withOrder = true)
    {
        $result = new Minder_SysScreen_View_QueryPart();
        $subQueries = array();

        foreach ($this->_getSubViews() as $subView) {
            $subQuery = $subView->getSelectQuery($limit, false);

            $subQueries[] = $subQuery->query;
            $result->args = array_merge($result->args, $subQuery->args);
        }

        $result->query = implode(PHP_EOL . "UNION ALL" . PHP_EOL, $subQueries);

        if ($withOrder) {
            $result->query .= ' ' . $this->_getOrderByExpression();
        }


        return $result;
    }

    /**
     * @param $limit
     * @return Minder_SysScreen_View_QueryPart
     */
    public function getKeysQuery($limit)
    {
        $result = new Minder_SysScreen_View_QueryPart();
        $subQueries = array();

        foreach ($this->_getSubViews() as $subView) {
            $subQuery = $subView->getKeysQuery($limit);

            $subQueries[] = $subQuery->query;
            $result->args = array_merge($result->args, $subQuery->args);
        }

        $result->query = implode(PHP_EOL . "UNION ALL" . PHP_EOL, $subQueries);

        return $result;
    }

    /**
     * @return Minder_SysScreen_View_QueryPart[]
     */
    public function getCountQueries()
    {
        return array_reduce(
            array_map(
                function(Minder_SysScreen_View_SourceInterface $view){
                    return $view->getCountQueries();
                },
                $this->_getSubViews()
            ),
            function(&$result, $subQueries) {
                return array_merge($result, $subQueries);
            },
            array()
        );
    }

    public function getPrimaryKeyAlias()
    {
        return 'PKEY_EXPR';
    }

    public function addSubView(Minder_SysScreen_View_SourceInterface $subView)
    {
        $this->_subViews[] = $subView;
    }

    protected function _getSubViews() {
        return $this->_subViews;
    }

    public function removeMasterSelectionConditions()
    {
        foreach($this->_getSubViews() as $subView) {
            $subView->removeMasterSelectionConditions();
        }
    }

    public function setEmptyMasterSelectionConditions()
    {
        foreach($this->_getSubViews() as $subView) {
            $subView->setEmptyMasterSelectionConditions();
        }
    }

    public function addMasterSelectionConditions($conditions)
    {
        foreach($this->_getSubViews() as $subView) {
            if (isset($conditions[$subView->getName()])) {
                $subView->addMasterSelectionConditions($conditions[$subView->getName()]);
            }
        }
    }

    public function createEmptyMasterSelectionConditions()
    {
        $result = array();

        foreach($this->_getSubViews() as $subView) {
            $result[$subView->getName()] = $subView->createEmptyMasterSelectionConditions();
        }

        return $result;
    }

    public function createMasterSelectionConditions($relation, $values)
    {
        $result = array();

        foreach($this->_getSubViews() as $subView) {
            $result[$subView->getName()] = $subView->createMasterSelectionConditions($relation, $values);
        }

        return $result;
    }

    public function makeConditionsFromSearch($searchFields = array())
    {
        $result = array();

        foreach($this->_getSubViews() as $subView) {
            $result[$subView->getName()] = $subView->makeConditionsFromSearch($searchFields);
        }

        return $result;
    }

    public function makeConditionsFromId($ids = '', $exlude = false)
    {
        $result = array();

        foreach($this->_getSubViews() as $subView) {
            $result[$subView->getName()] = $subView->makeConditionsFromId($ids, $exlude);
        }

        return $result;
    }

    public function setCustomOrderFields($sortFields = array())
    {
        $this->_customOrder = array();
        foreach ($sortFields as $sortField) {
            $fieldOrder = $this->_getFieldOrder($sortField['sortField']);

            if ($fieldOrder >= 0) {
                $this->_customOrder[] = $fieldOrder . ' ' . $sortField['sortOrder'];
            }
        }

        return $this;
    }

    public function setOrder(array $value = array())
    {
        // TODO: Implement setOrder() method.
    }

    protected function _getFieldOrder($sysScreenVar) {
        $alias = $sysScreenVar['SSV_ALIAS'];
        return isset($this->_fieldsOrder[$alias]) ? $this->_fieldsOrder[$alias] : -1;
    }

    public function getConditions()
    {
        $result = array();

        foreach($this->_getSubViews() as $subView) {
            $result[$subView->getName()] = $subView->getConditions();
        }

        return $result;
    }

    public function selectForeignKeyValues($relation, $offset, $limit)
    {
        // TODO: Implement selectForeignKeyValues() method.
    }

    /**
     * Set conditions for query. Replaces existent condotions.
     *
     * @param array $conditions - conditions array
     * @return Minder_SysScreen_Model_Interface
     */
    public function setConditions($conditions = array())
    {
        foreach($this->_getSubViews() as $subView) {
            if (isset($conditions[$subView->getName()])) {
                $subView->setConditions($conditions[$subView->getName()]);
            }
        }
    }

    /**
     * Add conditions for query.
     *
     * @param array $conditions - conditions array
     * @return Minder_SysScreen_Model_Interface
     */
    public function addConditions($conditions = array())
    {
        foreach($this->_getSubViews() as $subView) {
            if (isset($conditions[$subView->getName()])) {
                $subView->addConditions($conditions[$subView->getName()]);
            }
        }
    }

    /**
     * Remove conditions for query.
     *
     * @param array $conditions - conditions array. If empty remove all conditions.
     * @return Minder_SysScreen_Model_Interface
     */
    public function removeConditions($conditions = array())
    {
        foreach($this->_getSubViews() as $subView) {
            if (isset($conditions[$subView->getName()])) {
                $subView->removeConditions($conditions[$subView->getName()]);
            }
        }
    }

    public function getName()
    {
        return $this->_screenName;
    }

    public function setSearchFields($fields)
    {
        $this->_searchFields = $fields;
    }

    public function getAllResultFields()
    {
        $result = $this->_fields;

        foreach ($this->_getSubViews() as $subView) {
            $result = array_merge($result, $subView->getAllResultFields());
        }

        return $result;
    }

    public function setFields($fields)
    {
        $this->_fields = $fields;
    }

    protected function _mapFieldsOrder($fields) {
        return array_reduce(
            $fields,
            function(&$result, $field){
                $result[$field['SSV_ALIAS']] = (int)$result['SSV_SEQUENCE'];
            },
            array()
        );
    }

    protected function _reOrderFields($ownFieldsOrder, $allFieldsOrder) {
        $startOrderFrom = max($ownFieldsOrder) + 1;

        foreach ($allFieldsOrder as $fieldAlias => &$order) {
            $order = isset($ownFieldsOrder[$fieldAlias]) ? $ownFieldsOrder[$fieldAlias] : $startOrderFrom++;
        }

        return $allFieldsOrder;
    }

    public function init()
    {
        $ownFieldsOrder = $this->_mapFieldsOrder($this->_fields);
        $allFieldsOrder = $this->_mapFieldsOrder($this->getAllResultFields());
        $allFieldsOrder = $this->_reOrderFields($ownFieldsOrder, $allFieldsOrder);
        $this->_fieldsOrder = $allFieldsOrder;
        $this->reorderResultFields($allFieldsOrder);
    }

    public function reorderResultFields($newOrder)
    {
        foreach ($this->_getSubViews() as $subView) {
            $subView->reorderResultFields($newOrder);
        }
    }

    /**
     * @param string $mode
     * @return Minder_SysScreen_Model_MasterSlaveInterface
     */
    public function startMasterSelectionConditions($mode = Minder_SysScreen_ModelCondition::OPERATOR_OR)
    {
        foreach ($this->_getSubViews() as $subView) {
            $subView->startMasterSelectionConditions($mode);
        }
        return $this;
    }

    /**
     * @param $relation
     * @param $filterValues
     * @return Minder_SysScreen_Model_MasterSlaveInterface
     */
    public function createAndAddMasterSelectionConditions($relation, $filterValues)
    {
        foreach ($this->_getSubViews() as $subView) {
            $subView->createAndAddMasterSelectionConditions($relation, $filterValues);
        }
        return $this;
    }

    /**
     * @param $relation
     * @return Minder_SysScreen_Model_MasterSlaveInterface
     */
    public function addEmptyMasterSelectionConditions($relation)
    {
        foreach ($this->_getSubViews() as $subView) {
            $subView->addEmptyMasterSelectionConditions($relation);
        }
        return $this;
    }

    /**
     * @return Minder_SysScreen_Model_MasterSlaveInterface
     */
    public function applyMasterSelectionConditions()
    {
        foreach ($this->_getSubViews() as $subView) {
            $subView->applyMasterSelectionConditions();
        }
        return $this;
    }

    protected function _orderSortHelper($a, $b) {
        return $a["SSO_SEQUENCE"] - $b["SSO_SEQUENCE"];
    }

    protected function _orderByMapper($order) {
        return ltrim(str_ireplace('order by ', '', $order['SSO_ORDER']), ', ');
    }

    protected function _getOrderByExpression() {
        usort($this->_order, array($this, '_orderSortHelper'));
        $result = implode(', ', array_merge($this->_customOrder, array_map(array($this, '_orderByMapper'), $this->_order)));
        return empty($result) ? '' : 'ORDER BY ' . $result;
    }

    public function setStaticConditions($conditions = array())
    {
        // TODO: Implement setStaticConditions() method.
    }

    public function setPrimaryKeys($keys)
    {
        // TODO: Implement setPrimaryKeys() method.
    }
}