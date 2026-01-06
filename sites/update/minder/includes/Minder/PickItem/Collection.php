<?php

/**
 * Class Minder_PickItem_Collection
 *
 * @property string[] PICK_ORDER
 */
class Minder_PickItem_Collection extends Minder_Collection {
    protected function _add(Minder_PickItem_PickItem $pickItem)
    {
        parent::_add($pickItem);
    }

    public function fromArray($data = array()) {
        foreach ($data as $pickItem) {
            $this->_add(new Minder_PickItem_PickItem($pickItem));
        }
    }

    /**
     * @return Minder_PickItem_PickItem[]
     */
    public function getIterator()
    {
        return parent::getIterator();
    }

    public function filterPickLabelNoInList($pickLabelNos) {
        $pickLabelNos = array_flip($pickLabelNos);
        return $this->_filter(function(Minder_PickItem_PickItem $pickItem)use($pickLabelNos){
            return isset($pickLabelNos[$pickItem->PICK_LABEL_NO]);
        });
    }

    /**
     * @param $pickLabelNos
     * @return Minder_PickItem_Collection
     */
    public function filterPickLabelNoNotInList($pickLabelNos) {
        $pickLabelNos = array_flip($pickLabelNos);
        return $this->_filter(function(Minder_PickItem_PickItem $pickItem)use($pickLabelNos){
            return !isset($pickLabelNos[$pickItem->PICK_LABEL_NO]);
        });
    }

    public function hasDespatchedItems() {
        foreach ($this as $pickItem) {
            if (in_array($pickItem->PICK_LINE_STATUS, array('DC', 'DX'))) {
                return true;
            }
        }

        return false;
    }

    public function hasStatusNotInList($statusList) {
        $statusList = array_flip($statusList);
        foreach ($this as $pickItem) {
            if (!isset($statusList[$pickItem->PICK_LABEL_NO])) {
                return true;
            }
        }

        return false;
    }

    protected function _newItem($itemData = array())
    {
        return new Minder_PickItem_PickItem($itemData);
    }
}