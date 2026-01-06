<?php

class Minder_Db_Adapter_Sysscreen_Mapper_SysScreenOrder extends Minder_Db_Adapter_Sysscreen_Mapper_Abstract {

    private function _getDbTable() {
        if (is_null($this->_dbTable))
            $this->_dbTable = new Minder_Db_Table_SysScreenOrder();

        return $this->_dbTable;
    }

    public function fetchBySsName($ssName) {
        $sql = "
            SELECT
                *
            FROM
                SYS_SCREEN_ORDER
            WHERE
                (SYS_SCREEN_ORDER.COMPANY_ID IS NULL OR SYS_SCREEN_ORDER.COMPANY_ID = ? OR SYS_SCREEN_ORDER.COMPANY_ID = ?)
                AND (SYS_SCREEN_ORDER.WH_ID IS NULL OR SYS_SCREEN_ORDER.WH_ID = ? OR SYS_SCREEN_ORDER.WH_ID = ? )
                AND (SYS_SCREEN_ORDER.SSO_DEVICE_TYPE IS NULL OR SYS_SCREEN_ORDER.SSO_DEVICE_TYPE = ? OR SYS_SCREEN_ORDER.SSO_DEVICE_TYPE = ?)
                AND SYS_SCREEN_ORDER.SS_NAME = ?
                AND SYS_SCREEN_ORDER.SSO_ORDER_STATUS = ?
            ORDER BY
                SSO_SEQUENCE ASC
        ";

        $result      = array();
        $rowClass    = $this->_getDbTable()->getRowClass();
        $queryResult = $this->_getDbTable()->getAdapter()->query($sql, array(
                                                                 "",
                                                                 $this->_getCurrentCompany()->COMPANY_ID,
                                                                 "",
                                                                 $this->_getCurrentWarehouse()->WH_ID,
                                                                 "",
                                                                 $this->_getCurrentDevice()->DEVICE_TYPE,
                                                                 $ssName,
                                                                 'OK'
                                                               ));

        while ($nextRow = $queryResult->fetch(Zend_Db::FETCH_ASSOC))
            $result[] = new $rowClass(array('data' => $nextRow, 'table' => $this->_getDbTable(), 'stored' => true));

        return $result;

    }
}