<?php

class Minder_SysScreen_Legacy_SysEquipProvider {

    public function getFullPrinterList() {
        $sql = "
            SELECT
                DEVICE_ID,
                DEVICE_ID || ' - ' || COALESCE(EQUIPMENT_DESCRIPTION_CODE, '')
            FROM
                SYS_EQUIP
            WHERE
                SYS_EQUIP.DEVICE_TYPE IN ('PR', 'PL', 'LP')
            ORDER BY
                1
        ";

        return $this->_findList($sql);
    }

    /**
     * Simple Find List implementation, use this as getFullPrinterList can be called from Minder::getInstance
     * so Minder::findList will cause infinite loop
     *
     * @param $sql
     * @throws Exception
     * @return array|null
     */
    protected function _findList($sql) {
        $log = Minder_Registry::getLogger()->startDetailedLog(__METHOD__);
        $args = func_get_args();
        $queryMsg = 'Query text: ' . $sql . '; args: ' . implode(', ', array_slice($args, 1));

        $d = null;
        $query = ibase_prepare($sql);
        if ($query !== false) {
            $result = ibase_execute($query);
            if ($result !== false) {
                $d = array();
                while (false !== ($row = ibase_fetch_row($result, IBASE_FETCH_BLOBS))) {
                    $d[(string)$row[0]] = (string)$row[1];
                }
                ibase_free_result($result);

            } else {
                $errcode    = ibase_errcode();
                $errmsg     = ibase_errmsg();
                ibase_free_query($query);
                $log->error('Unable to run query ' . $errcode . ', ' . $errmsg . '.' . $queryMsg);
                throw new Exception('Unable to run query ' . $errcode . ', ' . $errmsg . '. Query text: ' . $sql);
            }

            ibase_free_query($query);
        }

        if ($d === null) {
            $log->error($queryMsg);
            throw new Exception('Unable to retrieve list');
        }
        else {
            $log->info($queryMsg);
            return $d;
        }

    }
}