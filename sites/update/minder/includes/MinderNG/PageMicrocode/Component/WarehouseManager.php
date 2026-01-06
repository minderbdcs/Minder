<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\AddOptions;

class WarehouseManager {
    static private $_virtualAllWarehouseData = array('WH_ID' => 'all', 'DESCRIPTION' => 'All');

    public function getUserWarehouseLimitList(\Minder2_Model_SysUser $user) {
        $limitList = array(static::$_virtualAllWarehouseData);

        foreach ($user->getAccessWarehouseList() as $warehouse) {
            $limitList[] = $warehouse->getFields();
        }

        $result = new WarehouseCollection();
        $result->init($limitList, new AddOptions(false, true));

        return $result;
    }

    public function getWarehouseLimit(\Minder2_Model_Warehouse $legacyLimitModel) {
        $result = new Warehouse();
        $result->init(
            $legacyLimitModel->existed ? $legacyLimitModel->getFields() : static::$_virtualAllWarehouseData,
            true
        );

        return $result;
    }
}