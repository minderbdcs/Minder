<?php

namespace MinderNG\PageMicrocode\Component\Exception;

use Exception;
use MinderNG\PageMicrocode\Component\DataSetRow;

class DataSetRowNotFound extends Exception {
    public function __construct(DataSetRow $dataSetRow, $code = 0, Exception $previous = null)
    {
        parent::__construct('DataSetRow #' . $dataSetRow->getId() . ' not found', $code, $previous);
    }

}