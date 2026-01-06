<?php

namespace MinderNG\PageMicrocode\Controller\Exception;

use Exception;
use MinderNG\PageMicrocode\Component\DataSetRow;

class DataSetRowUpdateError extends Exception {
    public function __construct(DataSetRow $dataSetRow, $code = 0, Exception $previous = null)
    {
        $message = 'Error saving DataSetRow #' . $dataSetRow->getId() . ' changes.';

        if (!empty($previous)) {
            $message .= ' Reason: ' . $previous->getMessage();
        }

        parent::__construct($message, $code, $previous);
    }

}