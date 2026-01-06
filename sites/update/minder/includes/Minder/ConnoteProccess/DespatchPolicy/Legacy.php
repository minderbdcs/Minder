<?php

class Minder_ConnoteProccess_DespatchPolicy_Legacy implements Minder_ConnoteProccess_DespatchPolicy_Interface {
    /**
     * @var Minder_PickOrder_Collection
     */
    protected $_pickOrders;

    function __construct(Minder_PickOrder_Collection $pickOrders)
    {
        $this->_pickOrders = $pickOrders;
    }

    public function check()
    {
        $partialDespatchPermission = new Minder_Permission_Order_PartialDespatch();
        $fullyPickedPermission = new Minder_Permission_Order_FullyPicked();
        $despatchingOrders = $this->_getPickOrders()->PICK_ORDER;

        $partiallyDespatchedOrders = $partialDespatchPermission->check($despatchingOrders);
        $notFullyPickedOrders = $fullyPickedPermission->check($despatchingOrders);

        $errors = array();

        if (count($partiallyDespatchedOrders) > 0) {
            $errors[] = 'Order(s) (' . implode(',', $partiallyDespatchedOrders) . ') have been despatched already and Partial Despatch is not allowed.';
        }

        if (count($notFullyPickedOrders) > 0) {
            $errors[] = 'Order(s) (' . implode(',', $notFullyPickedOrders) . ') are not fully picked but DESPATCH_FULL_ORDER set to \'T\'.';
        }

        if (count($errors) > 0) {
            throw new Exception(implode("\n", $errors));
        }
    }

    /**
     * @return Minder_PickOrder_Collection
     */
    public function _getPickOrders() {
        return $this->_pickOrders;
    }
}