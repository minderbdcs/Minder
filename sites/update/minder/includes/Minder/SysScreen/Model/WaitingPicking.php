<?php
class Minder_SysScreen_Model_WaitingPicking extends Minder_SysScreen_Model implements Minder_OrderAllocator_ItemProvider_Interface
{
    const OVER_LIMIT = 'OVERLIMIT';
    const UNDER_LIMIT = 'UNDERLIMIT';

    protected $_prodAllocateLimit = null;

    public function init()
    {
        parent::init();

        if (!is_array($this->staticConditions))
            $this->staticConditions = array();

        if (empty($this->staticConditions)) {
            //for backward compatibility
            $this->staticConditions = array_merge(
                $this->staticConditions,
                array(
                    '(PICK_ORDER.PICK_STATUS IN (?, ?))' => array('DA', 'OP'),
                    '(PICK_ITEM.PICK_LINE_STATUS IN (?))' => array('OP')
                )
            );
        }
    }

    public function selectPickOrder($rowOffset, $itemCountPerPage) {
        $pickOrders = array();
        
        $result = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT PICK_ORDER.PICK_ORDER');
        if (is_array($result) && count($result) > 0)
            $pickOrders = array_map(create_function('$item', 'return $item["PICK_ORDER"];'), $result);
        
        return $pickOrders;
    }

    public function fetchPickLabelNoForPickLabels($rowOffset, $itemCountPerPage) {
        $pickOrders = $this->selectPickOrder($rowOffset, $itemCountPerPage);

        if (count($pickOrders) < 1) return array();

        $sql = "SELECT PICK_LABEL_NO FROM PICK_ITEM WHERE PICK_ORDER IN (" . substr(str_repeat('?, ', count($pickOrders)), 0, -2) . ") AND PICK_LINE_STATUS IN (?, ?, ?, ?, ?, ?)";
        $args = array_merge(array($sql), $pickOrders, array('CF', 'AL', 'AS', 'PG', 'PL', 'OP'));

        if (false === ($queryResult = call_user_func_array(array(Minder::getInstance(), 'fetchAllAssoc'), $args)))
            throw new Minder_SysScreen_Model_WaitingPicking_Exception('Error fetching PICK_LABEL_NO: ' . Minder::getInstance()->lastError);

        return array_map(create_function('$item', 'return $item["PICK_LABEL_NO"];'), $queryResult);
    }

    public function fetchPickOrderColumn($items) {
        $pickOrderField = $this->_fieldExists('PICK_ORDER');
        if (!$pickOrderField) {
            return array();
        }

        $pKey = $this->getPKeyAlias();
        $pickOrderAlias = $this->__getFieldAlias($pickOrderField);

        $result = array();
        foreach ($items as $item) {
            $result[] = array(
                'ROW_ID'        => $item[$pKey],
                'PICK_ORDER'    => $item[$pickOrderAlias],
            );
        }

        return $result;
    }

    function selectPickOrderToAllocate($orderLimit)
    {
        $originalOrderBy = $this->order;

        $this->order = array(
            array('SSO_SEQUENCE' => 0, 'SSO_ORDER' => 'PICK_DUE_DATE ASC'),
            array('SSO_SEQUENCE' => 1, 'SSO_ORDER' => 'ORDER BY PICK_ORDER.PICK_PRIORITY ASC'),
        );

        try {
            $queryResult = $this->selectArbitraryExpression(
                0,
                count($this),
                "
                    DISTINCT
                        PICK_ORDER.PICK_ORDER,
                        COALESCE(PICK_ORDER.PICK_DUE_DATE, CURRENT_TIMESTAMP) AS PICK_DUE_DATE
                "
            );
        } catch (Exception $e) {
            $this->order = $originalOrderBy;
            throw $e;
        }

        $this->order = $originalOrderBy;

        $result = array();
        foreach ($queryResult as $resultRow)
            $result[$resultRow['PICK_ORDER']] = $resultRow['PICK_ORDER'];

        return $result;
    }

