<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\Collection;

class TransactionCollection extends Collection {
    protected function _getDefaultModelClassName()
    {
        return __NAMESPACE__ . '\\Transaction';
    }

    /**
     * @param DataSet $dataSet
     * @param $action
     * @return \Iterator|Transaction[]
     */
    public function getDataSetTransactionListByAction(DataSet $dataSet, $action) {
        $action = strtoupper($action);

        return $this->where(array(
            Transaction::FIELD_SCREEN_NAME => $dataSet->SS_NAME,
            Transaction::FIELD_VARIANCE => $dataSet->SS_VARIANCE,
            Transaction::FIELD_ACTION => $action,
        ));
    }
}