<?php

namespace MinderNG\Database\Table;

class SysScreenTransaction extends AbstractTable {
    protected $_name = 'SYS_SCREEN_TRANSACTION';
    protected $_primary = 'RECORD_ID';

    public function getScreenTransactions($screens) {
        if (empty($screens)) {
            return array();
        }

        $select = $this->select(self::SELECT_WITH_FROM_PART)
            ->where($this->_formatInListLimit($screens, 'SS_NAME', $this->_name));

        return $this->fetchAll($select)->toArray();
    }

}