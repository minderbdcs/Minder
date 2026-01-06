<?php

namespace MinderNG\PageMicrocode\Event;

use MinderNG\PageMicrocode\Component\DataSet;
use MinderNG\PageMicrocode\Component\DataSetRow;

class ReloadRowError extends AbstractEvent
{
    const EVENT_NAME = 'ReloadRowError';
    /**
     * @var
     */
    private $message;

    /**
     * ReloadRowError constructor.
     * @param DataSet $dataSet
     * @param DataSetRow $dataSetRow
     * @param $message
     */
    public function __construct(DataSet $dataSet, DataSetRow $dataSetRow, $message)
    {
        $this->_name = static::EVENT_NAME;
        $this->_args = func_get_args();
        $this->message = $message;
    }
}