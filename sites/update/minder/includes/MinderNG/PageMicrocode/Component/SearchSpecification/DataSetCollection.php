<?php

namespace MinderNG\PageMicrocode\Component\SearchSpecification;

use MinderNG\Collection\Collection;
use MinderNG\PageMicrocode\Component;

class DataSetCollection extends Collection {
    protected function _getDefaultModelClassName()
    {
        return __NAMESPACE__ . '\\DataSet';
    }

    /**
     * @param Component\DataSet $dataSet
     * @return DataSet|null
     */
    public function getDataSetSearchSpecification(Component\DataSet $dataSet) {
        return $this->findWhere(array(
            DataSet::getIdAttribute() => $dataSet->getId(),
        ));
    }

    /**
     * @param Component\DataSet $dataSet
     * @return DataSet
     */
    public function newDataSetSearchSpecification(Component\DataSet $dataSet) {
        return $this->_newDataSet($dataSet->getId());
    }

    /**
     * @param $idOrAggregate
     * @return DataSet
     */
    private function _newDataSet($idOrAggregate) {
        $idOrAggregate = is_string($idOrAggregate) ? array(DataSet::getIdAttribute() => $idOrAggregate) : $idOrAggregate;

        return $this->newModelInstance($idOrAggregate);
    }
}