<?php

namespace MinderNG\PageMicrocode\Command;

use MinderNG\PageMicrocode\Component;

class SearchDataSet extends Command {
    const COMMAND_NAME = 'SearchDataSet';

    function __construct(Component\DataSet $dataSet, Component\SearchSpecification\DataSet $searchSpecification)
    {
        $this->_name = static::COMMAND_NAME;
        $this->_args = func_get_args();
    }


}