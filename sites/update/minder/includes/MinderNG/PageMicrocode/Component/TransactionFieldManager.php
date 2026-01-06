<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\AddOptions;
use MinderNG\Database\Table\SysScreenTransaction;

class TransactionFieldManager {
    function __construct(SysScreenTransaction $transactionProvider)
    {
        $this->_transactionProvider = $transactionProvider;
    }

    public function getTransactionFields(ScreenCollection $screens) {
        $screenNames = array_unique(iterator_to_array($screens->pluck('SS_NAME')));
        $transactionFields = $this->_transactionProvider->getScreenTransactions($screenNames);

        $result = new TransactionFieldCollection();
        $result->init($transactionFields, new AddOptions(false, true));

        return $result;
    }
}