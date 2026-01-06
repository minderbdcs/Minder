<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\Collection;

class TransactionFieldCollection extends Collection {
    protected function _getDefaultModelClassName()
    {
        return __NAMESPACE__ . '\\TransactionField';
    }

    public function getTransactionUpdateFieldList(Transaction $transaction) {
        return $this->where(array(
            TransactionField::FIELD_SCREEN_NAME => $transaction->SS_NAME,
            TransactionField::FIELD_VARIANCE => $transaction->SS_VARIANCE,
            TransactionField::FIELD_ACTION => $transaction->SST_ACTION,
            TransactionField::FIELD_TRANSACTION_TYPE => $transaction->SST_TRN_TYPE,
            TransactionField::FIELD_TRANSACTION_CLASS => $transaction->SST_TRN_CLASS,
            TransactionField::FIELD_ROLE => TransactionField::ROLE_UPDATE,
        ));
    }

    public function getTransactionFieldList(Transaction $transaction) {
        return $this->where(array(
            TransactionField::FIELD_SCREEN_NAME => $transaction->SS_NAME,
            TransactionField::FIELD_VARIANCE => $transaction->SS_VARIANCE,
            TransactionField::FIELD_ACTION => $transaction->SST_ACTION,
            TransactionField::FIELD_TRANSACTION_TYPE => $transaction->SST_TRN_TYPE,
            TransactionField::FIELD_TRANSACTION_CLASS => $transaction->SST_TRN_CLASS,
        ));
    }
}