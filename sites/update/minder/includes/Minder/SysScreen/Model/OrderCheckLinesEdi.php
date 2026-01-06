<?php

class Minder_SysScreen_Model_OrderCheckLinesEdi extends Minder_SysScreen_Model {
    const ORDER_SELECTION_LIMIT = 'ORDER_SELECTION_CONDITION';
    const OUT_SSCC_LIMIT = 'OUT_SSCC_LIMIT';


    public function setOrderSelectionLimit(Minder_PickOrder_Collection $orders) {
        $conditionObject = $this->getConditionObject();
        $conditionObject->deleteConditions(static::ORDER_SELECTION_LIMIT);

        if (count($orders) > 0) {
            $conditionObject->addConditions(array(
                "PACK_SSCC.PS_PICK_ORDER IN (" . implode(', ', array_fill(0, count($orders), '?')) . ")" => $orders->PICK_ORDER
            ), static::ORDER_SELECTION_LIMIT);
        } else {
            $conditionObject->addConditions(array('1 = 2' => array()), static::ORDER_SELECTION_LIMIT);
        }

        $this->setConditionObject($conditionObject);
    }

    public function setOutSsccLimit($psOutSscc) {
        $conditionObject = $this->getConditionObject();
        $conditionObject->deleteConditions(static::OUT_SSCC_LIMIT);

        if (empty($psOutSscc)) {
            $conditionObject->addConditions(array('1 = 2' => array()), static::OUT_SSCC_LIMIT);
        } else {
            $conditionObject->addConditions(array("PACK_SSCC.PS_OUT_SSCC = ?" => array($psOutSscc)), static::OUT_SSCC_LIMIT);
        }

        $this->setConditionObject($conditionObject);
    }

    public function selectRecordIdMap($totalRows) {
        return $this->selectArbitraryExpression(0, $totalRows, $this->getPrimaryIdExpression() . " AS ROW_ID, PACK_SSCC.RECORD_ID");
    }
}