<?php

class Minder_SysScreen_Legacy_ControlProvider {
    public function getControls() {
        $sql = 'SELECT FIRST 1 * FROM CONTROL';
        return $this->_fetchAssoc($sql);
    }

    /**
     * Simple Fetch Assoc implementation, use this as getControls can be called from Minder::getInstance
     * so Minder::fetchAssoc will cause infinite loop
     *
     * @param $sql
     * @throws Minder_Exception
     * @return array
     */
    protected function _fetchAssoc($sql) {
        $log = Minder_Registry::getLogger()->startDetailedLog(__METHOD__);
        $queryMsg = 'Query text: ' . $sql;

        $query = ibase_prepare($sql);
        if (false === $query) {
            $log->error($queryMsg);
            throw new Minder_Exception(ibase_errcode() . ': ' . ibase_errmsg() . '. Query text: ' . $sql);
        }

        $result = ibase_execute($query);
        if (false === $result) {
            $errcode = ibase_errcode();
            $errmsg = ibase_errmsg();

            ibase_free_query($query);
            $log->error($queryMsg);
            throw new Minder_Exception($errcode . ': ' . $errmsg . '. Query text: ' . $sql);
        }
        $row = ibase_fetch_assoc($result, IBASE_TEXT);

        ibase_free_result($result);
        ibase_free_query($query);
        $log->info($queryMsg);
        return $row;

    }
}