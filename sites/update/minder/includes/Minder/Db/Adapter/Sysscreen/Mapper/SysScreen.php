<?php

class Minder_Db_Table_Sysscreen_Mapper_SysScreen extends Minder_Db_Adapter_Sysscreen_Mapper_Abstract {
    /**
     * @return Zend_Db_Table_Abstract
     */
    protected function _getDbTable() {
        if (is_null($this->_dbTable))
            $this->_dbTable = new Minder_Db_Table_SysScreen();

        return $this->_dbTable;
    }

    public function findBySsName($ssName, $fetchMode = Zend_Db::FETCH_OBJ) {
        $sql = "
            SELECT
                *
            FROM
                SYS_SCREEN
            WHERE
                (SYS_SCREEN.COMPANY_ID IS NULL OR SYS_SCREEN.COMPANY_ID = ? OR SYS_SCREEN.COMPANY_ID = ?)
                AND (SYS_SCREEN.WH_ID IS NULL OR SYS_SCREEN.WH_ID = ? OR SYS_SCREEN.WH_ID = ? )
                AND SYS_SCREEN.SS_NAME = ?
        ";

        return $this->_getDbTable()->getAdapter()->query($sql, array(
                                                                 "",
                                                                 $this->_getCurrentCompany()->COMPANY_ID,
                                                                 "",
                                                                 $this->_getCurrentWarehouse()->WH_ID,
                                                                 $ssName
                                                               ))->fetch($fetchMode);

    }

    public function fetchAll($fetchMode = Zend_Db::FETCH_OBJ) {
        $sysScreenTable = $this->_getDbTable();

        $sql = "
            SELECT
                *
            FROM
                SYS_SCREEN
            WHERE
                (SYS_SCREEN.COMPANY_ID IS NULL OR SYS_SCREEN.COMPANY_ID = ? OR SYS_SCREEN.COMPANY_ID = ?)
                AND (SYS_SCREEN.WH_ID IS NULL OR SYS_SCREEN.WH_ID = ? OR SYS_SCREEN.WH_ID = ? )
        ";

        return $sysScreenTable->getAdapter()->query($sql, array('', $this->_getCurrentCompany()->COMPANY_ID, '', $this->_getCurrentWarehouse()->WH_ID))->fetchAll($fetchMode);
    }

    /**
     * @return array
     */
    public function fetchSsNames() {
        $result = array();

        /**
         * @var Minder_Db_Table_SysScreenRow $sysScreen
         */
        foreach ($this->fetchAll() as $sysScreen) {
            $result[] = $sysScreen->SS_NAME;
        }


        return $result;
    }
}