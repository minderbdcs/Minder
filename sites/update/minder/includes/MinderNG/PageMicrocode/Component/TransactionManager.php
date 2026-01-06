<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\AddOptions;

class TransactionManager {
    public function getTransactions(TransactionFieldCollection $fields) {
        $result = new TransactionCollection();
        $result->init(iterator_to_array($fields->getArrayCopy()), new AddOptions(false, true));

        return $result;
    }
}