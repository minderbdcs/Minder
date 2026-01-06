<?php

namespace MinderNG\PageMicrocode\Controller\Exception;

use Exception;
use MinderNG\PageMicrocode\Component\DataSetRow;

class TransactionError extends Exception {
    /**
     * @var DataSetRow
     */
    private $_dataSetRow;

    public function __construct($message = "", DataSetRow $dataSetRow, $code = 0, Exception $previous = null)
    {
        $this->_dataSetRow = $dataSetRow;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return DataSetRow
     */
    public function getDataSetRow()
    {
        return $this->_dataSetRow;
    }

}