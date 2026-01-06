<?php

class Minder_ConnoteProccess_DespatchPolicy_Edi implements Minder_ConnoteProccess_DespatchPolicy_Interface {
    protected $_selectedPickItems;
    protected $_pickOrders;

    function __construct($selectedPickItems, Minder_PickOrder_Collection $pickOrders)
    {
        $this->_selectedPickItems = $selectedPickItems;
        $this->_pickOrders = $pickOrders;
    }

    public function check()
    {
        $filteredOrders = $this->_pickOrders->filterPartialDespatchAllowed(false);
        if (count($filteredOrders) < 1) {
            return; //nothing to check
        }

        $failedOrdersAndDestinations = array();

        $sscc       = $this->_fetchPackSscc($filteredOrders->PICK_ORDER);
        $deliverToDcList = array_unique(Minder_ArrayUtils::mapField($sscc, 'PS_DEL_TO_DC_NO'));

        $pickItems  = $this->_fetchPickItems($filteredOrders->PICK_ORDER);

        foreach ($deliverToDcList as $deliverToDc) {
            $deliverToSscc = Minder_ArrayUtils::filterFieldValueInList($sscc, 'PS_DEL_TO_DC_NO', array($deliverToDc));
            $psPickLabelNoList = Minder_ArrayUtils::mapField($deliverToSscc, 'PS_PICK_LABEL_NO');
            $deliverToDcItems = Minder_ArrayUtils::filterFieldValueInList($pickItems, 'PICK_LABEL_NO', $psPickLabelNoList);
            $selectedItems = Minder_ArrayUtils::filterFieldValueInList($deliverToDcItems, 'PICK_LABEL_NO', $this->_selectedPickItems);

            if (count($selectedItems) > 0 && $this->_pickItemListHasDespatchedItems($deliverToDcItems)) {
                $failedOrdersAndDestinations[] = array(
                    'PICK_ORDER' => array_shift($deliverToDcItems[0]['PICK_ORDER']),
                    'DELIVER_TO_DC' => $deliverToDc,
                );
            }
        }

        if (count($failedOrdersAndDestinations) > 0) {
            throw new Exception('Item(s) have been already despatched for order and DC pair(s) ' . implode(', ', array_map(function($orderAndDc){
                return '(' . $orderAndDc['PICK_ORDER'] . ', ' . $orderAndDc['DELIVER_TO_DC'] . ')';
            }, $failedOrdersAndDestinations)) . ' and Partial Despatch is not allowed');
        }
    }

    protected function _pickItemListHasDespatchedItems($pickItemList) {
        foreach ($pickItemList as $pickItem) {
            if (in_array($pickItem['PICK_LINE_STATUS'], array('DC', 'DX'))) {
                return true;
            }
        }

        return false;

    }

    protected function _fetchPackSscc($pickOrders) {
        if (empty($pickOrders)) {
            return array();
        }

        $sql = "
            SELECT
                PS_DEL_TO_DC_NO,
                PS_PICK_LABEL_NO
            FROM
                PACK_SSCC
            WHERE
                PS_PICK_ORDER IN (" . implode(', ', array_fill(0, count($pickOrders), '?')) . ") AND PS_SSCC_STATUS <> 'CN'
        ";

        array_unshift($pickOrders, $sql);
        $result = call_user_func_array(array(Minder::getInstance(), 'fetchAllAssoc'), $pickOrders);

        if (false === $result) {
            throw new Exception(Minder::getInstance()->lastError);
        }

        return $result;
    }

    protected function _fetchPickItems($pickOrders) {
        if (empty($pickOrders)) {
            return array();
        }

        $sql = "
            SELECT
                PICK_LABEL_NO,
                PICK_ORDER,
                PICK_LINE_STATUS
            FROM
                PICK_ITEM
            WHERE
                PICK_ORDER IN (" . implode(', ', array_fill(0, count($pickOrders), '?')) . ")
        ";

        array_unshift($pickOrders, $sql);
        $result = call_user_func_array(array(Minder::getInstance(), 'fetchAllAssoc'), $pickOrders);

        if (false === $result) {
            throw new Exception(Minder::getInstance()->lastError);
        }

        return $result;
    }
}