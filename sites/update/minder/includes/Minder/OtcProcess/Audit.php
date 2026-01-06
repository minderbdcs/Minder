<?php

class Minder_OtcProcess_Audit extends Minder_OtcProcess {

    /**
     * @return string
     */
    protected function _getProcessId()
    {
        return Minder_OtcProcess::AUDIT;
    }

    /**
     * @param Minder_OtcProcess_State $state
     * @return bool
     */
    protected function _isIncompleteProcess(Minder_OtcProcess_State $state)
    {
        // TODO: Implement _isIncompleteProcess() method.
    }

    public function save()
    {
        $processState = $this->_restoreState()->_getState();
        $processState = $this->_closeLocation($processState);
        $this->_setState($processState)->_saveState();
        return $processState;
    }

    public function setLocation($location, $via)
    {
        $locn = $this->_getMinder()->getLocn(substr($location, 2), substr($location, 0, 2));

        $processState = $this->_restoreState()->getState();

        $newLocation = new Minder_OtcProcess_State_Location($location, $via, $locn);
        $processState->committed = false;

        if ($processState->auditLocation != $newLocation) {
            $processState = $this->_closeLocation($processState);
        }

        if (!($processState->auditLocation->isExistedAndOpened())) {
            $processState->auditLocation = $newLocation;
            $processState->expectedQty = 0;
            $processState->setItem(new Minder_OtcProcess_State_AbstractItem());

            $processState = $this->_openLocation($processState);
        }


        return $this->_setState($processState)->_saveState()->getState();
    }


    protected function _setTool(Minder_OtcProcess_State_Tool $tool)
    {
        $processState = $this->_restoreState()->_getState();
        $processState->setItem($tool, self::getDefaultReturnLocation());
        $processState->committed = false;

        $this->_auditTool($processState);
        $this->_setState($processState)->_saveState();
        return $processState;
    }

    private function _runStloTransaction(Minder_OtcProcess_State $processState) {
        $transaction                = new Transaction_STLOA();
        $transaction->locationId    = $processState->auditLocation->getLocnId();
        $transaction->whId          = $processState->auditLocation->getWhId();

        $minder = $this->_getMinder();

        if (false === ($result = $minder->doTransactionResponse($transaction, 'Y', $processState->formatTransactionSource(), '', 'MASTER    ')))
            throw new Exception($minder->lastError . '   - a');

        $parsedResponse = $transaction->parseResponse($result);
        $processState->transactionMessage = $parsedResponse->getMessage();
        $processState->expectedQty = $parsedResponse->getIssnQty();

        if ($parsedResponse->isSuccess()) {
            $processState->auditLocation->open();
            $processState->checkedIssnList = array();


        }

        return $processState;
    }

    private function _openLocation(Minder_OtcProcess_State $processState)
    {
        if ((!$processState->auditLocation->existed) || $processState->auditLocation->opened) {
            return $processState;
        }

        try {
            $this->_runStloTransaction($processState);
        } catch (Exception $e) {
            $processState->transactionMessage = $e->getMessage();
        }

        return $processState;
    }

    private function _runStlxTransaction(Minder_OtcProcess_State $processState)
    {
        $transaction = new Transaction_STLXA();
        $transaction->whId = $processState->auditLocation->getWhId();
        $transaction->locationId = $processState->auditLocation->getLocnId();

        $minder = $this->_getMinder();

        if (false === ($result = $minder->doTransactionResponse($transaction, 'Y', $processState->formatTransactionSource(), '', 'MASTER    ')))
            throw new Exception($minder->lastError . '   - a');

        $parsedResponse = $transaction->parseResponse($result);
        $processState->transactionMessage = $result;

        if ($parsedResponse->isSuccess()) {
            $processState->expectedQty = 0;
            $processState->auditLocation->close();
            $processState->checkedIssnList = array();
        }

        return $processState;

    }

    private function _closeLocation(Minder_OtcProcess_State $processState)
    {
        if (!$processState->auditLocation->isExistedAndOpened()) {
            return $processState;
        }

        try {
            $this->_runStlxTransaction($processState);
        } catch (Exception $e) {
            $processState->transactionMessage = $e->getMessage();
        }

        return $processState;
    }

    private function _auditTool(Minder_OtcProcess_State $processState)
    {
        if (!($processState->item->isExisted() && $processState->auditLocation->isExistedAndOpened())) {
            return $processState;
        }

        try {
            $this->_runStisTransaction($processState);
        } catch (Exception $e) {
            $processState->transactionMessage = $e->getMessage();
        }

        return $processState;
    }

    private function _runStisTransaction(Minder_OtcProcess_State $processState)
    {
        $transaction = new Transaction_STISS(
            $processState->item->id,
            $processState->auditLocation->getWhId(),
            $processState->auditLocation->getLocnId()
        );

        $minder = $this->_getMinder();

        if (false === ($result = $minder->doTransactionResponse($transaction, 'Y', $processState->formatTransactionSource(), '', 'MASTER    ')))
            throw new Exception($minder->lastError . '   - a');

        $parsedResponse = $transaction->parseResponse($result);
        $processState->transactionMessage = $result;

        if ($parsedResponse->isSuccess()) {
            $processState->committed = true;
            if (!in_array($processState->item->id, $processState->checkedIssnList)) {
                $processState->checkedIssnList[] = $processState->item->id;

                $saveData = new Minder_OtcProcess_SaveState();
                $saveData->save_location = $processState->auditLocation->location;
                $saveData->save_datetime = $this->_getDatetime();
                $saveData->save_ssn_id = $processState->item->id;
                $saveData->save_desc = $processState->item->description;
                $saveData->transactionMessage = 'Processed successfully';

                $processState->save[] = $saveData;
            }
        }

        return $processState;
    }

    public function endAudit()
    {
        $processState = $this->_restoreState()->getState();

        $processState->setItem(new Minder_OtcProcess_State_Tool());
        $processState->committed = false;

        $processState = $this->_closeLocation($processState);

        return $this->_setState($processState)->_saveState()->getState();
    }
}