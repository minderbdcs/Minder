<?php

class Carrier_Mapper {

    public function find($carrierId) {
        $minder = Minder::getInstance();

        if (false !== ($result = $minder->fetchAssoc("SELECT CARRIER_ID, POST_CODE_DEPOT_ID FROM CARRIER WHERE CARRIER_ID = ?", $carrierId)))
            return new Carrier($result);

        return new Carrier_NullRecord();
    }
}