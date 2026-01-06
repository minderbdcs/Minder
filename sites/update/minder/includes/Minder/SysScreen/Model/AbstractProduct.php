<?php

abstract class Minder_SysScreen_Model_AbstractProduct extends Minder_SysScreen_Model
{
    protected $prodIdFilter = null;

    protected function _getProdIdFilterString() {
        return is_null($this->prodIdFilter) ? '%' : $this->prodIdFilter;
    }

    protected function _getExpressionParameterValue($paramName)
    {
        switch (strtoupper($paramName)) {
            case 'PROD_FILTER':
                return $this->_getProdIdFilterString();
            default:
                return parent::_getExpressionParameterValue($paramName);
        }
    }

    public function makeConditionsFromSearchField($fieldDescription) {
        $conditionString = '';
        $conditionArgs   = array();
        switch (strtoupper($fieldDescription['SSV_NAME'])) {
            case 'SHORT_DESC':
                if (!empty($fieldDescription['SEARCH_VALUE'])) {
                    $parserObj       = new Parser($fieldDescription['SEARCH_VALUE'], 'SHORT_DESC', $fieldDescription['SSV_TABLE'], '');
                    $conditionString = '(' . $parserObj->parse() . ')';
                }
                return array($conditionString, $conditionArgs, 'CONDITION_STRING' => $conditionString, 'CONDITION_ARGS' => $conditionArgs);

            case 'LONG_DESC':
                if (!empty($fieldDescription['SEARCH_VALUE'])) {
                    $parserObj       = new Parser($fieldDescription['SEARCH_VALUE'], 'LONG_DESC', 'PROD_PROFILE', '');
                    $conditionString = '(' . $parserObj->parse() . ')';
                }
                return array($conditionString, $conditionArgs, 'CONDITION_STRING' => $conditionString, 'CONDITION_ARGS' => $conditionArgs);

            case 'PROD_ID':
                $this->prodIdFilter = isset($fieldDescription['SEARCH_VALUE']) ? $this->addWildcardsToLikeSearchParams($fieldDescription['SEARCH_VALUE']) : null;
                return parent::makeConditionsFromSearchField($fieldDescription);

            default:
                return parent::makeConditionsFromSearchField($fieldDescription);
        }
    }

    protected function _getTotalAndOnHandQtySimple() {
        $result = new stdClass();
        $result->totalQty = 0;
        $result->onHandQty = 0;

        $tableName = null;

        if ($this->_tableAliasExists('PROD_PROFILE'))
            $tableName = 'PROD_PROFILE';
        else if ($this->_tableAliasExists('ISSN'))
            $tableName = 'ISSN';

        $prodIdExpression = (is_null($tableName)) ? 'PROD_ID' : $tableName . '.PROD_ID';

        list($where, $args) = $this->__getWhereAndArgs();
        $minder = Minder::getInstance();
        $stockStatusExpression = "
            PRODUCT_CMP_WH_STOCK_STATUS_V(
                '" . $this->_getProdIdFilterString() . "',
                '" . $minder->getCompanyFilterString() . "',
                '" . $minder->getWarehouseFilterString() . "',
                '" . $minder->userId . "',
                'ONSITE_QTY|UNPICKED_ORDER_QTY|AVAILABLE_QTY'
            ) AS STOCK_STATUS";

        $sql = "
            SELECT
                SUM(ONSITE_QTY) AS ONSITE_QTY,
                SUM(UNPICKED_ORDER_QTY) AS UNPICKED_ORDER_QTY,
                SUM(AVAILABLE_QTY) AS AVAILABLE_QTY
            FROM
                " . $stockStatusExpression . "
            WHERE
                PROD_ID IN (
                    SELECT
                        " . $prodIdExpression . "
                    FROM
                        " . $this->getFromExpression() . $where . ' ' . $this->_getGroupByExpression() . ' ' . $this->_getOrderByExpression() . "
                )
        ";

        array_unshift($args, $sql);

        if (false !== ($response = call_user_func_array(array($minder, 'fetchAllAssoc'), $args))) {
            $response = current($response);
            $result->totalQty = $response['AVAILABLE_QTY'] - $response['UNPICKED_ORDER_QTY'];
            $result->onHandQty = $response['ONSITE_QTY'];
        }

        return $result;
    }

