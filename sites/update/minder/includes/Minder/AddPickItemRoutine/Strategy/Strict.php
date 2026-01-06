<?php

class Minder_AddPickItemRoutine_Strategy_Strict extends Minder_AddPickItemRoutine_Strategy_Abstract {

    /**
     * @param Minder_AddPickItemRoutine_State $state
     * @param array $stockInfoArray
     * @return Minder_AddPickItemRoutine_State
     */
    protected function _addWhenNotEnoughStock($state, $stockInfoArray = null) {
        if ($state->request->addMode == Minder_AddPickItemRoutine_Request::ADD_MODE_FORCE_AVAILABLE)
            return $this->_forceAddAvailable($state, $stockInfoArray);

        return $state;
    }
}