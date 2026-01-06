<?php

namespace MinderNG\Database\Table;

class Param extends AbstractTable {
    protected $_name = 'PARAM';
    protected $_primary = array('DATA_MODEL', 'DATA_BRAND', 'DATA_ID');

    public function getDeviceIdentifiers(\Minder2_Model_SysEquip $device) {
        $select = $this->select(self::SELECT_WITH_FROM_PART)
            ->where('DATA_BRAND = ?', $device->getBrandOrDefault())
            ->where('DATA_MODEL = ?', $device->getModelOrDefault());

        return $this->fetchAll($select)->toArray();
    }
}