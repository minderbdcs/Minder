<?php

namespace MinderNG\PageMicrocode\Controller\Exception;

use Exception;

class DataSetRowNotFound extends Exception {
    public function __construct($dataSetRowId, $dataSetId, Exception $previous = null)
    {
        parent::__construct('DataSet row ' . $dataSetId . '#' . $dataSetRowId . ' not found.', 0, $previous);
    }

}