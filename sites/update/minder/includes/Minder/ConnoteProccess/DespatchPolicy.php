<?php

class Minder_ConnoteProccess_DespatchPolicy {
    /**
     * @param Minder_PickOrder_Collection $pickOrdersCollection
     * @param $pickItems
     * @return Minder_ConnoteProccess_DespatchPolicy_Interface
     */
    public static function factory(Minder_PickOrder_Collection $pickOrdersCollection, $pickItems) {
        if (self::_isEdiOrders($pickOrdersCollection)) {
            return new Minder_ConnoteProccess_DespatchPolicy_Edi($pickItems, $pickOrdersCollection);
        } else {
            return new Minder_ConnoteProccess_DespatchPolicy_Legacy($pickOrdersCollection);
        }
    }

    protected static function _isEdiOrders(Minder_PickOrder_Collection $orders)
    {
        $orderTypeManager = new Minder2_Options_PickOrderType_Manager();
        $ediSubTypes = Minder_ArrayUtils::mapField($orderTypeManager->getEdiTypes(), 'orderSubType');
        $result = false;

        if (count($ediSubTypes) > 0) {
            $commonTypes = array_intersect($ediSubTypes, array_unique($orders->PICK_ORDER_SUB_TYPE));
            $result = (count($commonTypes) > 0);
        }

        return $result || $orders->hasEdiOrders();
    }
}