<?php

namespace MinderNG\PageMicrocode\Controller\Exception;

use Exception;
use MinderNG\PageMicrocode\Component\DataSetRow;

class ReloadRowError extends Exception {
    /**
     * ReloadRowError constructor.
     * @param DataSetRow $dataSetRow
     * @param int $code
     * @param Exception $previous
     */
    public function __construct(DataSetRow $dataSetRow, $code = 0, Exception $previous = null)
    {
        $message = 'Cannot reload row data: ' . (is_null($previous) ? "Row not found." : $previous->getMessage());

        parent::__construct($message, $code, $previous);
    }

}