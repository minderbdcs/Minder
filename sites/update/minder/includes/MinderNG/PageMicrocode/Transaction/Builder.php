<?php

namespace MinderNG\PageMicrocode\Transaction;

use MinderNG\Filter\FilterInterface;
use MinderNG\PageMicrocode\Component;

class Builder {
    /**
     * @var FilterBuilder
     */
    private $_filterBuilder;

    /**
     * @param Component\Transaction $transaction
     * @param \Iterator|Component\TransactionField[] $transactionFields
     * @param array $sharedValues
     * @param array $originalValues
     * @return Transaction
     */
    public function buildTransaction(Component\Transaction $transaction, $transactionFields, array $sharedValues, array $originalValues) {
        $fieldValues = array();

        foreach($transactionFields as $field) {
            if ($field->isRoleResult()) { continue; }

            $value = null;

            switch (true) {
                case $field->isRoleKey():
                    $value = $this->_getFilteredColumnValue($field, $originalValues);
                    break;
                case $field->isRoleUpdate():
                    $value = $this->_getFilteredColumnValue($field, $sharedValues);
                    break;
                default:
                    $value = $field->SST_COLUMN;
            }

            $fieldValues[$field->SST_TRN_FIELD] = isset($fieldValues[$field->SST_TRN_FIELD]) ? $fieldValues[$field->SST_TRN_FIELD] : '';
            $fieldValues[$field->SST_TRN_FIELD] .= $value;
        }

        return new Transaction($transaction->SST_TRN_TYPE, $transaction->SST_TRN_CLASS, $fieldValues);
    }

    /**
     * @param Transaction $transaction
     * @param \Iterator|Component\TransactionField[] $transactionFields
     * @param array $sharedValues
     * @return mixed
     */
    public function updateSharedValues(Transaction $transaction, $transactionFields, $sharedValues) {
        foreach ($transactionFields as $field) {
            switch (true) {
                case $field->isRoleResult():
                    $sharedValues[$field->SST_COLUMN] = $this->_getFilteredTrnFieldValue($field, $transaction);
                    break;
            }
        }

        return $sharedValues;
    }

    /**
     * @param Component\TransactionField $field
     * @param \ArrayAccess|array $data
     * @return mixed
     */
    private function _getFilteredColumnValue(Component\TransactionField $field, $data) {
        $value = isset($data[$field->SST_COLUMN]) ? $data[$field->SST_COLUMN] : '';
        return $this->_getColumnFilter($field)->filter($value);
    }

    /**
     * @param Component\TransactionField $field
     * @return \MinderNG\Filter\Chain
     */
    private function _getColumnFilter(Component\TransactionField $field)
    {
        return $this->_getFilterBuilder()->buildFilter($field->SST_COLUMN_EXPRESSION);
    }

    /**
     * @param Component\TransactionField $field
     * @param \ArrayAccess|array $data
     * @return mixed
     */
    private function _getFilteredTrnFieldValue(Component\TransactionField $field, $data) {
        $value = isset($data[$field->SST_TRN_FIELD]) ? $data[$field->SST_TRN_FIELD] : '';
        return $this->_getTrnFieldFilter($field)->filter($value);
    }

    private function _getTrnFieldFilter(Component\TransactionField $field) {
        return $this->_getFilterBuilder()->buildFilter($field->SST_TRN_FIELD_EXPRESSION);
    }

    private function _getFilterBuilder() {
        if (empty($this->_filterBuilder)) {
            $this->_filterBuilder = new FilterBuilder();
        }

        return $this->_filterBuilder;
    }
}