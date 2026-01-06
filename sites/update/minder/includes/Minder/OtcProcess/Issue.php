<?php

class Minder_OtcProcess_Issue extends Minder_OtcProcess {

    public static function getDefaultCostCenter() {


        $deviceCostCentres = array();
        $userid_is=Minder_OtcProcess::_getMinder()->userId;
        $user_whId = Minder_OtcProcess::_getMinder()->getSysEquipData($userid_is);

        if($user_whId==""){
        $user_whId=strtoupper(self::_getMinder()->defaultControlValues['DEFAULT_WH_ID']);
        }

        $currentCompanyId = strtoupper(self::_getMinder()->defaultControlValues['COMPANY_ID']);
        //echo "wh id is now: ".$user_whId;

        $user_data = Minder_OtcProcess::_getMinder()->getSysUserData($userid_is);
        $user_companyId=$user_data['COMPANY_ID'];

        $code_val=$user_companyId."|".$user_whId;
        $code_val=$currentCompanyId."|".$user_whId;

/*
        $sql = "
            SELECT FIRST 1
                DESCRIPTION
            FROM
                OPTIONS
            WHERE
                GROUP_CODE = ? AND CODE = ?
        ";



        return self::_getMinder()->fetchOne($sql, 'DEF_RET_CC',$code_val);
*/
        $groupcode="DEF_RET_CC";   
        foreach (Minder_OtcProcess::_getMinder()->getOptionsList($groupcode) as $code => $costCentre) {
            list($companyId, $deviceId) = explode('|', $code);
            if (strtoupper($companyId) == $currentCompanyId) { // the old company defaults
                $deviceId = strtoupper(trim($deviceId));
                $deviceCostCentres[$deviceId] = $costCentre;                
            } elseif ((strtoupper($companyId) == $currentCompanyId) and 
                      (strtoupper($deviceId) == $user_whId)) { // the new wh defaults
                $deviceId = strtoupper(trim($deviceId));
                $deviceCostCentres[$deviceId] = $costCentre;                
            }  
        }

        if (isset($deviceCostCentres[$user_whId])) {

            return $deviceCostCentres[$user_whId];
       }

        return $deviceCostCentres[''] ;
    }

    /**
     * @param Minder_OtcProcess_State $state
     * @return bool
     */
    protected function _isIncompleteProcess(Minder_OtcProcess_State $state) {
        return $state->committed
               || (!$state->issueTo->existed)
               || (!$state->item->isExisted())
               || (!$state->item->doesExpirationConfirmed())
               || ($state->item->isOnLoan() && !$state->item->doesTransferConfirmed());
    }

    /**
     * @return string
     */
    protected function _getProcessId()
    {
        return static::ISSUES;
    }

    protected function _runTrolTransaction(Minder_OtcProcess_State $processState)
    {
        $processState->transactionType = 'TROL';

        $transaction           		= new Transaction_TROLA();
        $transaction->objectId 		= $processState->item->id;
        $transaction->whId     		= $processState->item->whId;
        $transaction->locnId   		= $processState->item->locnId;
        $transaction->quantity		= 1; //$qty;
        $transaction->reference		= $processState->chargeTo->id; // cost centre
        $transaction->transClass	= 'A';
        $transaction->companyId     = $processState->item->companyId;

        $processState->transactionMessage = $this->_getMinder()->doTransactionResponseV6($transaction);

        return $processState;
    }


    protected function _runTrilTransaction(Minder_OtcProcess_State $processState)
    {
        $processState->transactionType = 'TRIL';

        $transaction           		= new Transaction_TRILA();
        $transaction->objectId 		= $processState->item->id;

        if ($processState->issueTo->existed) {
            // set borrower
            $transaction->locnId = $processState->issueTo->getLocnId();
            $transaction->whId   = $processState->issueTo->getWhId();
        }

        $transaction->transClass	= 'A';
        $transaction->reference		= $processState->chargeTo->id; // cost centre
        $transaction->quantity		= 0; //$qty;
        $transaction->companyId     = $processState->item->companyId;

        $processState->transactionMessage = $this->_getMinder()->doTransactionResponseV6($transaction);

        return $processState;
    }