    function selectProdIdAndPickLabelNoToAllocate()
    {
        $totalRows = count($this);

        if ($totalRows < 1) {
            return array();
        }

        $pickOrders = $this->selectPickOrder(0, $totalRows);

        if (empty($pickOrders)) {
            return array();
        }

        $sql = "
            SELECT DISTINCT
                PICK_ORDER.PICK_ORDER,
                PICK_ITEM.PICK_LABEL_NO,
                CASE
                    WHEN PICK_ITEM.PROD_ID IS NULL THEN ISSN.PROD_ID
                    ELSE PICK_ITEM.PROD_ID
                END AS PROD_ID
            FROM
                PICK_ORDER
                LEFT JOIN PICK_ITEM ON PICK_ORDER.PICK_ORDER = PICK_ITEM.PICK_ORDER
                LEFT OUTER JOIN ISSN ON PICK_ITEM.SSN_ID = ISSN.SSN_ID
            WHERE
                PICK_ORDER.PICK_ORDER IN (" . implode(', ', array_fill(0, count($pickOrders), '?')) . ")
                AND PICK_ORDER.PICK_STATUS IN ('DA', 'OP')
                AND PICK_ITEM.PICK_LINE_STATUS IN ('OP')
        " . $this->_getMinder()->getWarehouseAndCompanyLimit(0, false, 'PICK_ORDER.', 'PICK_ORDER.');

        $sql = substr($sql, 0, -5);

        $sql .= "
            ORDER BY
                PICK_DUE_DATE ASC,
                PICK_ORDER.PICK_PRIORITY ASC
        ";

        array_unshift($pickOrders, $sql);

        if (false !== ($queryResult = call_user_func_array(array($this->_getMinder(), 'fetchAllAssoc'), $pickOrders))) {
            return $queryResult;
        }

        return array();
    }

    function selectProdIdToAllocate($productLimit)
    {
        return array(); //not used now
    }

    protected function _getFullPickOrderFieldExpression() {
        $table = $this->_tableExists(array('PICK_ORDER', 'PICK_ITEM'));

        if ($table !== false) {
            $tableAlias = empty($table['SST_ALIAS']) ? $table['SST_TABLE'] : $table['SST_ALIAS'];

            return empty($tableAlias) ? 'PICK_ORDER' : ($tableAlias . '.PICK_ORDER');
        }

        return null;
    }

    protected function _getProdCountSql() {
        $fieldExpression = $this->_getFullPickOrderFieldExpression();

        return empty($fieldExpression) ? null : "
            (SELECT
                COUNT(DISTINCT COALESCE(PICK_ITEM.PROD_ID, ISSN.PROD_ID))
            FROM
                PICK_ITEM
                LEFT JOIN ISSN ON PICK_ITEM.SSN_ID = ISSN.SSN_ID
            WHERE
                PICK_ITEM.PICK_ORDER = " . $fieldExpression . "
                AND PICK_ITEM.PICK_LINE_STATUS IN ('OP', 'AL', 'PG')
            )
        ";
    }

    protected function _makeProdAmountCondition($fieldDescription) {
        if (is_null($this->_getProdAllocateLimit())) {
            return null;
        }

        $countSql = $this->_getProdCountSql();

        if (empty($countSql)) {
            return null;
        }

        $conditionArgs = array($this->_getProdAllocateLimit());

        switch (strtoupper($fieldDescription['SEARCH_VALUE'])) {
            case static::OVER_LIMIT:
                $conditionString = $countSql . ' > ?';
                break;
            case static::UNDER_LIMIT:
                $conditionString = $countSql . ' <= ?';
                break;
            default:
                return null;
        }

        return array($conditionString, $conditionArgs, 'CONDITION_STRING' => $conditionString, 'CONDITION_ARGS' => $conditionArgs);
    }

    protected function makeConditionsFromSearchField($fieldDescription)
    {
        switch (strtoupper($fieldDescription['SSV_ALIAS'])) {
            case 'PROD_AMOUNT':
                if (empty($fieldDescription['SEARCH_VALUE'])) {
                    return null;
                } else {
                    return $this->_makeProdAmountCondition($fieldDescription);
                }
            default:
                return parent::makeConditionsFromSearchField($fieldDescription);
        }
    }

    /**
     * @return null|int
     */
    protected function _getProdAllocateLimit()
    {
        return $this->_prodAllocateLimit;
    }

    /**
     * @param int $prodAllocateLimit
     * @return $this
     */
    public function setProdAllocateLimit($prodAllocateLimit)
    {
        $this->_prodAllocateLimit = $prodAllocateLimit;
        return $this;
    }


}

class Minder_SysScreen_Model_WaitingPicking_Exception extends Minder_SysScreen_Model_Exception {}