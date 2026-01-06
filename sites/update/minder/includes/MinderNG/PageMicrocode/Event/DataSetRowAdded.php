<?php

namespace MinderNG\PageMicrocode\Event;

use MinderNG\PageMicrocode\Component\DataSet;
use MinderNG\PageMicrocode\Component\DataSetRow;

class DataSetRowAdded extends AbstractEvent {
    const EVENT_NAME = 'DataSetRowAdded';

    /**
     * DataSetRowAdded constructor.
     * @param DataSet $dataSet
     * @param DataSetRow $dataSetRow
     */
    public function __construct(DataSet $dataSet, DataSetRow $dataSetRow) {
        $this->_name = static::EVENT_NAME;
        $this->_args = func_get_args();
    }


}