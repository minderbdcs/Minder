<?php

class Minder_Db_Table_Mapper_SysScreenVar extends Minder_Db_Table_Mapper_Abstract {
    /**
     * @return string
     */
    public function getDefaultTableClassName()
    {
        return 'Minder_Db_Table_SysScreenVar';
    }

    /**
     * @param $sysScreen
     * @param $fieldType
     * @return Zend_Db_Table_Rowset
     */
    public function fetchCurrent($sysScreen, $fieldType) {
        $sql = "
            SELECT
                *
            FROM
                SYS_SCREEN_VAR
            WHERE
                (SYS_SCREEN_VAR.COMPANY_ID IS NULL OR SYS_SCREEN_VAR.COMPANY_ID = ? OR SYS_SCREEN_VAR.COMPANY_ID = ?)
                AND (SYS_SCREEN_VAR.WH_ID IS NULL OR SYS_SCREEN_VAR.WH_ID = ? OR SYS_SCREEN_VAR.WH_ID = ? )
                AND (SYS_SCREEN_VAR.SSV_DEVICE_TYPE IS NULL OR SYS_SCREEN_VAR.SSV_DEVICE_TYPE = ? OR SYS_SCREEN_VAR.SSV_DEVICE_TYPE = ? )
                AND SYS_SCREEN_VAR.SS_NAME = ?
                AND SYS_SCREEN_VAR.SSV_FIELD_TYPE = ?
                AND SYS_SCREEN_VAR.SSV_FIELD_STATUS = ?
        ";

        return $this->_filterByCurrentUserType($this->_fetchAll(
            $sql,
            "",
            $this->_getCurrentCompany()->COMPANY_ID,
            "",
            $this->_getCurrentWarehouse()->WH_ID,
            "",
            $this->_getCurrentDevice()->DEVICE_TYPE,
            $sysScreen,
            $fieldType,
            'OK'
        ));
    }

    /**
     * @param $fetchResult
     * @return Zend_Db_Table_Rowset
     */
    protected function _filterByCurrentUserType($fetchResult)
    {
        $result = array();

        /**
         * @var Minder_Db_Table_SysScreenVarRow $sysScreenVar
         */
        foreach ($fetchResult as $sysScreenVar) {
            if ($this->_getCurrentUser()->isSuperAdmin()) {
                $result[] = $sysScreenVar->toArray();
            } elseif (empty($sysScreenVar->SSV_USER_TYPE)) {
                $result[] = $sysScreenVar->toArray();
            } else {
                $tmpTypeArray = explode('|', $sysScreenVar->SSV_USER_TYPE);
                if (in_array($this->_getCurrentUser()->USER_TYPE, $tmpTypeArray))
                    $result[] = $sysScreenVar->toArray();
            }
        }

        $rowsetClass = $this->getDbTable()->getRowsetClass();
        return new $rowsetClass(
            array(
                 'table' => $this->getDbTable(),
                 'rowClass' => $this->getDbTable()->getRowClass(),
                 'data' => $result,
                 'stored' => true
            )
        );
    }
}