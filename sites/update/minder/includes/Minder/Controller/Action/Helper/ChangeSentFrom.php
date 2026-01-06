<?php

class Minder_Controller_Action_Helper_ChangeSentFrom extends Zend_Controller_Action_Helper_Abstract {

    public function changeSentFrom($selectedDespatches, $newCarrierServiceRecordId, Minder_JSResponse $result = null) {
        $result = $this->_validateData($selectedDespatches, $newCarrierServiceRecordId, $result);

        if (!$result->hasErrors()) {

            try {
                $result = $this->_changeSentFrom($selectedDespatches, $newCarrierServiceRecordId, $result);
            } catch (Exception $e) {
                $result->addErrors($e->getMessage());
            }
        }

        return $result;
    }

    protected function _changeSentFrom($selectedDespatches, $newCarrierServiceRecordId, Minder_JSResponse $result) {
        $carrierService = $this->_getCarrierService($newCarrierServiceRecordId);
        $updatedAmount = 0;

        foreach ($selectedDespatches as $despatch) {
            $pickOrder = $this->_getOrderFromDespatch($despatch);
            $transaction = new Transaction_DSUDT($despatch, $carrierService, $pickOrder);
            $this->_getMinder()->doTransactionResponseV6($transaction);
            $updatedAmount++;
        }

        $result->addMessages($updatedAmount . ' records was updated.');

        return $result;
    }

    protected function _validateData($selectedDespatches, $newCarrierServiceRecordId, Minder_JSResponse $result = null) {
        $result = $result ? $result : new Minder_JSResponse();
        $carrierId = null;

        if (count($selectedDespatches) < 1) {
            $result->addErrors('No rows selected. Please select one.');
        } else {
            $carriers = array_unique(Minder_ArrayUtils::mapField($selectedDespatches, 'PICKD_CARRIER_ID'));

            if (count($carriers) > 1) {
                $result->addErrors('Cannot change Sent From details for more then one carrier.');
            } else if (count($carriers) > 0) {
                $carrierId = array_shift($carriers);
            }
        }

        if (empty($newCarrierServiceRecordId)) {
            $result->addErrors('No Carrier Service selected.');
        } else {
            if (!empty($carrierId)) {
                $carrierServices = Minder_ArrayUtils::mapField($this->_getMinder()->getCarrierServiceTypes($carrierId), 'RECORD_ID');
                if (!in_array($newCarrierServiceRecordId, $carrierServices)) {
                    $result->addErrors('Selected Carrier Service does not belong to selected carrier.');
                }
            }
        }

        return $result;
    }

    protected function _getMinder() {
        return Minder::getInstance();
    }

    protected function _getCarrierService($recordId) {
        return $this->_getMinder()->fetchAssoc('SELECT FIRST 1 * FROM CARRIER_SERVICE WHERE RECORD_ID = ?', $recordId);
    }

    protected function _getOrderFromDespatch($despatch) {
        $orderData = $this->_getMinder()->fetchAssoc('
            SELECT FIRST 1
                PICK_ORDER.*
            FROM
                PICK_ITEM_DETAIL
                LEFT JOIN PICK_ORDER ON PICK_ITEM_DETAIL.PICK_ORDER = PICK_ORDER.PICK_ORDER
            WHERE
                PICK_ITEM_DETAIL.DESPATCH_ID = ?
        ', $despatch['DESPATCH_ID']);

        $orderData = array_change_key_case($orderData, CASE_LOWER);

        $pickOrder = new PickOrder();
        $pickOrder->save($orderData);

        return $pickOrder;
    }
}