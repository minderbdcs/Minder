<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\Collection;

class WarehouseCollection extends Collection {
    protected function _getDefaultModelClassName()
    {
        return __NAMESPACE__ . '\\Warehouse';
    }

    /**
     * @param string|array|Warehouse $idOrAggregate
     * @return Warehouse
     * @throws Exception\WarehouseNotFound
     */
    public function findWarehouse($idOrAggregate) {
        $warehouse = ($idOrAggregate instanceof Warehouse) ? $idOrAggregate : $this->newWarehouse($idOrAggregate);
        $foundWarehouse = $this->get($warehouse);

        if (empty($foundWarehouse)) {
            throw new Exception\WarehouseNotFound($warehouse);
        }

        return $foundWarehouse;
    }

    /**
     * @param $idOrAggregate
     * @return Warehouse
     */
    public function newWarehouse($idOrAggregate) {
        return $this->newModelInstance(is_string($idOrAggregate) ? array('WH_ID' => $idOrAggregate) : $idOrAggregate);
    }
}