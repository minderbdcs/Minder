<?php

class Minder2_Model_Mapper_Tool extends Minder2_Model_Mapper_Abstract {

    /**
     * @param Minder2_Model_Tool $tool
     * @return Minder2_Model_Tool
     */
    public function save(Minder2_Model_Tool $tool) {
        if (empty($tool->SSN_ID))
            $tool = $this->_runAUOBCTransaction($tool);
        else
            $tool = $this->_runAUOBATransaction($tool);

        if (!empty($tool->SSN_TYPE))
            $tool = $this->_runNITPATransaction($tool); //update SSN_TYPE

        if (!empty($tool->GENERIC))
            $tool = $this->_runNIOBATransaction($tool); //update GENERIC

        if (!empty($tool->SSN_SUB_TYPE))
            $tool = $this->_runNID3ATransaction($tool); //update SSN_SUBTYPE

        if (!empty($tool->BRAND))
            $tool = $this->_runNIBCATransaction($tool); //update BRAND

        if (!empty($tool->MODEL))
            $tool = $this->_runNIMOATransaction($tool); //update MODEL

        if (!empty($tool->SERIAL_NUMBER))
            $tool = $this->_runNISNATransaction($tool); //update SERIAL_NUMBER

        if (!empty($tool->LEGACY_ID))
            $tool = $this->_runNILGATransaction($tool); //update LEGACY_ID

        if (is_numeric($tool->PURCHASE_PRICE))
            $tool = $this->_runNIPPATransaction($tool); //update PURCHASE_PRICE

        if (!empty($tool->PO_ORDER))
            $tool = $this->_runNIPOATransaction($tool); //update PO_ORDER

        if (false === $this->_getMinder()->updateSsn($tool->SSN_ID, 'ALT_NAME', $tool->ALT_NAME))
            throw new Minder_Exception('Error updating ALT_NAME: ' . $this->_getMinder()->lastError);

        if (false === $this->_getMinder()->updateSsn($tool->SSN_ID, 'COMPANY_ID', $tool->COMPANY_ID))
            throw new Minder_Exception('Error updating COMPANY_ID: ' . $this->_getMinder()->lastError);

        if (false === $this->_getMinder()->updateIssn($tool->SSN_ID, 'COMPANY_ID', $tool->COMPANY_ID))
            throw new Minder_Exception('Error updating COMPANY_ID: ' . $this->_getMinder()->lastError);

        if (false === $this->_getMinder()->updateSsn($tool->SSN_ID, 'LOAN_SAFETY_CHECK', $tool->LOAN_SAFETY_CHECK))
            throw new Minder_Exception('Error updating LOAN_SAFETY_CHECK: ' . $this->_getMinder()->lastError);

        if (false === $this->_getMinder()->updateSsn($tool->SSN_ID, 'LOAN_LAST_SAFETY_CHECK_DATE', $tool->LOAN_LAST_SAFETY_CHECK_DATE))
            throw new Minder_Exception('Error updating LOAN_LAST_SAFETY_CHECK_DATE: ' . $this->_getMinder()->lastError);

        if (false === $this->_getMinder()->updateSsn($tool->SSN_ID, 'LOAN_SAFETY_PERIOD_NO', $tool->LOAN_SAFETY_PERIOD_NO))
            throw new Minder_Exception('Error updating LOAN_SAFETY_PERIOD_NO: ' . $this->_getMinder()->lastError);

        if (false === $this->_getMinder()->updateSsn($tool->SSN_ID, 'LOAN_SAFETY_PERIOD', $tool->LOAN_SAFETY_PERIOD))
            throw new Minder_Exception('Error updating LOAN_SAFETY_PERIOD: ' . $this->_getMinder()->lastError);


        if (false === $this->_getMinder()->updateSsn($tool->SSN_ID, 'LOAN_CALIBRATE_CHECK', $tool->LOAN_CALIBRATE_CHECK))
            throw new Minder_Exception('Error updating LOAN_CALIBRATE_CHECK: ' . $this->_getMinder()->lastError);

        if (false === $this->_getMinder()->updateSsn($tool->SSN_ID, 'LOAN_LAST_CALIBRATE_CHECK_DATE', $tool->LOAN_LAST_CALIBRATE_CHECK_DATE))
            throw new Minder_Exception('Error updating LOAN_LAST_CALIBRATE_CHECK_DATE: ' . $this->_getMinder()->lastError);

        if (false === $this->_getMinder()->updateSsn($tool->SSN_ID, 'LOAN_CALIBRATE_PERIOD_NO', $tool->LOAN_CALIBRATE_PERIOD_NO))
            throw new Minder_Exception('Error updating LOAN_CALIBRATE_PERIOD_NO: ' . $this->_getMinder()->lastError);

        if (false === $this->_getMinder()->updateSsn($tool->SSN_ID, 'LOAN_CALIBRATE_PERIOD', $tool->LOAN_CALIBRATE_PERIOD))
            throw new Minder_Exception('Error updating LOAN_CALIBRATE_PERIOD: ' . $this->_getMinder()->lastError);


        if (false === $this->_getMinder()->updateSsn($tool->SSN_ID, 'LOAN_INSPECT_CHECK', $tool->LOAN_INSPECT_CHECK))
            throw new Minder_Exception('Error updating LOAN_INSPECT_CHECK: ' . $this->_getMinder()->lastError);

        if (false === $this->_getMinder()->updateSsn($tool->SSN_ID, 'LOAN_LAST_INSPECT_CHECK_DATE', $tool->LOAN_LAST_INSPECT_CHECK_DATE))
            throw new Minder_Exception('Error updating LOAN_LAST_INSPECT_CHECK_DATE: ' . $this->_getMinder()->lastError);

        if (false === $this->_getMinder()->updateSsn($tool->SSN_ID, 'LOAN_INSPECT_PERIOD_NO', $tool->LOAN_INSPECT_PERIOD_NO))
            throw new Minder_Exception('Error updating LOAN_INSPECT_PERIOD_NO: ' . $this->_getMinder()->lastError);

        if (false === $this->_getMinder()->updateSsn($tool->SSN_ID, 'LOAN_INSPECT_PERIOD', $tool->LOAN_INSPECT_PERIOD))
            throw new Minder_Exception('Error updating LOAN_INSPECT_PERIOD: ' . $this->_getMinder()->lastError);

        return $tool;
    }

