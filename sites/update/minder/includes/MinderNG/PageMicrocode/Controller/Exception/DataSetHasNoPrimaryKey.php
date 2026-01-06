<?php

namespace MinderNG\PageMicrocode\Controller\Exception;

use Exception;
use MinderNG\PageMicrocode\Component\DataSet;

class DataSetHasNoPrimaryKEy extends Exception {
    public function __construct(DataSet $dataSet, Exception $previous = null)
    {
        parent::__construct('DataSet ' . $dataSet->getId() . ' has no Primary Key', 0, $previous);
    }
}