<?php

namespace MinderNG\PageMicrocode\Command;

use MinderNG\PageMicrocode\Component\DataSet;
use MinderNG\PageMicrocode\Component\DataSetRow;

class RunDataSetTransaction extends Command {
    const COMMAND_NAME = 'RunDataSetTransaction';

    const MODE_ALL = 'ALL';
    const MODE_CHANGED = 'CHANGED';

    function __construct(DataSet $dataSet, DataSetRow $dataSetRow, $action, $mode)
    {
        $this->_name = static::COMMAND_NAME;

        $mode = strtoupper($mode);
        $mode = in_array($mode, array(static::MODE_ALL, static::MODE_CHANGED)) ? $mode : static::MODE_ALL;

        $this->_args = array(
            $dataSet,
            $dataSetRow,
            $action,
            $mode,
        );
    }

    /**
     * @return DataSetRow
     */
    public function getResultDataSetRow() {
        return $this->getResponse();
    }
}