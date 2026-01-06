<?php

namespace MinderNG\PageMicrocode\Component\Exception;

use MinderNG\PageMicrocode\Component\DataSet;

class DataSetNotFound extends Exception {
    public function __construct(DataSet $dataSet, $code = 0, Exception $previous = null)
    {
        parent::__construct('DataSet #' . $dataSet->getId() . ' not found.', $code, $previous);
    }
}