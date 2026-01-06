<?php

namespace MinderNG\PageMicrocode\Controller\Transaction;

use MinderNG\Collection\Helper\Helper;
use MinderNG\PageMicrocode\Component;

class ExecuteStrategyChanged implements ExecuteStrategyInterface {
    /**
     * @var Component\Components
     */
    private $_components;
    /**
     * @var Helper
     */
    private $_collectionHelper;

    function __construct(Component\Components $components)
    {
        $this->_components = $components;
    }

    /**
     * @param Component\Transaction $transaction
     * @param Component\DataSetRow $dataSetRow
     * @return boolean
     */
    public function shouldExecute(Component\Transaction $transaction, Component\DataSetRow $dataSetRow)
    {
        $updateFields = $this->_components->transactionFieldCollection->getTransactionUpdateFieldList($transaction);

        $fieldAliases = iterator_to_array($this->_getCollectionHelper()->pluck($updateFields, Component\TransactionField::FIELD_COLUMN));
        $changedColumns = array_keys($dataSetRow->changedAttributes());

        return count(array_intersect($fieldAliases, $changedColumns)) > 0;
    }

    /**
     * @return Helper
     */
    private function _getCollectionHelper() {
        if (empty($this->_collectionHelper)) {
            $this->_collectionHelper = new Helper();
        }

        return $this->_collectionHelper;
    }
}