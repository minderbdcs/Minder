<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\Collection;
use MinderNG\PageMicrocode\Component\Exception\DataSetRowNotFound;

/**
 * Class DataSetRowCollection
 * @package MinderNG\PageMicrocode\Component
 *
 * @method DataSetRow get($idOrAggregate)
 * @method null|DataSetRow first()
 */
class DataSetRowCollection extends Collection {
    protected function _getDefaultModelClassName()
    {
        return __NAMESPACE__ . '\\DataSetRow';
    }

    /**
     * @param $idOrAggregate
     * @return DataSetRow
     * @throws DataSetRowNotFound
     */
    public function findDataSetRow($idOrAggregate) {
        $dataSetRow = ($idOrAggregate instanceof DataSetRow) ? $idOrAggregate : $this->newDataSetRow($idOrAggregate);

        $foundDataSetRow = $this->get($dataSetRow);

        if (empty($foundDataSetRow)) {
            throw new DataSetRowNotFound($dataSetRow);
        }

        return $foundDataSetRow;
    }

    /**
     * @param string $cid
     * @return DataSetRow|null
     */
    public function getDataSetRowByCID($cid) {
        return $this->findWhere(array(
            DataSetRow::FIELD_CID => $cid,
        ));
    }

    /**
     * @param $idOrAggregate
     * @return DataSetRow|null
     */
    public function getDataSetRow($idOrAggregate) {
        return $this->get($idOrAggregate);
    }

    /**
     * @return \Iterator|DataSetRow[]
     */
    public function getDataSetRows() {
        return $this;
    }

    public function updateRowIndexes() {
        foreach($this->getDataSetRows() as $index => $dataSetRow) {
            $dataSetRow->setIndex($index);
        }
    }

    /**
     * @param $idOrAggregate
     * @return DataSetRow
     */
    public function newDataSetRow($idOrAggregate) {
        $idOrAggregate = (is_array($idOrAggregate)) ? $idOrAggregate : array('PRIMARY_ID' => $idOrAggregate);
        return $this->newModelInstance($idOrAggregate, null, true);
    }
}