    protected function _runTransactionIsiz(Minder_OtcProcess_State $processState)
    {
        $processState->transactionType = 'ISIZ';

        $transaction           		= new Transaction_ISIZP();
        $transaction->objectId 		= $processState->item->id;
        $transaction->prodId        = $processState->item->id;

        if ($processState->issueTo->existed) {
            // set borrower
            $transaction->locnId = $processState->issueTo->getLocnId();
            $transaction->whId   = $processState->issueTo->getWhId();
        }

        $transaction->reference		= $processState->chargeTo->id; // cost centre
        $transaction->subLocnId		= $processState->issueTo->getLocnId(); // EMPLOYEE_ID
        $transaction->quantity		= $processState->issueQty; // quantity
        $transaction->transClass	= 'P';
        $transaction->companyId     = $processState->item->companyId;

        $processState->transactionMessage = $this->_getMinder()->doTransactionResponseV6($transaction);

        return $processState;
    }


    protected function _tryTransaction(Minder_OtcProcess_State $processState)
    {
        if ($processState->issueTo->closedLocation) {
            $processState->transactionMessage = 'Closed - Issues not allowed';
            return $processState;
        }

        if (!$processState->chargeTo->existed) {
            $processState->transactionMessage = 'Empty Cost Center';
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
            } elseif ($processState->item->isConsumable()) {
                $processState = $this->_runTransactionIsiz($processState);
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
    protected function _proceedTool(Minder_OtcProcess_State $processState) {
        $originalCostCenter = $processState->chargeTo;

        if (!$processState->chargeTo->existed) {
            $defaultCostCenter = self::getDefaultCostCenter();
            $description = $this->_getMinder()->getCostCentre($defaultCostCenter);
            $processState->chargeTo = new Minder_OtcProcess_State_CostCenter($defaultCostCenter, 'S', $description);
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
            $saveState->save_location      = $processState->issueTo->displayedId;
            $processState->save[]          = $saveState;
            // By #413
            $processState->issueTo->loanedTotal     = $this->_getMinder()->getLoanedByBorrower($processState->issueTo->getLocnId());
        }

        $processState->chargeTo = $originalCostCenter;
        return $processState;
    }

    /**
     * @param Minder_OtcProcess_State $processState
     * @return Minder_OtcProcess_State
     */
    protected function _proceedConsumable(Minder_OtcProcess_State $processState) {
        $originalCostCenter = $processState->chargeTo;

        $issueQty = (empty($processState->issueQty)) ? 1 : $processState->issueQty;

        if (!$processState->chargeTo->existed) {
            $costCenter = self::getDefaultCostCenter();
            $description = $this->_getMinder()->getCostCentre($costCenter);
            $processState->chargeTo = new Minder_OtcProcess_State_CostCenter($costCenter, 'S', $description);
        }

        $processState = $this->_tryTransaction($processState);
        if (!is_null($processState->transactionMessage)) {
            $saveState                     = new Minder_OtcProcess_SaveState();
            $saveState->transactionMessage = $processState->transactionMessage;
            $saveState->save_datetime      = $this->_getDatetime();
            $saveState->save_qty           = $issueQty;
            $saveState->save_desc          = $processState->item->description;
            $saveState->save_prod_code     = $processState->item->id;
            $saveState->save_cc            = $processState->chargeTo->id;
            $processState->save[]          = $saveState;
            // By #413
            $processState->issueTo->loanedTotal     = $this->_getMinder()->getLoanedByBorrower($processState->issueTo->getLocnId());
        }

        $processState->chargeTo = $originalCostCenter;
        return $processState;
    }

    public function setCostCenter($costCenterId, $via)
    {
        $costCenterDescription = $this->_getMinder()->getCostCentre($costCenterId);
        $processState = $this->_restoreState()->_getState();

        $costCenter = new Minder_OtcProcess_State_CostCenter($costCenterId, $via, $costCenterDescription);

        if ($processState->chargeTo->existed && $processState->chargeTo != $costCenter) {
            $processState               = $this->_commitProcess($processState);
            $processState->committed    = false;
        }

        $processState->chargeTo = $costCenter;

        if ($processState->item->isTool() && $processState->item->isExisted()) {
            $processState = $this->_commitProcess($processState);
        }

        $this->_setState($processState)->_saveState();
        return $processState;
    }

    public function setBorrower($borrowerId, $via)
    {
        $processState = $this->_restoreState()->_getState();

        $location = new Minder_OtcProcess_State_Location();

        $issueTo = $this->_getBorrowerManager()->getBorrower($borrowerId, $via);

        if ($processState->issueTo != $issueTo) {
            $processState               = $this->_commitProcess($processState);
            $processState->committed    = false;
        }

        $processState->issueTo = $issueTo;

        if ($processState->item->isTool() && $processState->item->isExisted()) {
             // this is a tool not a product !!!
             // don't commit ie run trol and tril and nicc but instead reset the ssn to not exists
             // here we have just scanned a borrower so no transactions
            $processState->item->existed = False;
            //$processState = $this->_commitProcess($processState);
        }

        $this->_setState($processState)->_saveState();
        return $processState;
    }

    protected function _setTool(Minder_OtcProcess_State_Tool $tool)
    {
        $processState = $this->_restoreState()->_getState();

        if ($processState->item != $tool) {
            $processState               = $this->_commitProcess($processState);
            $processState->committed    = false;
        }

        $processState->setItem($tool);
        $processState->issueQty = 1;
        $processState->issueQtyDescription = 'EA';
        $processState->issueQtyVia = 'S';
        $processState           = $this->_commitProcess($processState);

        $this->_setState($processState)->_saveState();
        return $processState;
    }

    public function setConsumable($itemId, $via)
    {
        $processState = $this->_restoreState()->_getState();

        $consumable = $this->_getConsumableManager()->getConsumable($itemId, $via);

        if ($processState->committed || $processState->item != $consumable) {
            $processState                       = $this->_commitProcess($processState);
            $processState->committed            = false;
            $processState->issueQty             = $consumable->defaultIssueQty;
            $processState->issueQtyDescription  = $consumable->defaultIssueUom;
        } else {
            $processState->issueQty             += $consumable->defaultIssueQty;
        }

        $processState->setItem($consumable);

        $this->_setState($processState)->_saveState();
        return $processState;
    }

    public function setIssueQty($qty, $via)
    {
        $processState = $this->_restoreState()->_getState();
        $processState->issueQty = $qty;
        $processState->issueQtyDescription = empty($processState->issueQtyDescription) ? 'EA' : $processState->issueQtyDescription;
        $processState->issueQtyVia = $via;

        $this->_setState($processState)->_saveState();
        return $processState;
    }

    public function setLocation($location, $via)
    {
        $locn = $this->_getMinder()->getLocn(substr($location, 2), substr($location, 0, 2));

        $processState = $this->_restoreState()->_getState();

        $newLocation = new Minder_OtcProcess_State_Location($location, $via, $locn);

        if ($processState->issueTo != $newLocation) {
            $processState               = $this->_commitProcess($processState);
            $processState->committed    = false;
        }

        $processState->issueTo = $newLocation;

        if ($this->_isCompleteProcess($processState)) {
            $processState = $this->_proceedTool($processState);
        }

        $this->_setState($processState)->_saveState();
        return $processState;
    }

    public function confirmExpiration()
    {
        $processState = $this->_restoreState()->_getState();
        $processState->item->confirmExpiration();
        $processState = $this->_commitProcess($processState);
        $this->_setState($processState)->_saveState();
        return $processState;
    }

    public function confirmToolTransfer()
    {
        $processState = $this->_restoreState()->_getState();
        $processState->item->confirmTransfer();
        $processState = $this->_commitProcess($processState);
        $this->_setState($processState)->_saveState();
        return $processState;
    }

}
