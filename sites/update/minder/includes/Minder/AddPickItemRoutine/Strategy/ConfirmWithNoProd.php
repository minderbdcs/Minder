<?php

class Minder_AddPickItemRoutine_Strategy_ConfirmWithNoProd extends Minder_AddPickItemRoutine_Strategy_Abstract {

    /**
     * @param Minder_AddPickItemRoutine_State $state
     * @param array $stockInfoArray
     * @return Minder_AddPickItemRoutine_State
     */
    protected function _forceAddRequested($state, $stockInfoArray = null) {
        $stockInfoArray = (is_null($stockInfoArray)) ? $state->itemProvider->getStockInfo($state->request) : $stockInfoArray;

        /**
         * @var Minder_AddPickItemRoutine_ItemProvider_StockInfo $lastItem
         */
        $lastItem = array_pop($stockInfoArray);
        $lastItem->availableQty = $state->request->toAddAmount = $state->request->requiredForOrder;
        array_push($stockInfoArray, $lastItem);

        $state->itemProvider->addPickItem($state->request, $stockInfoArray);
        $state->type = Minder_AddPickItemRoutine_State::STATE_ITEM_ADDED;
        return $state;
    }

    /**
     * @param Minder_AddPickItemRoutine_State $state
     * @param array $stockInfoArray
     * @return Minder_AddPickItemRoutine_State
     */
    protected function _addWhenNotEnoughStock($state, $stockInfoArray = null) {
        switch ($state->request->addMode) {
            case Minder_AddPickItemRoutine_Request::ADD_MODE_FORCE_AVAILABLE:
                return $this->_forceAddAvailable($state, $stockInfoArray);
            case Minder_AddPickItemRoutine_Request::ADD_MODE_FORCE_REQUESTED:
                return $this->_forceAddRequested($state, $stockInfoArray);
        }

        return $state;
    }
}