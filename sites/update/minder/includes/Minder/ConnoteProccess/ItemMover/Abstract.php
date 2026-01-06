<?php

/**
 * @throws Minder_ConnoteProccess_Exception
 */
abstract class Minder_ConnoteProccess_ItemMover_Abstract {

    /**
     * @return array
     */
    abstract protected function _selectItemsWithWrongDetailsStatus();
    abstract protected function _doMove($whId, $locnId);

    /**
     * @return array
     */
    abstract protected function _getMovedItems();

    public function moveItemsToDespatchLocation() {
        list($whId, $locnId) = $this->_getMinder()->getDeviceWhAndLocation();

        $this->_validateDeviceLocation($whId, $locnId);
        $this->_validatePickItemDetails();

        $this->_doMove($whId, $locnId);
    }

    public function moveItemsToOriginalLocation() {
        $transaction         = new Transaction_PKUBB();

        $servedOrders = array();

        foreach ($this->_getMovedItems() as $item) {
            if (isset($servedOrders[$item['PICK_ORDER']]))
                continue;

            $transaction->whId = $this->_getMinder()->whId;
            $transaction->locnId = $item['ITEM_ORIGINAL_LOCN_ID'];
            $transaction->pickOrder = $item['PICK_ORDER'];

            if (false === $this->_getMinder()->doTransactionResponse($transaction)) {
                throw new Minder_Exception('Errors executing transaction ' . $transaction->transCode . $transaction->transClass . ': ' . $this->_getMinder()->lastError);
            }

            $servedOrders[$transaction->pickOrder] = $transaction->pickOrder;
        }
    }

    /**
     * @return Minder
     */
    protected function _getMinder()
    {
        return Minder::getInstance();
    }

    /**
     * @param $whId
     * @param $locnId
     * @throws Minder_ConnoteProccess_Exception
     */
    protected function _validateDeviceLocation($whId, $locnId)
    {
        if (!$this->_getMinder()->isDespatchLocation($whId, $locnId))
            throw new Minder_ConnoteProccess_Exception('Current device is not defined as despatch location.');
    }

    /**
     * @throws Minder_ConnoteProccess_Exception
     */
    protected function _validatePickItemDetails()
    {
        $badItems = $this->_selectItemsWithWrongDetailsStatus();
        if (count($badItems) > 0) {
            throw new Minder_ConnoteProccess_Exception('Cannot move Items (' . implode(', ', $badItems) . ') to despatch location: not Item Details found.');
        }
    }


}
