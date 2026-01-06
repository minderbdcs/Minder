<?php

abstract class Minder_AddPickItemRoutine_Strategy_Abstract implements Minder_AddPickItemRoutine_Strategy_Interface {

    /**
     * @param Minder_AddPickItemRoutine_State $state
     * @param array $stockInfoArray
     * @return boolean
     */
    protected function _validateStockInfo(&$state, &$stockInfoArray) {
        /**
         * @var Minder_AddPickItemRoutine_ItemProvider_StockInfo $stockInfo
         */
        foreach ($stockInfoArray as $index => $stockInfo) {
            if (!$stockInfo->itemFound) {
                $state->errors[] = $state->itemProvider->formatItemNotFoundMessage($stockInfo->itemId);
                unset($stockInfoArray[$index]);
            }
        }

        if (empty($stockInfoArray))
            $state->type = Minder_AddPickItemRoutine_State::STATE_ITEM_NOT_FOUND;

        return $state->type !== Minder_AddPickItemRoutine_State::STATE_ITEM_NOT_FOUND;
    }

    /**
     * @param Minder_AddPickItemRoutine_State $state
     * @param array $stockInfoArray
     * @return bool
     */
    protected function _isStockEnough(&$state, $stockInfoArray) {
        $state->stockAmount = 0;

        /**
         * @var Minder_AddPickItemRoutine_ItemProvider_StockInfo $stockInfo
         */
        foreach ($stockInfoArray as $stockInfo) {
            $state->stockAmount += $stockInfo->availableQty;
        }

        if ($state->stockAmount < $state->request->requiredForOrder) {
            $state->type = Minder_AddPickItemRoutine_State::STATE_NO_STOCK;
            return false;
        }

        return true;
    }

    /**
     * @param Minder_AddPickItemRoutine_State $state
     * @param array $stockInfoArray
     * @return Minder_AddPickItemRoutine_State
     */
    protected function _forceAddAvailable($state, $stockInfoArray = null) {
        $state->request->toAddAmount = $state->stockAmount;
        $state->itemProvider->addPickItem($state->request, $stockInfoArray);
        $state->type = Minder_AddPickItemRoutine_State::STATE_ITEM_ADDED;
        return $state;
    }

    /**
     * @param Minder_AddPickItemRoutine_State $state
     * @param array $stockInfoArray
     * @return Minder_AddPickItemRoutine_State
     */
    abstract protected function _addWhenNotEnoughStock($state, $stockInfoArray = null);

    /**
     * @param Minder_AddPickItemRoutine_State $state
     * @return Minder_AddPickItemRoutine_State
     */
    function addPickItem($state)
    {
        try {
            $stockInfoArray = $state->itemProvider->getStockInfo($state->request);

            if (!$this->_validateStockInfo($state, $stockInfoArray))
                return $state;

            if (!$this->_isStockEnough($state, $stockInfoArray))
                return $this->_addWhenNotEnoughStock($state);

            $state->request->toAddAmount = $state->request->requiredForOrder;
            $state->itemProvider->addPickItem($state->request, $stockInfoArray);
            $state->type = Minder_AddPickItemRoutine_State::STATE_ITEM_ADDED;
        } catch (Exception $e) {
            $state->errors[] = $e->getMessage();
            $state->type = Minder_AddPickItemRoutine_State::STATE_ERROR;
        }

        return $state;
    }
}