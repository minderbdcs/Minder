<?php

class Minder_ConnoteProccess_PartialOrderPolicy {
    /**
     * @param Minder_PickOrder_Collection $pickOrderCollection
     * @param $pickItems
     * @return Minder_ConnoteProccess_PartialOrderPolicy_Interface
     */
    public static function factory(Minder_PickOrder_Collection $pickOrderCollection, $pickItems){
        if (static::_isEdiOrders($pickOrderCollection)) {
            return new Minder_ConnoteProccess_PartialOrderPolicy_Edi($pickOrderCollection, $pickItems);
        } else {
            return new Minder_ConnoteProccess_PartialOrderPolicy_Legacy($pickItems);
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