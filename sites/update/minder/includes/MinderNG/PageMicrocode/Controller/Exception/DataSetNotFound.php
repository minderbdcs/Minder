<?php

namespace MinderNG\PageMicrocode\Controller\Exception;

use Exception;

class DataSetNotFound extends Exception {
    public function __construct($dataSetId, Exception $previous = null)
    {
        parent::__construct('DataSet ' . $dataSetId . ' not found.', 0, $previous);
    }

}