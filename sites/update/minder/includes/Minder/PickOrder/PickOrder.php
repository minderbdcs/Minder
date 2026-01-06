<?php

/**
 * Class Minder_PickOrder_PickOrder
 *
 * @property string PICK_ORDER
 * @property string PICK_STATUS
 * @property string PICK_ORDER_SUB_TYPE
 * @property string PARTIAL_PICK_ALLOWED
 * @property string PARTIAL_DESPATCH_ALLOWED
 * @property string HAS_UNPICKED_ITEMS
 * @property string IMPORT_DESPATCH_ID
 */
class Minder_PickOrder_PickOrder extends ArrayObject {
    public function __construct($data = array())
    {
        $defaults = array(
            'PICK_ORDER' => '',
        );

        parent::__construct(array_merge($defaults, $data), ArrayObject::ARRAY_AS_PROPS, "ArrayIterator");
    }

    public function partialPickAllowed() {
        return strtoupper($this->PARTIAL_PICK_ALLOWED) === 'T';
    }

    public function partialDespatchAllowed() {
        return strtoupper($this->PARTIAL_DESPATCH_ALLOWED) === 'T';
    }

    public function hasUnpickedItems() {
        return strtoupper($this->HAS_UNPICKED_ITEMS) === 'T';
    }

    public function readyToCheck() {
        return $this->partialDespatchAllowed() || !$this->hasUnpickedItems();
    }

    public function isEdiOrder() {
        return !empty($this->IMPORT_DESPATCH_ID);
    }
}