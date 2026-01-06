<?php

class Minder_OtcProcess_State_Tool extends Minder_OtcProcess_State_AbstractItem {
    function __construct($id = null, $via = 'S')
    {
        parent::__construct($id, $via);

        $this->itemType = self::TOOL;
        $this->scannedItemType = self::TOOL;
    }
}