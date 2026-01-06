<?php

namespace MinderNG\PageMicrocode\JsonRpc;

use MinderNG\PageMicrocode\Microcode;

class SetWarehouseLimit {
    public function execute(Microcode $microcode, $warehouseLimit) {
        $warehouse = $microcode->getPageComponents()->warehouseLimitList->findWarehouse($warehouseLimit);
        $microcode->getPageComponents()->warehouseLimit->set($warehouse->getAttributes());

        return $microcode->getPageComponents()->getArrayCopy();
    }
}