<?php

class Minder_SysScreen_Legacy_TabProvider {
    public function fetchTabs($screenName) {
        $sql    =   "SELECT
                            SST_TAB_NAME,
                            SST_TITLE
                     FROM SYS_SCREEN_TAB
                     WHERE SS_NAME = ? AND SST_TAB_STATUS = 'OK'
                     ORDER BY SST_SEQUENCE ASC";

        return $this->_getMinder()->findList($sql, $screenName);

    }

    protected function _getMinder() {
        return Minder::getInstance();
    }
}