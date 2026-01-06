<?php

class Minder_SysScreen_Model_AwaitingExit extends Minder_SysScreen_Model {
    protected function _makeConditionForPickedExit($fieldDescription) {
        switch ($fieldDescription['SSV_ALIAS']) {
            case 'PICKD_EXIT_FROM':
                $conditionString = $this->__getFieldExpression($fieldDescription) . ' >= ZEROTIME(?)';
                $conditionArgs   = array($fieldDescription['SEARCH_VALUE']);
                break;
            case 'PICKD_EXIT_TILL':
                $conditionString = $this->__getFieldExpression($fieldDescription) . ' <= MAXTIME(?)';
                $conditionArgs   = array($fieldDescription['SEARCH_VALUE']);
                break;
            default:
                return parent::makeConditionsFromSearchField($fieldDescription);
        }
        return array($conditionString, $conditionArgs, 'CONDITION_STRING' => $conditionString, 'CONDITION_ARGS' => $conditionArgs);
    }

    protected function makeConditionsFromSearchField($fieldDescription)
    {
        $conditionString = '';
        $conditionArgs   = array();

        if (!empty($fieldDescription['SEARCH_VALUE'])) {
            switch ($fieldDescription['SSV_NAME']) {
                case 'PICKD_EXIT':
                    return $this->_makeConditionForPickedExit($fieldDescription);

                default:
                    return parent::makeConditionsFromSearchField($fieldDescription);
            }
        }

        return array($conditionString, $conditionArgs, 'CONDITION_STRING' => $conditionString, 'CONDITION_ARGS' => $conditionArgs);
    }

    /**
     * @param Minder_Printer_Abstract $printer
     * @throws Minder_Exception
     * @return Minder_JSResponse
     */
    public function printLabel($printer) {
        $printResult = new Minder_JSResponse();
        $tempResult  = new Minder_JSResponse();

        $rowsAmount = count($this);
        if ($rowsAmount < 1) {
            $tempResult->warnings[] = 'Now rows to print.';
            return $tempResult;
        }

        $printedLabelsAmount = 0;
        foreach ($this->selectArbitraryExpression(0, $rowsAmount, 'DISTINCT PICK_DESPATCH.DESPATCH_ID') as $despatch) {

            if (false === ($labelData = $this->getDespatchLabelData($despatch['DESPATCH_ID']))) {
                throw new Minder_Exception('Despatch #' . $despatch['DESPATCH_ID'] . ' does not exists.');
            }

            if (count($labelData) < 1) {
                throw new Minder_Exception('Despatch #' . $despatch['DESPATCH_ID'] . ' does not exists.');
            }

            $printTools = new Minder_PackIdPrintTools();
            $tempResult = $printTools->printLabels($despatch['DESPATCH_ID'], $printer);

            $printResult->messages[$printedLabelsAmount] = $tempResult->messages;

            $printedLabelsAmount++;
        }

        return $printResult;
    }

    public function getDespatchLabelData($despatchId)
    {
        $tmpFilters  = $this->_buildExpressionLimit('DESPATCH_ID', $despatchId);

        $sql  = 'SELECT * FROM PICK_DESPATCH WHERE (' . implode(' AND ', array_keys($tmpFilters)) . ')';
        $args = array_reduce(array_values($tmpFilters), array($this, '_reduceHelper'), null);

        array_unshift($args, $sql);

        return call_user_func_array(array(Minder::getInstance(), 'fetchAllAssocExt'), $args);
    }

    public function getDespatches($rowsAmount = null) {
        $rowsAmount = is_null($rowsAmount) ? count($this) : $rowsAmount;

        if ($rowsAmount < 1) {
            return array();
        }

        $despatches = $this->selectArbitraryExpression(0, $rowsAmount, 'DISTINCT PICK_DESPATCH.DESPATCH_ID');

        $sql  = 'SELECT * FROM PICK_DESPATCH WHERE DESPATCH_ID IN (' . substr(str_repeat('?, ', count($despatches)), 0, -2) . ')';

        $args = Minder_ArrayUtils::mapField($despatches, 'DESPATCH_ID');

        array_unshift($args, $sql);

        return call_user_func_array(array(Minder::getInstance(), 'fetchAllAssoc'), $args);
    }

    protected function _isAbleToChangeDepot(Depot $carrierDepot, $despatches) {
        $result = new Minder_JSResponse();

        if (count($despatches) < 1) {
            $result->addErrors('No rows selected.');
        }

        $carriers = array_unique(Minder_ArrayUtils::mapField($despatches, 'PICKD_CARRIER_ID'));

        if (count($carriers) > 1) {
            $result->addErrors('Cannot update multiply carriers at once.');
        } else{
            $despatchesCarrier = current($carriers);
            if ($despatchesCarrier !== $carrierDepot->CD_CARRIER_ID) {
                $result->addErrors('Selected carrier "' . $carrierDepot->CD_CARRIER_ID . '" does not match to Despatches carrier "' . $despatchesCarrier . '"');
            }
        }

        $states = array_unique(Minder_ArrayUtils::mapField($despatches, 'PICKD_STATE'));

        if (count($states) > 1) {
            $result->addErrors('Cannot update despatches from multiply states at once.');
        }

        return $result;
    }

    protected function _doChangeDepot(Depot $carrierDepot, $despatches, Minder_JSResponse $result = null) {
        $result = is_null($result) ? new Minder_JSResponse() : $result;

        $executed = 0;

        foreach ($despatches as $despatch) {
            $transaction = new Transaction_DSUSZ(
                $despatch['AWB_CONSIGNMENT_NO'],
                $despatch['RECEIVER_ACCOUNT'],
                $despatch['DESPATCH_ID'],
                $despatch['PICKD_CARRIER_ID'],
                $carrierDepot->RECORD_ID
            );

            try {
                $this->_getMinder()->doTransactionResponseV6($transaction);
                $executed++;
            } catch (Exception $e) {
                $result->addErrors('Error executing DSUS Z transaction for DESPATCH #' . $despatch['DESPATCH_ID'] . ': ' . $e->getMessage());
            }
        }

        if ($executed > 0) {
            $result->addMessages($executed . ' DSUS Z transaction(s) executed.');
        }

        return $result;
    }

    public function changeDepot($carrierDepotId) {
        $carrierDepot = $this->_getCarrierDepotManager()->find($carrierDepotId);
        $despatches = $this->getDespatches();

        $validationResults = $this->_isAbleToChangeDepot($carrierDepot, $despatches);

        if ($validationResults->hasErrors()) {
            return $validationResults;
        }

        return $this->_doChangeDepot($carrierDepot, $despatches, $validationResults);
    }

    protected function _getCarrierDepotManager() {
        return new DepotManager();
    }
}