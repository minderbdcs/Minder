<?php

class Minder_SysScreen_Legacy_TableProvider {
    public function fetchTables($screenName) {
        $sql = "SELECT SST_TABLE,
                       SST_SEQUENCE,
                       SST_ALIAS,
                       SST_JOIN,
                       SST_VIA,
                       COMPANY_ID,
                       WH_ID,
                       SST_USER_TYPE,
                       SST_DEVICE_TYPE
                FROM SYS_SCREEN_TABLE
                WHERE SS_NAME = ?
                AND   SST_TABLE_STATUS = ?
                ORDER BY SST_SEQUENCE ";

        $values = array( $screenName, 'OK' );

        if (false === ($values = $this->_getMinder()->prepareArgs($sql, $values))) {
            throw new Minder_Exception(ibase_errcode() . ': ' . ibase_errmsg() . '. Query text: ' . $sql);
        }

        return $this->_getMinder()->findAllAssocMod($sql, $values);
    }

    protected function _getMinder() {
        return Minder::getInstance();
    }
}