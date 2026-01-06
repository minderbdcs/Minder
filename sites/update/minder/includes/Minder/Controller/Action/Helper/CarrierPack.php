<?php

class Minder_Controller_Action_Helper_CarrierPack extends Zend_Controller_Action_Helper_Abstract {
    public function doDespatch($despatchLabelNo, $carrierId, Minder_JSResponse_ExitCarrier $response = null) {
        $response = is_null($response) ? new Minder_JSResponse_ExitCarrier() : $response;

        try {
            $packId = $this->_getPackIdManager()->findByLabel($despatchLabelNo);
            $pickDespatch = $this->_getPickDespatchManager()->findByPackId($packId);
            $response->pickedCarrierId = $pickDespatch->PICKD_CARRIER_ID;

            if ($pickDespatch->PICKD_CARRIER_ID !== $carrierId) {
                throw new Exception('Carriers does not match.');
            }

            if (empty($pickDespatch->PICKD_EXIT)) {
                $transaction = new Transaction_DSDXL();

                $transaction->reference = $pickDespatch->AWB_CONSIGNMENT_NO;
                $transaction->qty       = $pickDespatch->PICKD_ADDRESS_QTY;

                $response->messages[] = Minder::getInstance()->doTransactionResponseV6($transaction);
            } else {
                $response->messages[] = 'Already despatched';
            }
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }

        return $response;
    }

    public function fillCarrierPackStatistics($carriers, Minder_JSResponse_ExitCarrier $response) {
        try {
            $response->carriersStatistics = $this->_getPackIdManager()->getPackAmountPerCarrier($carriers);
        } catch (Exception $e) {
            $response->warnings[] = $e->getMessage();
        }

        return $response;
    }

    protected function _getPackIdManager() {
        return new PackId_Manager();
    }

    protected function _getPickDespatchManager() {
        return new PickDespatch_Manager();
    }

}