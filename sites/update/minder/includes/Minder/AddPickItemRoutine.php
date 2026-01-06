<?php

class Minder_AddPickItemRoutine {
    /**
     * @return Minder_AddPickItemRoutine_Strategy_Interface
     */
    protected function _getAddStrategy() {
        switch (strtoupper(Minder::getInstance()->defaultControlValues['CONFIRM_WITH_NO_PROD'])) {
            case 'T':
                return new Minder_AddPickItemRoutine_Strategy_ConfirmWithNoProd();
            default:
                return new Minder_AddPickItemRoutine_Strategy_Strict();
        }
    }

    /**
     * @param Minder_AddPickItemRoutine_Request $addRequest
     * @return Minder_AddPickItemRoutine_State
     */
    protected function _validateRequest($addRequest) {
        $minder    = Minder::getInstance();
        $pickOrder = $minder->getPickOrder($addRequest->orderNo);
        $person    = $minder->getPerson($pickOrder->personId , false, 'OF');
        $state     = new Minder_AddPickItemRoutine_State();

        if (!is_null($person)) {
            if ($person->status != 'CU' and $person->status != 'OB') {
                $state->type = Minder_AddPickItemRoutine_State::STATE_ERROR;
                $state->errors[] = 'Cannot Add Pick Item to Sales Order with Non Current Person.';
            }
        }

        return $state;
    }

    /**
     * @param Minder_AddPickItemRoutine_Request $addRequest
     * @param Minder_AddPickItemRoutine_ItemProvider_Interface $itemProvider
     * @return Minder_AddPickItemRoutine_State
     */
    public function addPickItem($addRequest, $itemProvider) {
        $state = $this->_validateRequest($addRequest);

        if ($state->type === Minder_AddPickItemRoutine_State::STATE_ERROR)
            return $state;

        $state->request = $addRequest;
        $state->itemProvider = $itemProvider;
        $state->addStrategy  = $this->_getAddStrategy();

        $state = $state->addStrategy->addPickItem($state);

        if ($state->type == Minder_AddPickItemRoutine_State::STATE_ITEM_ADDED) {
            Minder::getInstance()->pickOrderRecalculate($addRequest->orderNo);
        }

        return $state;
    }
}