<?php

abstract class Minder_OtcProcess {
    const OVERDUE = 'OD';
    const RETURNS = 'RETURNS';
    const ISSUES = 'ISSUES';
    const AUDIT = 'AUDIT';

    /**
     * @var Minder_OtcProcess_Manager_Borrower
     */
    protected $_borrowerManager;

    /**
     * @var Minder_OtcProcess_Manager_Tool
     */
    protected $_toolManager;

    /**
     * @var Minder_OtcProcess_Manager_Consumable
     */
    protected $_consumableManager;

    /**
     * @static
     * @return false|null|string
     */
    public static function getDefaultReturnLocation()
    {
        $deviceLocations = array();
        $currentCompanyId = strtoupper(self::_getMinder()->defaultControlValues['COMPANY_ID']);

/*-> edited 4 Aug 2016->*/
  
        $userid_is=Minder_OtcProcess::_getMinder()->userId;
        $user_whId = Minder_OtcProcess::_getMinder()->getSysEquipData($userid_is);

        if($user_whId==""){
        $user_whId=strtoupper(self::_getMinder()->defaultControlValues['DEFAULT_WH_ID']);
        }


        $user_data = Minder_OtcProcess::_getMinder()->getSysUserData($userid_is);
        $user_companyId=$user_data['COMPANY_ID'];
        
  $groupcode="DEF_RET_LO";   
    $code_val=$user_companyId."|".$user_whId;
        $wh_company=$user_whId.'|'.$currentCompanyId;


        //foreach (Minder_OtcProcess::_getMinder()->getOptionsList($groupcode,$code_val) as $code => $location) {
        foreach (Minder_OtcProcess::_getMinder()->getOptionsList($groupcode) as $code => $location) {


            list($companyId, $deviceId) = explode('|', $code);


            if (strtoupper($companyId) == $currentCompanyId) { // the old company defaults
            
                        $deviceId = strtoupper(trim($deviceId));
               
                $deviceLocations[$deviceId] = $location;                
             
            } elseif ((strtoupper($companyId) == $currentCompanyId) and 
                      (strtoupper($deviceId) == $user_whId)) { // the new wh defaults
                $deviceId = strtoupper(trim($deviceId));
               
                $deviceLocations[$deviceId] = $location;                
             
            }  


        }

        //if (isset($deviceLocations[self::_getMinder()->deviceId])) {
        //    return $deviceLocations[self::_getMinder()->deviceId];
        //}
        if (isset($deviceLocations[$user_whId])) {


            return $deviceLocations[$user_whId];
       }

       

        return isset($deviceLocations['']) ? $deviceLocations[''] : '';
    }

    /**
     * @return Minder
     */
    static protected function _getMinder() {
        return Minder::getInstance();
    }

    /**
     * @var Minder_OtcProcess_State
     */
    protected $_state = null;

    /**
     * @var Zend_Session_Namespace
     */
    protected $_session = null;

    /**
     * @param $processId
     * @return Minder_OtcProcess_Audit|Minder_OtcProcess_Issue|Minder_OtcProcess_Return
     * @throws Exception
     */
    public static function getOtcProcessObject($processId) {
        switch (strtoupper($processId)) {
            case Minder_OtcProcess::ISSUES:
                return new Minder_OtcProcess_Issue();
            case Minder_OtcProcess::RETURNS:
                return new Minder_OtcProcess_Return();
            case Minder_OtcProcess::AUDIT:
                return new Minder_OtcProcess_Audit();
            default:
                throw new Exception('Unsupported process id: "' . $processId . '"');
        }
    }

    /**
     * @return Minder_OtcProcess_State
     */
    protected function _getState() {
        if (is_null($this->_state)) {
            $this->_state = new Minder_OtcProcess_State();
            $this->_state->processId = $this->_getProcessId();
        }

        return $this->_state;
    }

    /**
     * @param Minder_OtcProcess_State|null $state
     * @return Minder_OtcProcess
     */
    protected function _setState(Minder_OtcProcess_State $state = null) {
        $this->_state = $state;
        return $this;
    }

