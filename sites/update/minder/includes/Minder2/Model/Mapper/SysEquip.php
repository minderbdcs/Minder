<?php

class Minder2_Model_Mapper_SysEquip {
    /**
     * @param string $deviceId
     * @return Minder2_Model_SysEquip
     */
    public function find($deviceId) {
        if (empty($deviceId))
            return new Minder2_Model_SysEquip();

        $sql = "
            SELECT
                *
            FROM
                SYS_EQUIP
            WHERE
                DEVICE_ID = ?
        ";

        $result = Minder::getInstance()->fetchAssoc($sql, $deviceId);

        if (false === $result) {
            $device = new Minder2_Model_SysEquip();
            $device->existed = false;
        } else {
            $device = new Minder2_Model_SysEquip($result);
            $device->existed = true;
        }

        return $device;
    }

    /**
     * @param string $deviceId
     * @return Minder2_Model_SysEquip
     */
    public function findPrinter($deviceId) {
        $sql = "
            SELECT
                *
            FROM
                SYS_EQUIP
            WHERE
                DEVICE_ID = ?
                AND DEVICE_TYPE IN (" . substr(str_repeat('?, ', count($this->_getPrinterDeviceTypes())), 0, -2) . ")
        ";

        $args = $this->_getPrinterDeviceTypes();
        array_unshift($args, $deviceId);
        array_unshift($args, $sql);
        $result = call_user_func_array(array(Minder::getInstance(), 'fetchAssoc'), $args);

        if (false === $result) {
            $device = new Minder2_Model_SysEquip();
            $device->existed = false;
        } else {
            $device = new Minder2_Model_SysEquip($result);
            $device->existed = true;
        }

        return $device;
    }

    protected function _getPrinterDeviceTypes() {
        return array('PR', 'PL', 'LP');
    }

    /**
     * @param Minder2_Model_SysUser $user
     * @return Minder2_Model_SysEquip[]
     */
    public function selectUsersAccessPrinteList($user) {
        if (!$user->existed)
            return array();

        if ($user->isSuperAdmin()) {
            $sql = "
                SELECT
                    *
                FROM
                    SYS_EQUIP
                WHERE
                    DEVICE_TYPE IN (" . substr(str_repeat('?, ', count($this->_getPrinterDeviceTypes())), 0, -2) . ")
                ORDER BY
                    DEVICE_ID
            ";

            $args = $this->_getPrinterDeviceTypes();
        } else {
            $sql = "
                SELECT
                    DISTINCT SYS_EQUIP.*
                FROM
                    ACCESS_USER
                    LEFT JOIN SYS_EQUIP ON ACCESS_USER.WH_ID = SYS_EQUIP.WH_ID
                WHERE
                    ACCESS_USER.USER_ID = ?
                    AND SYS_EQUIP.DEVICE_TYPE IN (" . substr(str_repeat('?, ', count($this->_getPrinterDeviceTypes())), 0, -2) . ")
                ORDER BY
                    DEVICE_ID
            ";

            $args = $this->_getPrinterDeviceTypes();
            array_unshift($args, $user->USER_ID);
        }

        return $this->_selectDeviceList($sql, $args);
    }

    /**
     * @param Minder2_Model_Warehouse $warehouse
     * @return array(Minder2_Model_SysEquip)
     */
    public function selectWarehousePrinterList($warehouse) {
        if (!$warehouse->existed)
            return array();

        $sql = "
            SELECT
                *
            FROM
                SYS_EQUIP
            WHERE
                SYS_EQUIP.WH_ID = ?
                AND DEVICE_TYPE IN (" . substr(str_repeat('?, ', count($this->_getPrinterDeviceTypes())), 0, -2) . ")
            ORDER BY
                DEVICE_ID
        ";

        $args = $this->_getPrinterDeviceTypes();
        array_unshift($args, $warehouse->WH_ID);

        return $this->_selectDeviceList($sql, $args);
    }

    /**
     * @param string $sql
     * @param array $args
     * @return array(Minder2_Model_SysEquip)
     */
    protected function _selectDeviceList($sql, array $args = array()) {
        array_unshift($args, $sql);
        $result = call_user_func_array(array(Minder::getInstance(), 'fetchAllAssoc'), $args);

        if (false === $result)
            return array();

        $sysEquipList = array();

        foreach ($result as $deviceDescription) {
            $tmpDevice = new Minder2_Model_SysEquip($deviceDescription);
            $tmpDevice->existed = true;
            $sysEquipList[$tmpDevice->DEVICE_ID] = $tmpDevice;
        }

        return $sysEquipList;
    }
}