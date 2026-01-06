<?php

class Minder_ConnoteProccess_PartialOrderPolicy_Edi implements Minder_ConnoteProccess_PartialOrderPolicy_Interface {
    protected $_pickOrderCollection;
    protected $_selectedPickLabelNos;

    function __construct(Minder_PickOrder_Collection $pickOrderCollection, $selectedPickLabelNos)
    {
        $this->_pickOrderCollection = $pickOrderCollection;
        $this->_selectedPickLabelNos = $selectedPickLabelNos;
    }


    public function check()
    {
        $filteredOrders = $this->_pickOrderCollection->filterPartialDespatchAllowed(false);

        if (count($filteredOrders) < 1) {
            return; //nothing to check
        }

        $failedOrdersAndDestinations = array();
        $orderItems = $this->_fetchPickItems($filteredOrders->PICK_ORDER);
        $orderSscc  = $this->_fetchPackSscc($filteredOrders->PICK_ORDER);
        $psDelToDcNoList = array_unique(Minder_ArrayUtils::mapField($orderSscc, 'PS_DEL_TO_DC_NO'));

        foreach ($psDelToDcNoList as $deliverToDc) {
            $deliverToDcSscc  = Minder_ArrayUtils::filterFieldValueInList($orderSscc, 'PS_DEL_TO_DC_NO', array($deliverToDc));
            $psPickLabelNoList = Minder_ArrayUtils::mapField($deliverToDcSscc, 'PS_PICK_LABEL_NO');
            $deliverToDcItems = Minder_ArrayUtils::filterFieldValueInList($orderItems, 'PICK_LABEL_NO', $psPickLabelNoList);
            $selectedItems    = Minder_ArrayUtils::filterFieldValueInList($deliverToDcItems, 'PICK_LABEL_NO', $this->_selectedPickLabelNos);

            if (count($selectedItems) > 0) {
                $notSelectedItems = Minder_ArrayUtils::filterFieldValueNotInList($deliverToDcItems, 'PICK_LABEL_NO', $this->_selectedPickLabelNos);
                $notSelectedStatuses = Minder_ArrayUtils::filterFieldValueNotInList($notSelectedItems, 'PICK_LINE_STATUS', array('DS', 'AS', 'DC', 'DX'));

                if (count($notSelectedItems) > 0 && count($notSelectedStatuses) > 0) {
                    $failedOrdersAndDestinations[] = array(
                        'PICK_ORDER' => $deliverToDcItems[0]['PICK_ORDER'],
                        'DELIVER_TO_DC' => $deliverToDc,
                    );
                }
            }
        }

        if (count($failedOrdersAndDestinations) > 0) {
            throw new Exception('Item(s) have not been fully picked for order and DC pair(s) ' . implode(', ', array_map(function($orderAndDc){
                    return '(' . $orderAndDc['PICK_ORDER'] . ', ' . $orderAndDc['DELIVER_TO_DC'] . ')';
                }, $failedOrdersAndDestinations)) . ' but Partial Despatch is not allowed');
        }
    }

    protected function _fetchPackSscc($pickOrders) {
        if (count($pickOrders) < 1) {
            return array();
        }

        $sql = "
            SELECT
                PS_DEL_TO_DC_NO,
                PS_PICK_LABEL_NO
            FROM
                PACK_SSCC
            WHERE
                PS_PICK_ORDER IN (" . implode(', ', array_fill(0, count($pickOrders), '?')) . ")
                AND PS_SSCC_STATUS <> 'CN'
        ";

        array_unshift($pickOrders, $sql);
        $result = call_user_func_array(array(Minder::getInstance(), 'fetchAllAssoc'), $pickOrders);

        if (false === $result) {
            throw new Exception(Minder::getInstance()->lastError);
        }

        return $result;
    }

    protected function _fetchPickItems($pickOrders) {
        if (count($pickOrders) < 1) {
            return array();
        }

        $sql = "
            SELECT
                PICK_LABEL_NO,
                PICK_LINE_STATUS,
                PICK_ORDER
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