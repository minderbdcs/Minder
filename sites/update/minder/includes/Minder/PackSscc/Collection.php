<?php

/**
 * Class Minder_PackSscc_Collection
 *
 * @property string[] PS_PICK_ORDER
 * @property string[] PS_PICK_ORDER_LINE_NO
 * @property string[] PS_PICK_LABEL_NO
 * @property string[] PS_SSCC
 * @property string[] PS_SSCC_STATUS
 * @property string[] PS_DEL_TO_DC_NO
 */
class Minder_PackSscc_Collection extends Minder_Collection {
    protected function _add(Minder_PackSscc_PackSscc $object)
    {
        parent::_add($object);
    }

    public function fromArray($data = array()) {
        foreach ($data as $packSscc) {
            $this->_add(new Minder_PackSscc_PackSscc($packSscc));
        }
    }

    public function filterByDeliverToDcNo($dcList) {
        $dcList = array_flip($dcList);
        return $this->_filter(function(Minder_PackSscc_PackSscc $sscc)use($dcList){
            return isset($dcList[$sscc->PS_DEL_TO_DC_NO]);
        });
    }

    protected function _newItem($itemData = array())
    {
        return new Minder_PackSscc_PackSscc($itemData);
    }
}