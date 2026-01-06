<?php

namespace MinderNG\PageMicrocode\Event;

use MinderNG\PageMicrocode\Component\DataSet;
use MinderNG\PageMicrocode\Component\DataSetRow;

class DataSetRowChanged extends AbstractEvent {
    const EVENT_NAME = 'DataSetRowChanged';

    function __construct(DataSet $dataSet, DataSetRow $dataSetRow)
    {
        $this->_name = static::EVENT_NAME;
        $this->_args = func_get_args();
    }

}