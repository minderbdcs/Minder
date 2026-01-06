<?php

namespace MinderNG\Database\Table;

class SysScreenButton extends AbstractTable {
    protected $_name = 'SYS_SCREEN_BUTTON';
    protected $_primary = 'RECORD_ID';

    public function getScreenButtonCollection(array $screens, \Minder2_Model_SysUser $user) {
        if (empty($screens)) {
            return array();
        }

        $select = $this->select(self::SELECT_WITH_FROM_PART)
            ->where($this->_formatInListLimit($screens, 'SS_NAME', $this->_name));

        if (!$user->isSuperAdmin()) {
            $select->where($this->_formatAccessLimit($user->getAccessCompanyList(), 'COMPANY_ID', $this->_name))
                ->where($this->_formatAccessLimit($user->getAccessWarehouseList(), 'WH_ID', $this->_name));
        }

        return $this->fetchAll($select)->toArray();
    }
}