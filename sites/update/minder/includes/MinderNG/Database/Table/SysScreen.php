<?php

namespace MinderNG\Database\Table;

class SysScreen extends AbstractTable {
    protected $_name = 'SYS_SCREEN';
    protected $_primary = 'RECORD_ID';

    public function getScreenCollection(array $pages, \Minder2_Model_SysUser $user, \Minder2_Model_SysEquip $device, \Minder2_Model_Company $companyLimit) {
        $where = $this->select(self::SELECT_WITH_FROM_PART)
//            ->where($this->_formatEmptyOrEqualLimit($this->_getCompanyLimit($user, $companyLimit), 'COMPANY_ID', $this->_name))
//            ->where($this->_formatEmptyOrEqualLimit($device->WH_ID, 'WH_ID', $this->_name))
            ->where($this->_formatInListLimit($pages, 'SS_MENU_ID', $this->_name));

        if (!$user->isSuperAdmin()) {
            $where->where($this->_formatAccessLimit($user->getAccessCompanyList(), 'COMPANY_ID', $this->_name))
                ->where($this->_formatAccessLimit($user->getAccessWarehouseList(), 'WH_ID', $this->_name));
        }

        return $this->fetchAll($where)->toArray();
    }

}