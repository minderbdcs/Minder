<?php

class Minder_OtcProcess_State_Consumable extends Minder_OtcProcess_State_AbstractItem {

    function __construct($id = null, $via = 'S', $prodProfile = null, $defaultCompanyId = '')
    {
        parent::__construct($id, $via);

        $this->itemType = self::CONSUMABLE;
        $this->scannedItemType = self::CONSUMABLE;
    }
}