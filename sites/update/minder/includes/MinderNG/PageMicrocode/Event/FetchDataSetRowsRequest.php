<?php

namespace MinderNG\PageMicrocode\Event;

use MinderNG\PageMicrocode\Component\DataSet;

class FetchDataSetRowsRequest extends AbstractEvent {
    const EVENT_NAME = 'FetchDataSetRowsRequest';

    function __construct(DataSet $dataSet)
    {
        $this->_name = static::EVENT_NAME;
        $this->_args = func_get_args();
    }

}