    protected function _updateFieldsDirect(Minder2_Model_Tool $tool, $fields) {
        $tmpSqlFieldPart = array();
        $args            = array();
        foreach ($fields as $fieldName) {
            if ($fieldName == 'SSN_ID') continue;

            $tmpSqlFieldPart[] = $fieldName . ' = ?';
            $args[]            = $tool->$fieldName;
        }

        if (count($tmpSqlFieldPart) < 1) return $tool;

        $sql = "UPDADE SSN SET " . PHP_EOL
               . implode(',' . PHP_EOL, $tmpSqlFieldPart) . PHP_EOL . 'WHERE SSN_ID = ?';

        $args[] = $tool->SSN_ID;

        $this->_getMinder()->execSQL($sql, $args);
        return $tool;
    }

    /**
     * @param Minder2_Model_Tool $tool
     * @return Minder2_Model_Tool
     */
    protected function _runAUOBCTransaction(Minder2_Model_Tool $tool) {
        $transaction = new Transaction_AUOBC();
        $transaction->whId = $tool->WH_ID;
        $transaction->locnId = $tool->LOCN_ID;

        if (false === ($transactionResponse = $this->_getMinder()->doTransactionResponse($transaction)))
            throw new Minder_Exception('Error ' . $transaction->transCode . $transaction->transClass . ' transaction: ' . $this->_getMinder()->lastError);

        $responseArray = explode('|', $transactionResponse);
        if (empty($responseArray) || empty($responseArray[0]))
            throw new Minder_Exception('Bad AUOB C response "' . $transactionResponse . '".');

        $tool->SSN_ID = $responseArray[0];

        return $tool;
    }

    protected function _runAUOBATransaction(Minder2_Model_Tool $tool) {
        $transaction = new Transaction_AUOBA();
        $transaction->ssnId = $tool->SSN_ID;
        $transaction->whId = $tool->WH_ID;
        $transaction->locnId = $tool->LOCN_ID;

        if (false === $this->_getMinder()->doTransactionResponse($transaction))
            throw new Minder_Exception('Error ' . $transaction->transCode . $transaction->transClass . ' transaction: ' . $this->_getMinder()->lastError);

        return $tool;
    }

    protected function _runNITPATransaction(Minder2_Model_Tool $tool) {
        $transaction = new Transaction_NITPA();
        $transaction->ssnId = $tool->SSN_ID;
        $transaction->ssnTypeValue = $tool->SSN_TYPE;
        $transaction->whId = $tool->WH_ID;
        $transaction->locnId = $tool->LOCN_ID;

        if (false === $this->_getMinder()->doTransactionResponse($transaction))
            throw new Minder_Exception('Error ' . $transaction->transCode . $transaction->transClass . ' transaction: ' . $this->_getMinder()->lastError);

        return $tool;
    }

