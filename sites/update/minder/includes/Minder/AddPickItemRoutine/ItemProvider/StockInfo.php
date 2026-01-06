<?php

class Minder_AddPickItemRoutine_ItemProvider_StockInfo {
    public $itemId       = '';
    public $itemFound    = false;
    public $availableQty = 0;
    public $defaultPrice = 0;

    function __construct($itemId, $availableQty, $itemFound, $defaultPrice) {
        $this->itemId       = $itemId;
        $this->availableQty = $availableQty;
        $this->itemFound    = $itemFound;
        $this->defaultPrice = $defaultPrice;
    }
}