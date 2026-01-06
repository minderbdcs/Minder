<?php

namespace MinderNG\PageMicrocode\Event;

use MinderNG\PageMicrocode\Component;

class EditDataSetRowRequest extends AbstractEvent {
    const EVENT_NAME = 'EditDataSetRowRequest';

    function __construct(Component\DataSet $dataSet, Component\DataSetRow $dataRow, Component\Form $form)
    {
        $this->_name = static::EVENT_NAME;
        $this->_args = func_get_args();
    }

}