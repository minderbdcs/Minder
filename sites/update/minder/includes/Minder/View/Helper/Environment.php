<?php

class Minder_View_Helper_Environment extends Zend_View_Helper_Abstract {

    public function environment() {
        return $this;
    }

    public function getCurrentWarehouseId() {
        $whId = '';

        try {
            $whId = Minder2_Environment::getCurrentWarehouse()->WH_ID;
        } catch (Exception $e) {
            trigger_error(__METHOD__ . ': ' . $e->getMessage(), E_USER_ERROR);
        }

        return $whId;
    }
}