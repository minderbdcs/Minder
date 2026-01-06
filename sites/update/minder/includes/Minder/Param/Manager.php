<?php

class Minder_Param_Manager {
    protected $_known = array();

    /**
     * @param $dataId
     * @param Minder2_Model_SysEquip $device
     * @return Minder_Param_Param|null
     * @throws Minder_Exception
     */
    public function getDeviceDataIdentifier($dataId, Minder2_Model_SysEquip $device) {
        $minder = Minder::getInstance();

        $sql = 'SELECT * FROM PARAM WHERE DATA_ID = ? AND DATA_BRAND = ? AND DATA_MODEL = ?';

        $result = $minder->fetchAssoc($sql, $dataId, $device->getBrandOrDefault(), $device->getModelOrDefault());

        return is_array($result) ? new Minder_Param_Param($result) : null;
    }

    /**
     * @param $dataTypes
     * @param Minder2_Model_SysEquip $device
     * @return array
     */
    public function getDeviceDataIdByType($dataTypes, Minder2_Model_SysEquip $device) {
        $dataTypes = (array)$dataTypes;

        if (empty($dataTypes)) {
            return array();
        }

        $sql = "
            SELECT
                DATA_ID,
                DATA_ID AS PARAM_ID
            FROM
                PARAM
            wHERE
                DATA_BRAND = ?
                AND DATA_MODEL = ?
                AND DATA_TYPE_ID IN (" . substr(str_repeat('?, ', count($dataTypes)), 0, -2) . ")
        ";

        array_unshift($dataTypes, $sql, $device->getBrandOrDefault(), $device->getModelOrDefault());

        return call_user_func_array(array(Minder::getInstance(), 'findList'), $dataTypes);
    }

    /**
     * @param array $dataIds
     * @param Minder2_Model_SysEquip $device
     * @return Minder_Param_Param[]|null[]
     */
    public function getMany(array $dataIds, Minder2_Model_SysEquip $device = null) {
        $toFetch = $this->_getUnknown($dataIds);

        if (count($toFetch) > 0) {
            $tmpData = Minder_ArrayUtils::populate(array_flip($dataIds), null);
            $fetchedData = Minder_ArrayUtils::mapKey($this->_fetch($dataIds), 'DATA_ID');

            $result = array_replace($tmpData, $fetchedData);
            $this->_addKnown($result);
        } else {
            $result = $this->_mapKnown($dataIds);
        }

        return $result;
    }

    /**
     * @param array $dataIds
     * @param Minder2_Model_SysEquip $device
     * @return Minder_Param_Param[]
     */
    protected function _fetch(array $dataIds, Minder2_Model_SysEquip $device = null) {
        if (count($dataIds) < 1) {
            return array();
        }

        $sql = 'SELECT * FROM PARAM WHERE DATA_ID IN (' . substr(str_repeat('?, ', count($dataIds)), 0, -2) . ')';

        if (!is_null($device)) {
            $sql .= "
                AND DATA_BRAND = ?
                AND DATA_MODEL = ?
            ";

            $dataIds[] = $device->getBrandOrDefault();
            $dataIds[] = $device->getModelOrDefault();
        }

        array_unshift($dataIds, $sql);
        $result = array();
        foreach (call_user_func_array(array(Minder::getInstance(), 'fetchAllAssoc'), $dataIds) as $rowData) {
            $result[] = new Minder_Param_Param($rowData);
        }

        return $result;
    }
    protected function _getUnknown(array $dataIds) {
        return array_diff($dataIds, array_keys($this->_known));
    }

    protected function _addKnown($data) {
        $this->_known = array_replace($this->_known, $data);
    }

    protected function _mapKnown(array $dataIds) {
        $result = array();

        foreach ($dataIds as $dataId) {
            $result[$dataId] = $this->_known[$dataId];
        }
        return $result;
    }
}