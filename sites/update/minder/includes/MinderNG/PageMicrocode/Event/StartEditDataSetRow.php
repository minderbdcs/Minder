<?php

namespace MinderNG\PageMicrocode\Event;

use MinderNG\PageMicrocode\Component;

class StartEditDataSetRow extends AbstractEvent {
    const EVENT_NAME = 'StartEditDataSetRow';

    function __construct(Component\DataSet $dataSet, Component\DataSetRow $dataSetRow, Component\Form $editForm)
    {
        $this->_name = static::EVENT_NAME;
        $this->_args = func_get_args();
    }

}