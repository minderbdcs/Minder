<?php

class Minder2_SysScreen_Builder_ReceivePo extends  Minder2_SysScreen_Builder_Default {
    protected function _getSearchFormTitle() {
        return 'SEARCH PURCHASE ORDER';
    }

    /**
     * @param $ssName
     * @return Minder2_Model_SysScreen
     */
    function build($ssName)
    {
        $sysScreen = parent::build($ssName);
        $sysScreen->_TITLE = 'Orders List';
        return $sysScreen;
    }

}