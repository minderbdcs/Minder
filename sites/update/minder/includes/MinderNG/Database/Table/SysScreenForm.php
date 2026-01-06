<?php

namespace MinderNG\Database\Table;

class SysScreenForm extends AbstractTable {
    protected $_name = 'SYS_SCREEN_FORM';
    protected $_primary = 'RECORD_ID';

    public function getScreenForms($screens, \Minder2_Model_SysUser $user, \Minder2_Model_SysEquip $device, \Minder2_Model_Company $companyLimit) {
        if (empty($screens)) {
            return array();
        }

        $select = $this->select(self::SELECT_WITH_FROM_PART)
//            ->where('SSF_STATUS = ?', 'OK')
//            ->where($this->_formatEmptyOrEqualLimit($this->_getCompanyLimit($user, $companyLimit), 'COMPANY_ID', $this->_name))
//            ->where($this->_formatEmptyOrEqualLimit($device->WH_ID, 'WH_ID', $this->_name))
            ->where($this->_formatInListLimit($screens, 'SS_NAME', $this->_name));

        if (!$user->isSuperAdmin()) {
            $select->where($this->_formatAccessLimit($user->getAccessCompanyList(), 'COMPANY_ID', $this->_name))
                ->where($this->_formatAccessLimit($user->getAccessWarehouseList(), 'WH_ID', $this->_name));
        }

        return $this->fetchAll($select)->toArray();
    }
}