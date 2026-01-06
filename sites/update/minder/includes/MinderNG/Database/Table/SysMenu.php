<?php

namespace MinderNG\Database\Table;

class SysMenu extends AbstractTable {
    protected $_name = 'SYS_MENU';
    protected $_primary = 'RECORD_ID';

    public function getSysMenuCollection(\Minder2_Model_SysUser $user, \Minder2_Model_SysEquip $device, \Minder2_Model_Company $companyLimit) {

        $where = $this->select(self::SELECT_WITH_FROM_PART)
//            ->where($this->_formatEmptyOrEqualLimit($this->_getCompanyLimit($user, $companyLimit), 'SM_COMPANY_ID', $this->_name))
//            ->where($this->_formatEmptyOrEqualLimit($device->WH_ID, 'SM_WH_ID', $this->_name))
            ->where('SM_MENU_STATUS = ?', 'OK');

        if (!$user->isSuperAdmin()) {
            $where->where($this->_formatAccessLimit($user->getAccessCompanyList(), 'SM_COMPANY_ID'))
                ->where($this->_formatAccessLimit($user->getAccessWarehouseList(), 'SM_WH_ID'));
        }

        return $this->fetchAll($where)->toArray();
    }


}