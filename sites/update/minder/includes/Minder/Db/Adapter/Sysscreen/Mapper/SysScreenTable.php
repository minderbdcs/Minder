<?php

class Minder_Db_Adapter_Sysscreen_Mapper_SysScreenTable extends Minder_Db_Adapter_Sysscreen_Mapper_Abstract {

    /**
     * @param string $ssName
     * @return array
     */
    public function fetchBySsName($ssName) {
        $sql = "
            SELECT
                *
            FROM
                SYS_SCREEN_TABLE
            WHERE
                (SYS_SCREEN_TABLE.COMPANY_ID IS NULL OR SYS_SCREEN_TABLE.COMPANY_ID = ? OR SYS_SCREEN_TABLE.COMPANY_ID = ?)
                AND (SYS_SCREEN_TABLE.WH_ID IS NULL OR SYS_SCREEN_TABLE.WH_ID = ? OR SYS_SCREEN_TABLE.WH_ID = ? )
                AND (SYS_SCREEN_TABLE.SST_DEVICE_TYPE IS NULL OR SYS_SCREEN_TABLE.SST_DEVICE_TYPE = ? OR SYS_SCREEN_TABLE.SST_DEVICE_TYPE = ?)
                AND SYS_SCREEN_TABLE.SS_NAME = ?
                AND SYS_SCREEN_TABLE.SST_TABLE_STATUS = ?
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

    private function _getDbTable()
    {
        if (is_null($this->_dbTable))
            $this->_dbTable = new Minder_Db_Table_SysScreenTable();

        return $this->_dbTable;
    }


}