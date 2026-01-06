<?php

class Minder_OtcProcess_Manager_ToolTransaction {
    public function getToolTransaction($descriptionLabel) {
        $result = new Minder_OtcProcess_State_ToolTransaction($descriptionLabel, true);

        if (empty($result->descriptionLabel)) {
            $result->setError('Empty description label');
            return $result;
        }

        $transactionType = $this->_getTransactionType($result);

        if (empty($transactionType)) {
            $result->setError('Cannot find TRANSACTION_TYPE for TRANSACTION_PREFIX "' . $result->prefix . '"');
            return $result;
        }

        $transactionType = current($transactionType);
        $result->type = $transactionType['TRN_TYPE'];

        return $result;
    }

    protected function _getMinder() {
        return Minder::getInstance();
    }

    protected function _getTransactionType(Minder_OtcProcess_State_ToolTransaction $result)
    {
        $transactionType = array();
        try {
            $transactionType = $this->_getMinder()->getTrn($result->prefix);
        } catch (Exception $e) {}

        return $transactionType;
    }
}