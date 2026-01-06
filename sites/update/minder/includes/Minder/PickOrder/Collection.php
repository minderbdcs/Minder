<?php

/**
 * Class Minder_PickOrder_Collection
 *
 * @property string[] PICK_ORDER
 * @property string[] PICK_STATUS
 * @property string[] PICK_ORDER_SUB_TYPE
 * @property string[] COMPANY_ID
 */
class Minder_PickOrder_Collection extends Minder_Collection {
    protected function _add(Minder_PickOrder_PickOrder $object)
    {
        parent::_add($object);
    }

    public function fromArray($data = array()) {
        foreach ($data as $pickOrder) {
            $this->_add(new Minder_PickOrder_PickOrder($pickOrder));
        }
    }

    /**
     * @return Minder_PickOrder_PickOrder[]
     */
    public function getIterator()
    {
        return parent::getIterator();
    }

    public function readyToCheck() {
        foreach ($this->getIterator() as $pickOrder) {
            if (!$pickOrder->readyToCheck()) {
                return false;
            }
        }

        return true;
    }

    public function hasEdiOrders() {
        foreach ($this->getIterator() as $pickOrder) {
            if ($pickOrder->isEdiOrder()) {
                return true;
            }
        }

        return false;
    }

    public function filterPartialDespatchAllowed($allowed = true) {
        return $this->_filter(function(Minder_PickOrder_PickOrder $pickOrder)use($allowed){
            return $pickOrder->PARTIAL_DESPATCH_ALLOWED == (($allowed) ? 'T' : 'F');
        });
    }

    public function hasOrdersWhichCannotBePartiallyDespatched() {
        foreach ($this as $pickOrder) {
            if ($pickOrder->PARTIAL_DESPATCH_ALLOWED == 'F') {
                return true;
            }
        }

        return false;
    }

    protected function _newItem($itemData = array())
    {
        return new Minder_PickOrder_PickOrder($itemData);
    }


}