    protected function _runNIOBATransaction(Minder2_Model_Tool $tool) {
        $transaction = new Transaction_NIOBA();
        $transaction->ssnId = $tool->SSN_ID;
        $transaction->genericValue = $tool->GENERIC;
        $transaction->whId = $tool->WH_ID;
        $transaction->locnId = $tool->LOCN_ID;

        if (false === $this->_getMinder()->doTransactionResponse($transaction))
            throw new Minder_Exception('Error ' . $transaction->transCode . $transaction->transClass . ' transaction: ' . $this->_getMinder()->lastError);

        return $tool;
    }

    protected function _runNID3ATransaction(Minder2_Model_Tool $tool) {
        $transaction = new Transaction_NID3A();
        $transaction->ssnId = $tool->SSN_ID;
        $transaction->ssnSubTypeValue = $tool->SSN_SUB_TYPE;
        $transaction->whId = $tool->WH_ID;
        $transaction->locnId = $tool->LOCN_ID;

        if (false === $this->_getMinder()->doTransactionResponse($transaction))
            throw new Minder_Exception('Error ' . $transaction->transCode . $transaction->transClass . ' transaction: ' . $this->_getMinder()->lastError);

        return $tool;
    }

    protected function _runNIBCATransaction(Minder2_Model_Tool $tool) {
        $transaction = new Transaction_NIBCA();
        $transaction->ssnId = $tool->SSN_ID;
        $transaction->brandCodeValue = $tool->BRAND;
        $transaction->whId = $tool->WH_ID;
        $transaction->locnId = $tool->LOCN_ID;

        if (false === $this->_getMinder()->doTransactionResponse($transaction))
            throw new Minder_Exception('Error ' . $transaction->transCode . $transaction->transClass . ' transaction: ' . $this->_getMinder()->lastError);

        return $tool;
    }

    protected function _runNIMOATransaction(Minder2_Model_Tool $tool) {
        $transaction = new Transaction_NIMOA();
        $transaction->ssnId = $tool->SSN_ID;
        $transaction->model = $tool->MODEL;
        $transaction->whId = $tool->WH_ID;
        $transaction->locnId = $tool->LOCN_ID;

        if (false === $this->_getMinder()->doTransactionResponse($transaction))
            throw new Minder_Exception('Error ' . $transaction->transCode . $transaction->transClass . ' transaction: ' . $this->_getMinder()->lastError);

        return $tool;
    }

    protected function _runNISNATransaction(Minder2_Model_Tool $tool) {
        $transaction = new Transaction_NISNA();
        $transaction->ssnId = $tool->SSN_ID;
        $transaction->serialNumber = $tool->SERIAL_NUMBER;
        $transaction->whId = $tool->WH_ID;
        $transaction->locnId = $tool->LOCN_ID;

        if (false === $this->_getMinder()->doTransactionResponse($transaction))
            throw new Minder_Exception('Error ' . $transaction->transCode . $transaction->transClass . ' transaction: ' . $this->_getMinder()->lastError);

        return $tool;
    }

    protected function _runNILGATransaction(Minder2_Model_Tool $tool) {
        $transaction = new Transaction_NILGA();
        $transaction->ssnId = $tool->SSN_ID;
        $transaction->legacyId = $tool->LEGACY_ID;
        $transaction->whId = $tool->WH_ID;
        $transaction->locnId = $tool->LOCN_ID;

        if (false === $this->_getMinder()->doTransactionResponse($transaction))
            throw new Minder_Exception('Error ' . $transaction->transCode . $transaction->transClass . ' transaction: ' . $this->_getMinder()->lastError);

        return $tool;
    }

    protected function _runNIPPATransaction(Minder2_Model_Tool $tool) {
        $transaction = new Transaction_NIPPA();
        $transaction->ssnId = $tool->SSN_ID;
        $transaction->purchasePrice = $tool->PURCHASE_PRICE;
        $transaction->whId = $tool->WH_ID;
        $transaction->locnId = $tool->LOCN_ID;

        if (false === $this->_getMinder()->doTransactionResponse($transaction))
            throw new Minder_Exception('Error ' . $transaction->transCode . $transaction->transClass . ' transaction: ' . $this->_getMinder()->lastError);

        return $tool;
    }

    protected function _runNIPOATransaction(Minder2_Model_Tool $tool) {
        $transaction = new Transaction_NIPOA($tool->SSN_ID, $tool->PO_ORDER, $tool->WH_ID, $tool->LOCN_ID);

        if (false === $this->_getMinder()->doTransactionResponse($transaction))
            throw new Minder_Exception('Error ' . $transaction->transCode . $transaction->transClass . ' transaction: ' . $this->_getMinder()->lastError);

        return $tool;
    }
}