    /**
     * @return Zend_Session_Namespace
     */
    protected function _getSession() {
        if (is_null($this->_session))
            $this->_session = new Zend_Session_Namespace('OTC_ISSUE_PROCCESS');

        return $this->_session;
    }

    /**
     * @abstract
     * @return string
     */
    abstract protected function _getProcessId();

    /**
     * @return Minder_OtcProcess
     */
    protected function _saveState() {
        $session = $this->_getSession();

        $states = (isset($session->processState) && is_array($session->processState)) ? $session->processState : array();
        $states[$this->_getProcessId()] = $this->_getState();

        $session->processState = $states;
        return $this;
    }

    /**
     * @return Minder_OtcProcess
     */
    protected function _restoreState() {
        $session = $this->_getSession();

        if (isset($session->processState) && isset($session->processState[$this->_getProcessId()])) {
            /**
             * @var Minder_OtcProcess_State $tmpState
             */
            $tmpState = $session->processState[$this->_getProcessId()];
            $tmpState->doCleanup();
        } else {
            $tmpState = new Minder_OtcProcess_State();
        }
        $tmpState->processId = $this->_getProcessId();
        $this->_setState($tmpState);

        return $this;
    }

    protected function _getDatetime() {

    	return date('Y-m-d H:i:s');
    }

    /**
     * @param Minder_OtcProcess_State $processState
     * @return Minder_OtcProcess_State
     */
    protected function _proceedTool(Minder_OtcProcess_State $processState) {
        return $processState;
    }

    /**
     * @param Minder_OtcProcess_State $processState
     * @return Minder_OtcProcess_State
     */
    protected function _proceedConsumable(Minder_OtcProcess_State $processState) {
        return $processState;
    }

    /**
     * @abstract
     * @param Minder_OtcProcess_State $state
     * @return bool
     */
    abstract protected function _isIncompleteProcess(Minder_OtcProcess_State $state);

    /**
     * @param Minder_OtcProcess_State $state
     * @return bool
     */
    protected function _isCompleteProcess(Minder_OtcProcess_State $state) {
        return !$this->_isIncompleteProcess($state);
    }

    /**
     * @param Minder_OtcProcess_State $processState
     * @return Minder_OtcProcess_State
     */
    protected function _commitProcess(Minder_OtcProcess_State $processState) {
        if ($this->_isCompleteProcess($processState)) {
            if ($processState->item->isTool())
                $processState = $this->_proceedTool($processState);

            if ($processState->item->isConsumable())
                $processState = $this->_proceedConsumable($processState);
        }

        return $processState;
    }

    /**
     * @return Minder_OtcProcess_State
     */
    public function resetProcess() {
        return $this->_setState()->_saveState()->_getState();
    }

    /**
     * @return Minder_OtcProcess_State
     */
    public function recordHome() {
        return $this->_setState()->_saveState()->_getState();
    }

    /**
     * @return Minder_OtcProcess_State
     */
    public function save() {
        $processState = $this->_restoreState()->_getState();
        $processState = $this->_commitProcess($processState);
        $this->_setState($processState)->_saveState();
        return $processState;
    }

    /**
     * @param string $costCenterId
     * @param string $via
     * @return Minder_OtcProcess_State
     */
    public function setCostCenter($costCenterId, $via) {
        $state = $this->_restoreState()->getState();

        $state->chargeTo   = new Minder_OtcProcess_State_CostCenter($costCenterId, $via);

        $this->_setState($state)->_saveState();

        return $state;
    }

    /**
     * @param string $borrowerId
     * @param string $via
     * @return Minder_OtcProcess_State
     */
    public function setBorrower($borrowerId, $via) {
        return $this->_restoreState()->_getState();
    }

    /**
     * @param string $itemId
     * @param string $via
     * @return Minder_OtcProcess_State
     */
    public function setToolBarcode($itemId, $via) {
        return $this->_setTool($this->_getToolManager()->getTool($itemId, $via));
    }

    public function setToolAltBarcode($itemId, $via) {
        return $this->_setTool($this->_getToolManager()->getToolByLegacyId($itemId, $via));
    }

