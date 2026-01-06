<?php

namespace MinderNG\PageMicrocode\QueryBuilder;

use MinderNG\Collection\Helper\Helper;
use MinderNG\PageMicrocode\Component\Components;

class ParameterFormatter {
    /**
     * @var Helper
     */
    private $_collectionHelper;

    function __construct(Helper $collectionHelper)
    {
        $this->_collectionHelper = $collectionHelper;
    }

    public function fillExpressionParameters($expression, Components $pageComponents) {
        $parameterValues = $this->_fillExpressionParameterValues($this->_prepareExpressionParameterList($expression), $pageComponents);

        return str_replace(array_keys($parameterValues), array_values($parameterValues), $expression);
    }

    private function _prepareExpressionParameterList($expression) {
        $result = array();
        $foundMatches = array();
        if (preg_match_all('/(%\w+%)/', $expression, $foundMatches)) {
            $result = $foundMatches[1];
        }

        return $result;
    }

    private function _fillExpressionParameterValues($parameterList, Components $pageComponents) {

        $result = array();

        foreach ($parameterList as $parameterName) {
            $result[$parameterName] = $this->_getParameterValue($parameterName, $pageComponents);
        }

        return $result;
    }

    private function _getParameterValue($parameterName, Components $pageComponents) {
        switch ($parameterName) {
            case '%COMPANY_FILTER_STRING%':
                return implode('|', iterator_to_array($pageComponents->companyLimitList->pluck('COMPANY_ID')));
            case '%WH_FILTER_STRING%':
                return implode('|', iterator_to_array($pageComponents->warehouseLimitList->pluck('WH_ID')));
            case '%CURRENT_USER_ID%':
                return $pageComponents->user->USER_ID;
            case '%WAREHOUSE_LIMIT%':
                return $pageComponents->warehouseLimit->WH_ID;
            case '%COMPANY_LIMIT%':
                return $pageComponents->companyLimit->COMPANY_ID;
            case '%CURRENT_WAREHOUSE%':
                return $pageComponents->warehouse->WH_ID;
            case '%CURRENT_COMPANY%':
                return $pageComponents->company->COMPANY_ID;
            default:
                return $parameterName;
        }
    }

    private function _quote($value) {
        return $this->_getDbAdapter()->quote($value);
    }

    /**
     * @return \Zend_Db_Adapter_Abstract
     */
    protected function _getDbAdapter()
    {
        return \Zend_Db_Table::getDefaultAdapter();
    }
}