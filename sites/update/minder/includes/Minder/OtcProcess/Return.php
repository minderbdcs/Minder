<?php

class Minder_OtcProcess_Return extends Minder_OtcProcess {


    /**
     * @param Minder_OtcProcess_State $state
     * @return bool
     */
    protected function _isIncompleteProcess(Minder_OtcProcess_State $state) {
        return (!$state->returnFrom->existed)
               || (!$state->returnTo->existed)
               || ($state->returnTo->location == $state->item->getLocationId())
               || !$state->item->isExisted()
               || $state->committed;
    }

    /**
     * @return string
     */
    protected function _getProcessId()
    {
        return static::RETURNS;
    }

    /**
     * @throws Exception
     * @param Minder_OtcProcess_State $processState
     * @return Minder_OtcProcess_State
     */
    protected function _runTrolTransaction(Minder_OtcProcess_State $processState)
    {
        $processState->transactionType = 'TROL';

        $transaction           		= new Transaction_TROLA();
        $transaction->objectId 		= $processState->item->id;
        $transaction->whId     		= $processState->returnFrom->getWhId();
        $transaction->locnId   		= $processState->returnFrom->getLocnId();
        $transaction->quantity		= 1; //$qty;
        $transaction->reference		= $processState->returnTo->location;
        $transaction->transClass	= 'A';
        $transaction->companyId     = $processState->item->companyId;

        $processState->transactionMessage = $this->_getMinder()->doTransactionResponseV6($transaction);

        return $processState;
    }

    /**
     * @throws Exception
     * @param Minder_OtcProcess_State $processState
     * @return Minder_OtcProcess_State
     */
    protected function _runTrilTransaction(Minder_OtcProcess_State $processState)
    {
        $processState->transactionType = 'TRIL';

        $transaction           		= new Transaction_TRILA();
        $transaction->objectId 		= $processState->item->id;
        $transaction->whId     		= $processState->returnTo->getWhId();
        $transaction->locnId   		= $processState->returnTo->getLocnId();
        $transaction->transClass	= 'A';
        $transaction->reference		= $processState->chargeTo->id; // cost centre
        $transaction->quantity		= 0; //$qty;
        $transaction->companyId     = $processState->item->companyId;

        $processState->transactionMessage = $this->_getMinder()->doTransactionResponseV6($transaction);

        return $processState;
    }

    /**
     * @param Minder_OtcProcess_State $processState
     * @return Minder_OtcProcess_State
     */
    protected function _tryTransaction(Minder_OtcProcess_State $processState)
    {
        if (empty($processState->returnTo->location)) {
            $processState->transactionMessage = 'Empty return location';
            return $processState;
        }

        $processState->transactionType    = null;
        $processState->transactionMessage = null;

        if (empty($processState->issueQty))
            $processState->issueQty = 1;

        if ($this->_isIncompleteProcess($processState))
            return $processState;

        try {
            if ($processState->item->isTool())
            {
                $processState = $this->_runTrolTransaction($processState);
                $processState = $this->_runTrilTransaction($processState);
                $processState = $this->_runNiccTransaction($processState);
            }
        } catch (Exception $e) {
            $processState->transactionMessage = $e->getMessage();
        }

        $processState->committed = true;

        return $processState;
    }

    /**
     * @param Minder_OtcProcess_State $processState
     * @return Minder_OtcProcess_State
     */
    protected function _proceedTool(Minder_OtcProcess_State $processState)
    {
        $originalLocation = $processState->returnTo;
        $defaultReturnLocation = self::getDefaultReturnLocation();

        if (empty($processState->returnTo->location)) {
            $locn = $this->_getMinder()->getLocn(substr($defaultReturnLocation, 2), substr($defaultReturnLocation, 0, 2));
            $processState->setReturnTo(new Minder_OtcProcess_State_Location($defaultReturnLocation, 'S', $locn), $defaultReturnLocation);
        }

        $processState = $this->_tryTransaction($processState);

        if (!is_null($processState->transactionMessage)) {
            $saveState                     = new Minder_OtcProcess_SaveState();
            $saveState->transactionMessage = $processState->transactionMessage;
            $saveState->save_datetime      = $this->_getDatetime();
            $saveState->save_qty           = 1;
            $saveState->save_desc          = $processState->item->description;
            $saveState->save_ssn_id        = $processState->item->id;
            $saveState->save_cc            = $processState->chargeTo->id;
            $saveState->save_location      = $processState->returnTo->location;
            $processState->save[]          = $saveState;
            // By #413
            $processState->returnFrom->loanedTotal = $this->_getMinder()->getLoanedByBorrower($processState->returnFrom->getLocnId());
        }

        $processState->setReturnTo($originalLocation, $defaultReturnLocation);
        return $processState;
    }

