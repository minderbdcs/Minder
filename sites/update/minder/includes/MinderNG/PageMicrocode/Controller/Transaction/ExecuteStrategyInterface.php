<?php

namespace MinderNG\PageMicrocode\Controller\Transaction;

use MinderNG\PageMicrocode\Component\DataSetRow;
use MinderNG\PageMicrocode\Component\Transaction;

interface ExecuteStrategyInterface {

    /**
     * @param Transaction $transaction
     * @param DataSetRow $dataSetRow
     * @return boolean
     */
    public function shouldExecute(Transaction $transaction, DataSetRow $dataSetRow);
}