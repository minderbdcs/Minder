<?php

class Minder2_Model_Mapper_Warehouse {

    /**
     * @param string $whId
     * @return Minder2_Model_Warehouse
     */
    public function find($whId) {
        if (empty($whId))
            return new Minder2_Model_Warehouse();

        $sql = "
            SELECT
                *
            FROM
                WAREHOUSE
            WHERE
                WH_ID = ?
        ";

        $result = Minder::getInstance()->fetchAssoc($sql, $whId);

        if (false === $result) {
            $warehouse = new Minder2_Model_Warehouse();
            $warehouse->existed = false;
        } else {
            $warehouse = new Minder2_Model_Warehouse($result);
            $warehouse->existed = true;
        }

        return $warehouse;
    }

    /**
     * @param Minder2_Model_SysUser $user
     * @return Minder2_Model_Warehouse[]
     */
    public function selectUsersAccessWarehouseList($user) {
        if (!$user->existed)
            return array();

        if ($user->isSuperAdmin()) {
            $sql = "SELECT * FROM WAREHOUSE";
            $result = Minder::getInstance()->fetchAllAssoc($sql);
        } elseif ($user->isAdmin()) {
            $sql = "SELECT * FROM WAREHOUSE WHERE NOT ((WH_ID  = 'XA') OR (WH_ID >= 'XC' AND WH_ID <= 'XX'))";
            $result = Minder::getInstance()->fetchAllAssoc($sql);
        } else {
            $sql = "
                SELECT *
                FROM
                    ACCESS_USER
                    LEFT JOIN WAREHOUSE ON ACCESS_USER.WH_ID = WAREHOUSE.WH_ID
                WHERE
                    USER_ID = ?
            ";
            $result = Minder::getInstance()->fetchAllAssoc($sql, $user->USER_ID);
        }

        if (false === $result)
            return array();

        $warehouseList = array();
        foreach ($result as $warehouseDescription) {
            $tmpWarehouse = new Minder2_Model_Warehouse($warehouseDescription);
            $tmpWarehouse->existed = true;
            $warehouseList[$tmpWarehouse->WH_ID] = $tmpWarehouse;
        }

        return $warehouseList;
    }
}