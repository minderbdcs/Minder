<?php

class Minder_SysScreen_Legacy_VarProvider
{
    public function fetchVars($screenName, $queryType)
    {
        $sql = "
            SELECT
                *
            FROM
                SYS_SCREEN_VAR
            WHERE
                SS_NAME = ?
                AND   SSV_FIELD_STATUS = ?
                AND   SSV_FIELD_TYPE = ?
            ORDER BY SSV_SEQUENCE
        ";

        $values = array($screenName, 'OK', $queryType);

        if (false === ($values = $this->_getMinder()->prepareArgs($sql, $values))) {
            throw new Minder_Exception(ibase_errcode() . ': ' . ibase_errmsg() . '. Query text: ' . $sql);
        }

        return $this->_getMinder()->findAllAssocMod($sql, $values);
    }

    protected function _getMinder()
    {
        return Minder::getInstance();
    }
}