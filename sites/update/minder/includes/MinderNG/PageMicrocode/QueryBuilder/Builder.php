<?php

namespace MinderNG\PageMicrocode\QueryBuilder;

use MinderNG\Collection;
use MinderNG\PageMicrocode\Component;
use MinderNG\PageMicrocode\Controller\Exception;

class Builder {
    /**
     * @var Collection\Helper\Helper
     */
    private $_collectionHelper;

    /**
     * @var ParameterFormatter
     */
    private $_formatter;

    function __construct(Collection\Helper\Helper $collectionHelper, ParameterFormatter $formatter)
    {
        $this->_collectionHelper = $collectionHelper;
        $this->_formatter = $formatter;
    }

    /**
     * @param Component\Components $pageComponents
     * @param Component\DataSet $dataSet
     * @param Component\SearchSpecification\DataSet $searchSpecification
     * @return string
     * @throws Exception\DataSetHasNoPrimaryKEy
     */
    public function buildQuery(Component\Components $pageComponents, Component\DataSet $dataSet, Component\SearchSpecification\DataSet $searchSpecification = null)
    {
        return $this->_doBuild($pageComponents, $dataSet, ($dataSet->LIMIT > 0) ? 'FIRST ' . $dataSet->LIMIT : '', $searchSpecification);
    }

    public function buildReloadQuery(Component\Components $pageComponents, Component\DataSet $dataSet, Component\SearchSpecification\DataSet $searchSpecification) {
        return $this->_doBuild($pageComponents, $dataSet, 1, $searchSpecification);
    }

    /**
     * @param Component\Components $pageComponents
     * @param Component\DataSet $dataSet
     * @return string
     */
    protected function _from(Component\Components $pageComponents, Component\DataSet $dataSet)
    {
        return implode("\n", array_map(function (Component\Table $table) {
            return $table->SST_JOIN . ' ' . $table->SST_TABLE . ' AS ' . $table->SST_ALIAS . ' ' . $table->SST_VIA;
        }, iterator_to_array($dataSet->filterDataSetTables($pageComponents->tables))));
    }

    protected function _fields(Component\Components $pageComponents, Component\DataSet $dataSet) {
        $dataSourceFields = iterator_to_array($dataSet->filterDataSetFields($pageComponents->dataSourceFieldCollection));
        $dataSourcePrimaryIdList = iterator_to_array(
            $this->_getCollectionHelper()->pluck($dataSet->filterPrimaryKeys($pageComponents->dataSourceFieldCollection), 'SSV_EXPRESSION')
        );

        if (empty($dataSourcePrimaryIdList)) {
            throw new Exception\DataSetHasNoPrimaryKEy($dataSet);
        }

        $fields = array_map(function (Component\DataSourceField $field) {
            return $field->SSV_EXPRESSION . ' AS ' . $field->SSV_ALIAS;
        }, $dataSourceFields);

        $fields[] = implode(" || '{-}' || ", $dataSourcePrimaryIdList) . ' AS PRIMARY_ID';

        return implode(",\n", $fields);
    }

    private function _userConditions(Component\SearchSpecification\DataSet $searchSpecification) {
        $rowConditions = array();

        foreach($searchSpecification->getRows() as $specificationRow) {
            $rowCondition = $this->_rowConditions($specificationRow);

            if (!empty($rowCondition)) {
                $rowConditions[] = $rowCondition;
            }
        }

        return implode(' AND ', $rowConditions);
    }

    /**
     * @param Component\SearchSpecification\EqualTo[] $rowSpecification
     * @return string
     */
    private function _rowConditions($rowSpecification) {
        $parts = array();

        foreach($rowSpecification as $fieldSpecification) {
            $parts[] = $fieldSpecification->getExpression() . ' = ' . $this->_getDbAdapter()->quote($fieldSpecification->getValue());
        }

        return implode(' AND ', $parts);
    }

    private function _staticConditions(Component\Components $components, Component\DataSet $dataSet) {
        $parts = array();

        foreach ($components->fields->filterDataSetStaticConditionFields($dataSet) as $conditionField) {
            if (!empty($conditionField->SSV_EXPRESSION)) {
                $parts[] = '(' . $this->_getFormatter()->fillExpressionParameters($conditionField->SSV_EXPRESSION, $components) . ')';
            }
        }

        return implode(' AND ', $parts);
    }

    /**
     * @return \Zend_Db_Adapter_Abstract
     */
    protected function _getDbAdapter()
    {
        return \Zend_Db_Table::getDefaultAdapter();
    }

    /**
     * @return Collection\Helper\Helper
     */
    private function _getCollectionHelper()
    {
        return $this->_collectionHelper;
    }

    /**
     * @return ParameterFormatter
     */
    private function _getFormatter()
    {
        return $this->_formatter;
    }

    /**
     * @param Component\Components $pageComponents
     * @param Component\DataSet $dataSet
     * @param $limit
     * @param Component\SearchSpecification\DataSet $searchSpecification
     * @return string
     * @throws Exception\DataSetHasNoPrimaryKEy
     */
    private function _doBuild(Component\Components $pageComponents, Component\DataSet $dataSet, $limit, Component\SearchSpecification\DataSet $searchSpecification = null)
    {
        $from = $this->_from($pageComponents, $dataSet);
        $fields = $this->_fields($pageComponents, $dataSet);

        $where = array();

        if (!is_null($searchSpecification)) {
            $userConditions = $this->_userConditions($searchSpecification);

            if (!empty($userConditions)) {
                $where[] = $userConditions;
            }
        }

        $staticConditions = $this->_staticConditions($pageComponents, $dataSet);
        if (!empty($staticConditions)) {
            $where[] = $staticConditions;
        }

        $query = "SELECT " . $limit . ' ' . $fields . ' FROM ' . $from;

        if (count($where) > 0) {
            $query .= ' WHERE ' . implode(' AND ', $where);
            return $query;
        }
        return $query;
    }

}