<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\Collection;

/**
 * Class DataSetCollection
 * @package MinderNG\PageMicrocode\Component
 *
 * @method DataSet get($idOrAggregate)
 */
class DataSetCollection extends Collection {

    /**
     * @param string|array|DataSet $idOrAggregate
     * @return DataSet
     * @throws Exception\DataSetNotFound
     */
    public function findDataSet($idOrAggregate) {
        $dataSet = ($idOrAggregate instanceof DataSet) ? $idOrAggregate : $this->newDataSet($idOrAggregate);

        $foundDataSet = $this->get($dataSet);

        if (empty($foundDataSet)) {
            throw new Exception\DataSetNotFound($dataSet);
        }

        return $foundDataSet;
    }

    /**
     * @param $screen
     * @return \Iterator|DataSet[]
     */
    public function getAllScreenDataSets(Screen $screen) {
        return $this->where(array(
            'SS_NAME' => $screen->SS_NAME
        ));
    }

    protected function _getDefaultModelClassName()
    {
        return __NAMESPACE__ . '\\DataSet';
    }

    /**
     * @param $idOrAggregate
     * @return DataSet|null
     */
    public function getDataSet($idOrAggregate) {
        return $this->get($idOrAggregate);
    }

    /**
     * @return DataSet[]
     */
    public function filterMainDataSets() {
        return array_reduce($this->_aggregates, function($previous, DataSet $current) {
            if ($current->isMain()) {
                $previous[] = $current;
            }
            return $previous;
        }, array());
    }

    /**
     * @return \Iterator|DataSet[]
     */
    public function filterFieldDataSets() {
        return $this->filter(function(DataSet $dataSet){
            return $dataSet->fieldDataSet() && !$dataSet->dependsOnRowData();
        });
    }

    /**
     * @param string|array $dataSetData
     * @return \MinderNG\Collection\ModelAggregateInterface
     */
    public function newDataSet($dataSetData) {
        //todo: split Id to dataSetData array
        return $this->newModelInstance($dataSetData);
    }
}