    /**
     * @param $location
     * @param $via
     * @return Minder_OtcProcess_State
     */
    public function setLocation($location, $via)
    {
        $locn = $this->_getMinder()->getLocn(substr($location, 2), substr($location, 0, 2));

        $processState = $this->_restoreState()->_getState();

        $processState->setReturnTo(new Minder_OtcProcess_State_Location($location, $via, $locn), self::getDefaultReturnLocation());
        $processState->committed = false;
        $processState->setItem(new Minder_OtcProcess_State_AbstractItem());
        $processState->returnFrom = new Minder_OtcProcess_State_AbstractLocation();

        $this->_setState($processState)->_saveState();
        return $processState;
    }

    /**
     * @param $borrowerId
     * @param $via
     * @return Minder_OtcProcess_State
     */
    public function setBorrower($borrowerId, $via)
    {
        $processState = $this->_restoreState()->_getState();

        $processState->returnFrom = $this->_getBorrowerManager()->getBorrower($borrowerId, $via);
        $processState->setItem(new Minder_OtcProcess_State_AbstractItem(), self::getDefaultReturnLocation());
        $processState->committed = false;

        $this->_setState($processState)->_saveState();
        return $processState;
    }


    protected function _getBorrowerFromTool(Minder_OtcProcess_State $processState) {
        /**
         * @var Minder_OtcProcess_State_Tool $tool
         */
        $tool = $processState->item;

        if ('XB' == $tool->whId) {
            $processState->returnFrom = $this->_getBorrowerManager()->getBorrower($tool->locnId, 'S');
        } else {
            $locn = $this->_getMinder()->getLocn($tool->locnId, $tool->whId);
            $processState->returnFrom = new Minder_OtcProcess_State_Location($tool->whId . $tool->locnId, 'S', $locn);
        }

        return $processState;
    }

    /**
     * @param Minder_OtcProcess_State_Tool $tool
     * @return Minder_OtcProcess_State
     * @throws Exception
     */
    protected function _setTool(Minder_OtcProcess_State_Tool $tool) {
        $processState = $this->_restoreState()->_getState();
        $processState->setItem($tool, self::getDefaultReturnLocation());
        $processState->issueQty = 1;
        $processState->issueQtyDescription = 'EA';
        $processState->issueQtyVia = 'S';
        $processState->committed = false;

        if (!$tool->isExisted()) {
            $this->_setState($processState)->_saveState();
            return $processState;
        }

        $processState = $this->_getBorrowerFromTool($processState);

        if ($this->_isCompleteProcess($processState)) {
            $processState = $this->_proceedTool($processState);
        }

        $this->_setState($processState)->_saveState();
        return $processState;
    }

    protected function _updateHomeLocation(Minder_OtcProcess_State $processState) {
        $transaction = new Transaction_NIHL(
            $processState->item->id,
            $processState->returnTo->location,
            $processState->item->currentQty,
            $processState->item->companyId
        );

        try {
            $processState->transactionMessage = $this->_getMinder()->doTransactionResponseV6($transaction, 'Y');
        } catch (Exception $e) {
            $processState->transactionMessage = $e->getMessage();
        }

        $processState->setItem($this->_getToolManager()->reloadTool($processState->item), self::getDefaultReturnLocation());

        return $processState;
    }

    public function recordHome()
    {
        $processState = $this->_restoreState()->_getState();

        if (!$processState->item->isExisted() || !$processState->item->isTool()) {
            $processState->item->description = 'Scan Item first.';
            $this->_setState($processState)->_saveState();
            return $processState;
        }

        if (!$processState->returnTo->existed) {
            $processState->item->description = 'Scan Home Location first.';
            $this->_setState($processState)->_saveState();
            return $processState;
        }

        if (!$processState->item->isExisted()) {
            $processState->item->description = 'Item not found.';
            $this->_setState($processState)->_saveState();
            return $processState;
        }

        $defaultReturnLocation = $this->getDefaultReturnLocation();

        if ($defaultReturnLocation != $processState->returnTo->location) {
            $processState = $this->_updateHomeLocation($processState);
        }

        return $this->_setState($processState)->_saveState()->getState();
    }


}