    protected function _getTotalAndOnHandQtyFast($stockStatusTableDesc) {
        $result = new stdClass();
        $result->totalQty = 0;
        $result->onHandQty = 0;

        list($where, $args) = $this->__getWhereAndArgs();

        $sql = "
            SELECT
                SUM(ONSITE_QTY) AS ONSITE_QTY,
                SUM(UNPICKED_ORDER_QTY) AS UNPICKED_ORDER_QTY,
                SUM(AVAILABLE_QTY) AS AVAILABLE_QTY
            FROM
                (
                    SELECT DISTINCT
                        " . $stockStatusTableDesc['SST_ALIAS'] . ".PROD_ID,
                        " . $stockStatusTableDesc['SST_ALIAS'] . ".ONSITE_QTY,
                        " . $stockStatusTableDesc['SST_ALIAS'] . ".UNPICKED_ORDER_QTY,
                        " . $stockStatusTableDesc['SST_ALIAS'] . ".AVAILABLE_QTY
                    FROM
                        " . $this->getFromExpression() . $where . ' ' . $this->_getGroupByExpression() . ' ' . $this->_getOrderByExpression() . "
                )

        ";
        array_unshift($args, $sql);

        $minder = Minder::getInstance();
        if (false !== ($response = call_user_func_array(array($minder, 'fetchAllAssoc'), $args))) {
            $response = current($response);
            $result->totalQty = $response['AVAILABLE_QTY'] - $response['UNPICKED_ORDER_QTY'];
            $result->onHandQty = $response['ONSITE_QTY'];
        }

        return $result;
    }

    protected function _updateProcedureArgument($procName, $index, $value) {
        foreach ($this->tables as &$tableDescription) {
            if ($tableDescription['SST_TABLE'] == $procName) {
                $tableDescription['TABLE_PARAMS'][$index] = $value;
            }
        }
    }

    public function getTotalAndOnHandQty() {
        if ($tableDesc = $this->_tableExists('PRODUCT_COMPANY_WH_STOCK_STATUS'))
            return $this->_getTotalAndOnHandQtyFast($tableDesc);

        if ($tableDesc = $this->_tableExists('PRODUCT_CMP_WH_STOCK_STATUS_V')) {
            $this->_updateProcedureArgument('PRODUCT_CMP_WH_STOCK_STATUS_V', 4, 'ONSITE_QTY|UNPICKED_ORDER_QTY|AVAILABLE_QTY');
            $result = $this->_getTotalAndOnHandQtyFast($tableDesc);
            $this->_updateProcedureArgument('PRODUCT_CMP_WH_STOCK_STATUS_V', 4, $tableDesc['TABLE_PARAMS'][4]);
            return $result;
        }

        if ($this->_aliasExists('AVAILABLE_QTY') && $unpickedField = $this->_aliasExists('UNPICKED_QTY')) {
            $result = new stdClass();
            $result->onHandQty = $this->getTotalAvailableQtyTifs();
            $result->totalQty = $result->onHandQty - $this->getTotalUnpickedQtyTifs();

            return $result;
        }

        return $this->_getTotalAndOnHandQtySimple();
    }

    public function getTotalAvailableQtyTifs() {
        $field = $this->_aliasExists('AVAILABLE_QTY');

        if (false === $field) {
            return 0;
        }

        $orderBy = $this->order;
        $customOrderBy = $this->_customOrder;
        $this->_customOrder = array();
        $this->order = array();
        $result = $this->selectArbitraryExpression(0, 1, 'coalesce(SUM(' . $field['SSV_EXPRESSION'] .  '), 0) AS AVAILABLE_QTY');
        $this->order = $orderBy;
        $this->_customOrder = $customOrderBy;

        if (count($result) > 0) {
            $result = current($result);
            return $result['AVAILABLE_QTY'];
        }

        return 0;
    }

    public function getTotalUnpickedQtyTifs() {
        $unpickedField = $this->_aliasExists('UNPICKED_QTY');
        $unpickedAlias = $unpickedField['SSV_ALIAS'];

        if (false === $unpickedField) {
            return 0;
        }

        $prodIdField = $this->_fieldExists('PROD_ID');

        if (false === $prodIdField) {
            return 0;
        }

        $unpickedExpression = $this->__getFieldExpressionWithAlias($unpickedField);
        $prodExpression = $this->__getFieldExpressionWithAlias($prodIdField);

        $orderBy = $this->order;
        $customOrderBy = $this->_customOrder;
        $this->_customOrder = array();
        $this->order = array();
        $result = $this->selectArbitraryExpression(0, count($this), 'distinct ' . $prodExpression . ', ' . $unpickedExpression);
        $this->order = $orderBy;
        $this->_customOrder = $customOrderBy;

        $sum = 0;
        if (count($result) > 0) {
            foreach ($result as $resultRow) {
                $sum += $resultRow[$unpickedAlias];
            }

            return $sum;
        }

        return 0;
    }

}