    public function reloadTool() {
        $processState = $this->_restoreState()->_getState();

        if ($processState->item->isTool()) {
            $processState->setItem($this->_getToolManager()->reloadTool($processState->item), self::getDefaultReturnLocation());
        }

        $this->_setState($processState)->_saveState();

        return $processState;
    }

    abstract protected function _setTool(Minder_OtcProcess_State_Tool $tool);

    /**
     * @param string $itemId
     * @param string $via
     * @return Minder_OtcProcess_State
     */
    public function setConsumable($itemId, $via) {
        $proccessState = $this->_restoreState()->_getState();
        $proccessState->setItem(new Minder_OtcProcess_State_Consumable($itemId, $via));

        $this->_setState($proccessState)->_saveState();

        return $proccessState;
    }

    /**
     * @param int $qty
     * @param string $via
     * @return Minder_OtcProcess_State
     */
    public function setIssueQty($qty, $via) {
        return $this->_restoreState()->_getState();
    }

    /**
     * @param string $location
     * @param string $via
     * @return Minder_OtcProcess_State
     */
    public function setLocation($location, $via) {
        return $this->_restoreState()->_getState();
    }

    /**
     * @return Minder_OtcProcess_State
     */
    public function getState() {
        return $this->_restoreState()->_getState();
    }

    public function confirmExpiration() {
        return $this->_restoreState()->_getState();
    }

    public function confirmToolTransfer() {
        return $this->_restoreState()->_getState();
    }

    public function endAudit() {
        return $this->_restoreState()->_getState();
    }

    public function executeToolTransaction($descriptionLabel) {
        $processState = $this->_restoreState()->_getState();

        $processState->toolTransaction = $this->_getToolTransactionManager()->getToolTransaction($descriptionLabel);

        if (!$processState->item->isTool()) {
            $processState->toolTransaction->setError('Scan Tool label first');
        } else {
            if (!$processState->toolTransaction->error) {
                try {
                    $processState = $this->_runToolTransaction($processState);
                } catch (Exception $e) {
                    $processState->toolTransaction->setError($e->getMessage());
                }

                $processState->setItem($this->_getToolManager()->reloadTool($processState->item), self::getDefaultReturnLocation());
            }
        }

        $this->_setState($processState)->_saveState();

        return $processState;
    }

    protected function _runNiccTransaction(Minder_OtcProcess_State $processState)
    {
        $processState->transactionType = 'NICC';
        /**
         * @var Minder_OtcProcess_State_Tool $tool
         */
        $tool = $processState->item;
        $whId = $tool->whId . str_repeat(' ', 2 - strlen($tool->whId));

        $transaction = new Transaction_NICCA($tool->id, $processState->chargeTo->id, $whId . $tool->locnId, $tool->currentQty);
        $transaction->companyId = $tool->companyId;

        $processState->transactionMessage = $this->_getMinder()->doTransactionResponseV6($transaction);

        return $processState;

    }

    /**
     * @return Minder_OtcProcess_Manager_Borrower
     */
    protected function _getBorrowerManager()
    {
        if (empty($this->_borrowerManager)) {
            $this->_borrowerManager = new Minder_OtcProcess_Manager_Borrower();
        }

        return $this->_borrowerManager;
    }

    protected function _getToolManager()
    {
        if (empty($this->_toolManager)) {
            $this->_toolManager = new Minder_OtcProcess_Manager_Tool();
        }

        return $this->_toolManager;
    }

    protected function _getConsumableManager() {
        if (empty($this->_consumableManager)) {
            $this->_consumableManager = new Minder_OtcProcess_Manager_Consumable();
        }

        return $this->_consumableManager;
    }

    protected function _getToolTransactionManager()
    {
        return new Minder_OtcProcess_Manager_ToolTransaction();
    }

    protected function _runToolTransaction(Minder_OtcProcess_State $processState)
    {
        $transaction = new Minder_OtcProcess_ToolTransaction($processState);
        $transactionResponse = $this->_getMinder()->doTransactionResponseV6($transaction);

        if (stripos($transactionResponse, 'success')) {
            $processState->toolTransaction->setMessage($transactionResponse);
        } else {
            $processState->toolTransaction->setError($transactionResponse);
        }

        return $processState;
    }
}
